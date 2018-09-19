<?php

use Carbon\Carbon;

class NcmrExternalController extends \BaseController {

	private $header_validation_rules = [
		'report_number'	=> ['required'],
		'report_date'	=> ['required', 'date'],
		'inspector'		=> ['required', 'text'],
		'supplier'		=> ['text'],
		'destination'	=> ['text'],
		'sort_time'		=> ['numeric'],
		'details' 		=> ['array'],
		'attachments'	=> ['array']
	];

	private $detail_validation_rules = [
		'hash_code'		=> ['required', 'numeric'],
		'die_number'	=> ['required', 'text'],
		'date_received'	=> ['required', 'date'],
		'stock_length'	=> ['required', 'numeric'],
		'colour'		=> ['required', 'text'],
		'po_number'		=> ['required', 'numeric'],
		'rcv_number'	=> ['required', 'numeric'],
		'discrepancy'	=> ['required', 'text'],
		'picked'		=> ['required', 'numeric'],
		'rejected'		=> ['required', 'numeric']
	];


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$time_start = microtime(true);
		$hash = Input::get('hash', false);
		$tableState = json_decode(Input::get('tableState', false));

		if ($tableState)
		{
			$start = isset($tableState->pagination->start)
				? $tableState->pagination->start
				: 0;

			$number = isset($tableState->pagination->number)
				? $tableState->pagination->number
				: 10;

			$where = function($query) use ($tableState)
			{
				if (! (isset($tableState->search) && isset($tableState->search->predicateObject)))
				{
					return;
				}

				foreach ($tableState->search->predicateObject as $key => $value)
				{
					if ($key === 'global')
					{
						$query->where('report_number', (int)$value);
						$query->orWhere('report_date', $value);
						$query->orWhere('details.die_number', 'like', "%{$value}%");
						$query->orWhere('details.date_received', $value);
						$query->orWhere('details.po_number', 'like', "%{$value}%");
						$query->orWhere('details.rcv_number', 'like', "%{$value}%");
					}
					elseif ($key === 'report_number')
					{
						$query->where($key, (int)$value);
					}
					else
					{
						$query->where($key, 'like', "%{$value}%");
					}
				}
			};
		}
		else
		{
			$start = 0;
			$number = 10000; // Unlimited

			$where = function()
			{
				// Nothin' to see here
			};
		}

		$query = NcmrExternal::where($where);
		$total = $query->count();
		$data = $query->orderBy('report_number', 'DESC')->skip($start)->take($number)->get();
		$new_hash = md5(json_encode($data));

		if ($hash && $hash === $new_hash)
		{
			return Response::make(null, 204); // Should use 304 but it breaks CORS
		}

		return Response::prettyjson([
			'name' => 'ncmr_external',
			'compression' => 'none',
			'data' => $data,
			'hash' => $new_hash,
			'total' => $total,
			'time' => microtime(true) - $time_start
		]);
	}

	public function show($id)
	{
		$ncmr = NcmrExternal::find($id);
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

		if (is_null($ncmr_data))
		{
			return Response::prettyjson([
				'message' => 'Failed to save NCMR.',
				'errors' => array('ncmr' => 'Missing!')
			]);
		}

		$validation = Validator::make($ncmr_data, $this->header_validation_rules);
		if ($validation->fails())
		{
			return Response::prettyjson([
				'message' => 'Failed to save NCMR.',
				'ncmr' => Input::all(),
				'errors' => $validation->messages()
			]);
		}

		// Validate details
		$details = [];

		if (count($ncmr_data['details']) > 0)
		{
			for ($i = 0; $i < count($ncmr_data['details']); ++$i)
			{
				// Filter out deleted items
				if (isset($ncmr_data['details'][$i]['status']) &&  $ncmr_data['details'][$i]['status'] == 'deleted')
				{
					continue;
				}

				$validation = Validator::make($ncmr_data['details'][$i], $this->detail_validation_rules);
				if ($validation->fails())
				{
					return Response::prettyjson([
						'message'	=> 'Failed to save NCMR detail.',
						'detail'	=> $ncmr_data['details'][$i],
						'errors'	=> $validation->messages()
					]);
				}

				$details[] = [
					'hash_code'		=> $ncmr_data['details'][$i]['hash_code'],
					'die_number'	=> $ncmr_data['details'][$i]['die_number'],
					'date_received'	=> $ncmr_data['details'][$i]['date_received'],
					'stock_length'	=> $ncmr_data['details'][$i]['stock_length'],
					'colour'		=> $ncmr_data['details'][$i]['colour'],
					'po_number'		=> $ncmr_data['details'][$i]['po_number'],
					'rcv_number'	=> $ncmr_data['details'][$i]['rcv_number'],
					'discrepancy'	=> $ncmr_data['details'][$i]['discrepancy'],
					'picked'		=> $ncmr_data['details'][$i]['picked'],
					'rejected'		=> $ncmr_data['details'][$i]['rejected']
				];
			}
		}

		$ncmr = new NcmrExternal;
		$ncmr->report_number = $ncmr_data['report_number'];
		$ncmr->report_date = $ncmr_data['report_date'];
		$ncmr->inspector = $ncmr_data['inspector'];
		$ncmr->destination = $ncmr_data['destination'];
		$ncmr->supplier = $ncmr_data['supplier'];
		$ncmr->comment = mb_convert_encoding($ncmr_data['comment'], 'UTF-8', 'UTF-8');

		if (array_key_exists('sort_time', $ncmr_data))
		{
			$ncmr->sort_time = $ncmr_data['sort_time'];
		}

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

		if (is_null($ncmr_data))
		{
			return Response::prettyjson([
				'message' => 'Failed to save NCMR.',
				'errors' => array('ncmr' => 'Missing!')
			]);
		}

		$validation = Validator::make($ncmr_data, $this->header_validation_rules);
		if ($validation->fails())
		{
			return Response::prettyjson([
				'message' => 'Failed to update NCMR.',
				'ncmr' => Input::all(),
				'errors' => $validation->messages()
			]);
		}

		// Validate details
		$details = [];

		if (count($ncmr_data['details']) > 0)
		{
			for ($i = 0; $i < count($ncmr_data['details']); ++$i)
			{
				$validation = Validator::make($ncmr_data['details'][$i], $this->detail_validation_rules);
				if ($validation->fails())
				{
					return Response::prettyjson([
						'message'	=> 'Failed to save NCMR detail.',
						'detail' 	=> $ncmr_data['details'][$i],
						'errors' 	=> $validation->messages()
					]);
				}

				$details[] = [
					'hash_code' 	=> $ncmr_data['details'][$i]['hash_code'],
					'die_number' 	=> $ncmr_data['details'][$i]['die_number'],
					'date_received'	=> $ncmr_data['details'][$i]['date_received'],
					'stock_length' 	=> $ncmr_data['details'][$i]['stock_length'],
					'colour' 		=> $ncmr_data['details'][$i]['colour'],
					'po_number' 	=> $ncmr_data['details'][$i]['po_number'],
					'rcv_number' 	=> $ncmr_data['details'][$i]['rcv_number'],
					'discrepancy'	=> $ncmr_data['details'][$i]['discrepancy'],
					'picked'		=> $ncmr_data['details'][$i]['picked'],
					'rejected' 		=> $ncmr_data['details'][$i]['rejected']
				];
			}
		}

		$ncmr = NcmrExternal::find($id);
		$ncmr->report_number = $ncmr_data['report_number'];
		$ncmr->report_date = $ncmr_data['report_date'];
		$ncmr->inspector = $ncmr_data['inspector'];
		$ncmr->destination = $ncmr_data['destination'];
		$ncmr->supplier = $ncmr_data['supplier'];
		$ncmr->comment = mb_convert_encoding($ncmr_data['comment'], 'UTF-8', 'UTF-8');

		if (array_key_exists('sort_time', $ncmr_data))
		{
			$ncmr->sort_time = $ncmr_data['sort_time'];
		}

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
		try
		{
			$ncmr = NcmrExternal::find($id);
			$ncmr->delete();
			return Response::prettyjson([
				'message' => 'Success.',
				'errors' => array()
			]);
		}
		catch (Exception $e) {
			return Response::prettyjson([
				'message' => $e->getMessage(),
				'errors' => array('ncmr' => 'Something bad happened!')
			]);
		}
	}

	public function getNextReportNumber()
	{
		$max = 0 + NcmrExternal::max('report_number');
		return Response::json($max + 1);
	}

	public function download($id)
	{
		$ncmr = NcmrExternal::find($id)->toArray();
		$ncmr['comment'] = mb_convert_encoding($ncmr['comment'], 'HTML-ENTITIES');
		$ncmr['report_date'] = Carbon::parse($ncmr['report_date'])->toFormattedDateString();

		$total_rejected = 0;
		for ($i = 0; $i < count($ncmr['details']); ++$i)
		{
			$total_rejected += $ncmr['details'][$i]['rejected'];
		}

		$view = View::make('ncmr.external', ['ncmr' => $ncmr, 'total_rejected' => $total_rejected]);
		$html = $view->render();

		if ($html)
		{
			$tmp = tempnam(sys_get_temp_dir(), '_api.');

			// Write html input
			file_put_contents("$tmp.html", $html);

			// Print to pdf with phantomjs
			$cmd = NcmrController::RASTERIZE_PROGRAM
				. "$tmp.html $tmp.pdf"
				. ' 11in*8.5in';

			$response = exec($cmd);

			// Make files get deleted afterwards
			App::finish(function($request, $response) use ($tmp)
			{
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
		$ncmrs = NcmrExternal::all();

		foreach($ncmrs as $ncmr)
		{
			if (isset($ncmr->inspector_id))
			{
				$ncmr->inspector = DB::connection('archdb')
					->table('quality_web.ncmr_inspectors')
					->where('id', $ncmr->inspector_id)
					->first()->description;
			}

			if (isset($ncmr->supplier_id))
			{
				$ncmr->supplier = DB::connection('archdb')
					->table('quality_web.ncmr_suppliers')
					->where('id', $ncmr->supplier_id)
					->first()->description;
			}

			if (isset($ncmr->destination_id))
			{
				$ncmr->destination = DB::connection('archdb')
					->table('quality_web.ncmr_suppliers')
					->where('id', $ncmr->destination_id)
					->first()->description;
			}

			$hash_codes = [];
			$details = [];

			foreach($ncmr->details as $detail)
			{
				if (! in_array($detail['hash_code'], $hash_codes))
				{
					$hash_codes[] = $detail['hash_code'];
					$details[] = $detail;
				}
			}

			$ncmr->details = $details;
			$ncmr->save();
		}

		echo "Done.\r\n";
	}
}
