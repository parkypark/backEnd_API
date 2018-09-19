<?php

use Carbon\Carbon;

class NcmrFabricationController extends \BaseController
{

	private $header_validation_rules = [
		'report_number' => ['required'],
		'report_date' => ['required', 'date'],
		'inspector' => ['required', 'text'],
		'supplier' => ['text'],
		'bundle_number' => ['text'],
		'bundle_qty' => ['numeric'],
		'date_inspected' => ['text'],
		'report_filled_by' => ['text'],
		'details' => ['array'],
		'attachments' => ['array']
	];

	private $detail_validation_rules = [

	];


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

		$tableState = json_decode(Input::get('tableState', false));
		$hash = Input::get('hash', false);

		if ($tableState) {
			$start = isset($tableState->pagination->start)
				? $tableState->pagination->start
				: 0;

			$number = isset($tableState->pagination->number)
				? $tableState->pagination->number
				: 10;

			$where = function ($query) use ($tableState) {
				if (! (isset($tableState->search) && isset($tableState->search->predicateObject))) {
					return;
				}

				foreach ($tableState->search->predicateObject as $key => $value) {
					if ($key === 'global') {
						$query->where('report_number', (int)$value);
						$query->orWhere('report_date', $value);
						$query->orWhere('date_inspected', $value);
						$query->orWhere('supplier', 'like', "%{$value}%");
						$query->orWhere('bundle_number', 'like', "%{$value}%");
						$query->orWhere('details.list_number', 'like', "%{$value}%");
						$query->orWhere('details.material', 'like', "%{$value}%");
						$query->orWhere('details.colour', 'like', "%{$value}%");
						$query->orWhere('details.project_id', 'like', "%{$value}%");
						$query->orWhere('details.project_name', 'like', "%{$value}%");
						$query->orWhere('details.rejected_reason', 'like', "%{$value}%");
					} elseif ($key === 'report_number') {
						$query->where($key, (int)$value);
					} else {
						$query->where($key, 'like', "%{$value}%");
					}
				}
			};
		} else {
			$order_by = 'updated_at';
			$order_dir = 'desc';
			$start = 0;
			$number = 10000; // Unlimited

			$where = function () {
				// Nothin' to see here
			};
		}

		$query = NcmrFabrication::where($where);
		$total = $query->count();
		$data = $query->orderBy('report_number', 'DESC')->skip($start)->take($number)->get();
		$new_hash = md5(json_encode($data));

		if ($hash && $hash === $new_hash) {
			return Response::make(null, 204); // Should send 304, but apache strips CORS headers when status = 304
		}

		return Response::prettyjson([
			'name' => 'ncmr_fabrication',
			'compression' => 'none',
			'data' => $data,
			'hash' => $new_hash,
			'total' => $total
		]);
	}

	public function show($id)
	{
		$ncmr = NcmrFabrication::find($id);
		return Response::prettyjson($ncmr);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$ncmr_data = Input::get('ncmr');

		if (is_null($ncmr_data)) {
			return Response::prettyjson([
				'message' => 'Failed to save NCMR.',
				'errors' => array('ncmr' => 'Missing!')
			]);
		}

		// Validate header
		$validation = Validator::make($ncmr_data, $this->header_validation_rules);
		if ($validation->fails()) {
			return Response::prettyjson([
				'message' => 'Failed to save NCMR.',
				'ncmr' => Input::all(),
				'errors' => $validation->messages()
			]);
		}

		// Validate details
		$details = [];
		if (count($ncmr_data['details']) > 0) {
			for ($i = 0; $i < count($ncmr_data['details']); ++$i) {
				$line = $ncmr_data['details'][$i];

				$validation = Validator::make($line, $this->detail_validation_rules);
				if ($validation->fails()) {
					return Response::prettyjson([
						'message' => 'Failed to save NCMR detail.',
						'detail' =>  $line,
						'errors' => $validation->messages()
					]);
				}

				if (!isset($line['remakable']) || $line['remakable'] == true) {
					$line = $this->generateBarcodes($line);
				}

				$details[] = $line;
			}
		}

		if (count($details) > 0) {
			$result = $this->storeRemakes($ncmr_data['report_number'], $ncmr_data['report_filled_by'], $details);
			if ($result !== true) {
				return Response::prettyjson($result, 500);
			}
		}

		$ncmr = new NcmrFabrication;
		$ncmr->report_number = $ncmr_data['report_number'];
		$ncmr->report_date = $ncmr_data['report_date'];
		$ncmr->inspector = $ncmr_data['inspector'];
		$ncmr->comment = mb_convert_encoding($ncmr_data['comment'], 'UTF-8', 'UTF-8');
		$ncmr->supplier = $ncmr_data['supplier'];
		$ncmr->bundle_number = $ncmr_data['bundle_number'];
		$ncmr->bundle_qty = $ncmr_data['bundle_qty'];
		$ncmr->date_inspected = $ncmr_data['date_inspected'];
		$ncmr->report_filled_by = $ncmr_data['report_filled_by'];
		$ncmr->details = $details;
		$ncmr->attachments = NcmrController::ProcessAttachments($ncmr_data['attachments']);
		$ncmr->save();

		return Response::prettyjson($ncmr);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$ncmr_data = Input::get('ncmr');

		if (is_null($ncmr_data)) {
			return Response::prettyjson([
				'message' => 'Failed to save NCMR.',
				'errors' => array('ncmr' => 'Missing!')
			]);
		}

		$validation = Validator::make($ncmr_data, $this->header_validation_rules);
		if ($validation->fails()) {
			return Response::prettyjson([
				'message' => 'Failed to update NCMR.',
				'ncmr' => Input::all(),
				'errors' => $validation->messages()
			]);
		}

		// Validate details
		$details = [];
		if (count($ncmr_data['details']) > 0) {
			for ($i = 0; $i < count($ncmr_data['details']); ++$i) {
				$line = $ncmr_data['details'][$i];

				$validation = Validator::make($line, $this->detail_validation_rules);
				if ($validation->fails()) {
					return Response::prettyjson([
						'message' => 'Failed to save NCMR details.',
						'detail' =>  $line,
						'errors' => $validation->messages()
					]);
				}

				if (!isset($line['remakable']) || $line['remakable'] == true) {
					$line = $this->generateBarcodes($line);
				}

				$details[] = $line;
			}
		}

		// delete previous remakes
		$result = $this->deleteRemakes($ncmr_data['report_number']);
		if ($result !== true) {
			return Response::prettyjson($result, 500);
		}

		// store new remakes
		if (count($details) > 0) {
			$result = $this->storeRemakes($ncmr_data['report_number'], $ncmr_data['report_filled_by'], $details);
			if ($result !== true) {
				return Response::prettyjson($result, 500);
			}
		}

		$ncmr = NcmrFabrication::find($id);
		$ncmr->report_number = $ncmr_data['report_number'];
		$ncmr->report_date = $ncmr_data['report_date'];
		$ncmr->inspector = $ncmr_data['inspector'];
		$ncmr->comment = mb_convert_encoding($ncmr_data['comment'], 'UTF-8', 'UTF-8');
		$ncmr->supplier = $ncmr_data['supplier'];
		$ncmr->bundle_number = $ncmr_data['bundle_number'];
		$ncmr->bundle_qty = $ncmr_data['bundle_qty'];
		$ncmr->date_inspected = $ncmr_data['date_inspected'];
		$ncmr->report_filled_by = $ncmr_data['report_filled_by'];
		$ncmr->details = $details;
		$ncmr->attachments = NcmrController::ProcessAttachments($ncmr_data['attachments']);
		$ncmr->save();

		return Response::prettyjson($ncmr);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		try {
			$ncmr = NcmrFabrication::find($id);

			$this->deleteRemakes($ncmr->report_number);

			$ncmr->delete();
			return Response::prettyjson([
				'message' => 'Success.',
				'errors' => array()
			]);
		} catch (Exception $e) {
			return Response::prettyjson([
				'message' => $e->getMessage(),
				'errors' => array('ncmr' => 'Something bad happened!')
			]);
		}
	}

	public function getLineInfo($barcode)
	{
		$data = DB::connection('archdb')->select(DB::raw('CALL workorders.sp_get_NCMR_Fab_barcodeLine(?)'), [$barcode]);
		return Response::prettyjson($data);
	}

	public function getCurrentReportNumber()
	{ 
		$temp = "". NcmrFabrication::max('report_number');
		$max = 0 + $temp;
		//$max = 0 + NcmrFabrication::max('report_number');
		return Response::json($max);	
	}

	public function getNextReportNumber()
	{
		$max = 0 + NcmrFabrication::max('report_number');
		return Response::json($max + 1);
	}

	public function getStickerData($ncmr)
	{
		$errorList = array();
		$panels = json_decode($ncmr, false);
		$query = DB::connection('work-orders')->table('panel_labels As pl')
			->join('panels As p', function ($join) {
				$join->on('pl.ordernumber', '=', 'p.ordernumber');
				$join->on('pl.Linenumber', '=', 'p.linenumber');
				$join->on('pl.FrameIndex', '=', 'p.frameindex');
				$join->on('pl.CompIndex', '=', 'p.compindex');
				$join->on('pl.PanelPackageIndex', '=', 'p.panelindex');
				$join->on('pl.paneltypeid', '=', 'p.paneltypeid');
			})
			->join('orders as o', 'o.ordernumber', '=', 'pl.ordernumber');

		$first = true;
		if (!isset($panels)) {
			return Response::prettyjson(['error' => 'invalid input'], 400);
		}

		foreach ($panels as $row) {
			if (!isset($row->ordernumber)) {
				array_push($errorList, $row);
				break;
			}

			if ($first) {
				$query->where('barcode', $row->barcode);
				$first = false;
			} else {
				$query->orwhere('barcode', $row->barcode);
			}
		}

		$query->select('p.paneltype', 'o.processreference as list', 'pl.BoxNumber', 'p.width', 'p.height', 'p.color', 'p.materialcode', 'p.insulation', 'pl.barcode');
		$data = $query->get();
		$hash = md5(json_encode($data));
		$reportNum = $panels[0]->reportNum;

		if (sizeof($panels) == sizeof($errorList)) {
			return ['message' => 'All reports are manually made', 'error' => 'NobarcodesError'];
		}

		$file = "/tmp/sticker_tmp.pdf";
		$name = 'sticker_tmp.pdf';
		$fpdf = new Fpdf('p', 'mm');
		$fpdf->SetTopMargin(5);
		$fpdf->AddPage();

		$generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();

		foreach ($data as $single) {
			$fpdf->SetFont('Arial', '', 12);
			$fpdf->MultiCell(0, 5, "NCMR# $reportNum (L# $single->list) B#$single->BoxNumber", 0, 'C');
			$fpdf->setX(50);
			$fpdf->Cell(50, 5, "$single->paneltype", 0, 0, 'L');
			$fpdf->Cell(50, 5, "$single->color", 0, 1, 'R');

			$fpdf->setX(50);
			$fpdf->Cell(40, 5, "$single->width x $single->height", 0, 0, 'L');
            $fpdf->Cell(30, 5, "$single->insulation", 0, 0, 'C');
			$fpdf->Cell(30, 5, "$single->materialcode", 0, 1, 'R');
			$picURL =  'data:image/png;base64,' . base64_encode($generatorPNG->getBarcode($single->barcode, $generatorPNG::TYPE_CODE_128));
			$pic = $this->getImage($picURL);
			if ($pic !== false) {
				$fpdf->Cell(100, 5, $fpdf->Image($pic[0], $fpdf->getX() + 35, $fpdf->getY(), 0, 0, $pic[1]), 0, 1, 'R');
			}
			$fpdf->SetFont('Arial', '', 8);
			$fpdf->Ln(2);
			$fpdf->Cell(100, 5, "$single->barcode", 0, 1, 'C');
			$fpdf->Ln(18);
		}

		App::finish(function ($request, $response) use ($file) {
			if (file_exists($file)) {
				unlink($file);
			}
		});

		$buffer = $fpdf->Output($file, 'S');

		file_put_contents($file, $buffer);
		$header = array('Content-Type'=> 'application/x-download',
                    'Content-Disposition' => 'attachment; filename="' . $name . '"' ,
                    'Cache-Control' => 'private, max-age=0, must-revalidate',
                    'Pragma' => 'public');

		return Response::download($file, $name, $header);
	}

	public function download($id)
	{
		$ncmr = NcmrFabrication::find($id)->toArray();
return Response::prettyjson(['msg' => $ncmr], 200);
		

		$ncmr['comment'] = mb_convert_encoding($ncmr['comment'], 'HTML-ENTITIES');
		$ncmr['report_date'] = Carbon::parse($ncmr['report_date'])->toFormattedDateString();

		$total_rejected = 0;
		for ($i = 0; $i < count($ncmr['details']); ++$i) {
			$total_rejected += $ncmr['details'][$i]['rejected'];

			$ncmr['details'][$i]['width'] = $this->dec2frac($ncmr['details'][$i]['width']);
			$ncmr['details'][$i]['height'] = $this->dec2frac($ncmr['details'][$i]['height']);
			$ncmr['details'][$i]['depth'] = $this->dec2frac($ncmr['details'][$i]['depth']);

			if (is_array($ncmr['details'][$i]['rejected_reason']) {
				$ncmr['details'][$i]['rejected_reason'] = $ncmr['details'][$i]['rejected_reason']['description'];
			}
			
			if (!array_key_exists('list_number', $ncmr['details'][$i])) {
				$ncmr['details'][$i]['list_number'] = 'N/A';
			}
		}

		$view = View::make('ncmr.fabrication', [
			'ncmr' => $ncmr,
			'total_rejected' => $total_rejected
		]);

		$html = $view->render();

		if ($html) {
			$tmp = tempnam(sys_get_temp_dir(), '_api.');

			// Write html input
			file_put_contents("$tmp.html", $html);

			// Print to pdf with phantomjs
			$cmd = NcmrController::RASTERIZE_PROGRAM
				. "$tmp.html $tmp.pdf"
				. ' 11in*8.5in';

			$response = exec($cmd);

			// Make files get deleted afterwards
			App::finish(function ($request, $response) use ($tmp) {
				unlink("$tmp.html");
				unlink("$tmp.pdf");
			});

			// Return generated pdf
			return Response::make(file_get_contents("$tmp.pdf"), 200, [
				'Content-Type'              => 'application/pdf',
				'Content-Disposition'       => "filename={$ncmr['report_number']} NCMR.pdf",
				'Content-Transfer-Encoding' => 'binary',
				'Accept-Ranges'             => 'bytes'
			]);
		}

		return Response::prettyjson(['error' => 'Bad request'], 400);
	}

	public function upgrade()
	{
		$ncmrs = NcmrFabrication::all();

		foreach ($ncmrs as $ncmr) {
			if (isset($ncmr->inspector_id)) {
				$ncmr->inspector = DB::connection('archdb')
					->table('quality_web.ncmr_inspectors')
					->where('id', $ncmr->inspector_id)
					->first()->description;
			}

			$hash_codes = [];
			$details = [];

			foreach ($ncmr->details as $detail) {
				if (isset($detail['header_id'])) {
					unset($detail['header_id']);
				}

				if (! in_array($detail['hash_code'], $hash_codes)) {
					$details[] = $detail;
					$hash_codes[] = $detail['hash_code'];
				}
			}

			$ncmr->details = $details;
			$ncmr->save();

			echo "Saved $ncmr->report_number...\n";
		}

		echo "\nDone.\n";
	}

	private function dec2frac($f)
	{
		$f = floor($f * 16) / 16;
		$base = floor($f);

		if ($base) {
			$out = $base . ' ';
			$f = $f - $base;
		} else {
			$out = '';
		}

		if ($f != 0) {
			$d = 1;
			while (fmod($f, 1) != 0.0) {
				$f *= 2;
				$d *= 2;
			}

			$n = sprintf('%.0f', $f);
			$d = sprintf('%.0f', $d);
			$out .= $n . '/' . $d;
		}

		return $out;
	}

	private function deleteRemakes($report_number)
	{
		try {
			DB::connection("archdb")
				->table("workorders.remakes")
				->where('reference_number', $report_number)
				->whereNull('processed_at')
				->delete();

			return true;
		} catch (Exception $e) {
			return ['message' => 'Error', 'error' => $e->getMessage()];
		}
	}

	private function generateBarcodes($line)
	{
		if (!array_key_exists('barcode', $line)) {
			$panelindex = str_pad($line['panelindex'], 2, '0', STR_PAD_LEFT);
			$paneltypeid = str_pad($line['paneltypeid'], 4, '0', STR_PAD_LEFT);

			if ($line['frame_number'] === 'NA') {
				// OE ORDERS
				$boxnumber = str_pad($line['linenumber'], 3, '0', STR_PAD_LEFT);
				$barcode = [];

				for ($i = 1; $i <= $line['rejected']; ++$i) {
					$compindex = str_pad($i, 2, '0', STR_PAD_LEFT);
					$barcode[] = "X{$line['ordernumber']}{$boxnumber}{$compindex}P{$panelindex}V{$paneltypeid}";
				}
			} else {
				$compindex = str_pad($line['compindex'], 2, '0', STR_PAD_LEFT);
				$barcode = "X{$line['ordernumber']}{$line['frame_number']}{$compindex}P{$panelindex}V{$paneltypeid}";
			}

			$line['barcode'] = $barcode;
		}

		return $line;
	}
	public function getFailureReasons()
	{
		$query = DB::connection('archdb')
			->table("reports.lkp_NCMR_failure_reasons")
			->select("id", "description");
			$data = $query->get();
			
		return Response::prettyjson([
			'name' => 'ncmr_fabrication',
			'data' => $data
		]);
	}
	public function test1(){
		//mongoDB
		
		$ncmrs1 = NcmrFabrication::where('report_date','like','2018-04%')->get();
		
		$panelCnt = 0;
		//Mysql
		$ncmrs2 = DB::connection('archdb')->table('workorders.remakes')->where('created_at','like','2018-04%')->get();


		$statusMsg = [];
		$cnt = 0;
		$mySQlSubMongo = [];
		foreach($ncmrs2 as $ncmrMy){
			$is = false;
			foreach($ncmrs1 as $ncmrMongo){
				foreach($ncmrMongo->details as $ncmrMongo1){

					if($ncmrMongo->report_number->value == $ncmrMy->reference_number){
						$barcodes = [];
						if(!isset($ncmrMongo1['frame_numbers'])){
							break;
						}
						foreach($ncmrMongo1['frame_numbers'] as $frame){

							array_push($barcodes, '' . $ncmrMongo1['ordernumber'] .  str_pad($frame, 3, '0', STR_PAD_LEFT) . str_pad($ncmrMongo1['compindex'],2,'0', STR_PAD_LEFT) . "P" . str_pad($ncmrMongo1['panelindex'],2,'0', STR_PAD_LEFT));


							/*if($ncmrMongo1['ordernumber'] == '165834' && $ncmrMongo1['compindex'] == '3' && $frame == '171' && $ncmrMongo1['panelindex'] == '1'){
								array_push($statusMsg,$ncmrMongo);
							}*/
						}
 
						foreach($barcodes as $barcode){
							if(strpos($ncmrMy->barcode,$barcode) != false){
								$is = true;		
							}
							//array_push($statusMsg,$ncmrMy->barcode . '||' . $barcode);
							array_push($statusMsg,$ncmrMy->barcode);
						}
						
						
					}	
				}
			}

			if($is == false){
				array_push($mySQlSubMongo, $ncmrMy);
				$cnt ++;
			}
		}

		foreach($ncmrs1 as $ncmrs){
			$panelCnt += count($ncmrs->details);
		}


		return Response::prettyjson([
			'name' => 'ncmr_fabrication',
			'compression' => 'none',
			'statusMSG' =>$statusMsg,
			'missingData' =>$mySQlSubMongo,
			'NCMRQty' =>count($ncmrs1),
			'selectedCNt' => $cnt,
			'mysqlCnt' => count($ncmrs2),
			'PanelQty' => $panelCnt
		]); 
	}
	 

	private function getImage($dataURI)
	{
		$img = explode(',', $dataURI, 2);
		$pic = 'data://text/plain;base64,'.$img[1];
		$type = explode('/', explode(':', substr($dataURI, 0, strpos($dataURI, ';')))[1])[1];
		return ($type === 'png' || $type === 'jpeg' || $type === 'gif') ? array($pic, $type) : false;
	}

	private function storeRemakes($report_number, $reported_by, $lines)
	{
		try {
			$values = [];
			foreach ($lines as $line) {
				// skip if remakable is false
				if (isset($line['remakable']) && $line['remakable'] == false) {
					continue;
				}

				if (is_array($line['barcode'])) {
					foreach ($line['barcode'] as $barcode) {
						$values[] = [
							'barcode' => $barcode,
							'created_at' => date('Y-m-d H:i:s'),
							'created_by' => $reported_by,
							'project_id' => $line['project_id'],
							'reference_number' => $report_number,
						];
					}
				} else {
					$values[] = [
						'barcode' => $line['barcode'],
						'created_at' => date('Y-m-d H:i:s'),
						'created_by' => $reported_by,
						'project_id' => $line['project_id'],
						'reference_number' => $report_number
					];
				}
			}

			if (count($values)) {
				DB::connection('archdb')->table('workorders.remakes')->insert($values);
			}

			return true;
		} catch (Exception $e) {
			return ['error' => $e->getMessage()];
		}
	}
}
