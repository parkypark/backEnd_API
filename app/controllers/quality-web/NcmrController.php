<?php

class NcmrController extends \BaseController
{
	const RASTERIZE_PROGRAM = '/usr/bin/xvfb-run -a /usr/bin/phantomjs /usr/share/doc/phantomjs/examples/rasterize.js ';

	/**
	 * Resize and compress attachments
	 *
	 * @return Array
	 */
	public static function ProcessAttachments($attachments)
    {
		$ret = [];

		if ($attachments && is_array($attachments)) {
			foreach ($attachments as $attachment) {
				if (! (array_key_exists('processed', $attachment) && $attachment['processed'])) {
					$data = explode(',', $attachment['data']);
					$filename = tempnam(sys_get_temp_dir(), 'ncmrat');
					$file = fopen($filename, 'w');
					fwrite($file, base64_decode($data[1]));
					fclose($file);

					$img = new Imagick($filename);
					$img->resizeImage(1920, 1080, Imagick::FILTER_LANCZOS, 1, true);
					$img->stripImage();
					$img->setImageCompressionQuality(85);
					$img->writeImage($filename);
					$data[1] = base64_encode($img->getImageBlob());
					$img->destroy();
					unlink($filename);

					$attachment['data'] = implode(',', $data);
					$attachment['processed'] = true;
				}

				$ret[] = $attachment;
			}
		}

		return $ret;
	}

	/**
	 * Get lookup data
	 *
	 * @return Response
	 */
	public function getLookups()
	{
		return Response::prettyjson([
			'name' => 'lookups',
			'compression' => 'lz-string: utf16',
			'data' => NcmrLookup::all()
		]);
	}


	/**
	 * Get materials for external NCMRs
	 *
	 * @return Response
	 */
	public function getMaterials()
	{
		return Response::prettyjson([
			'name' => 'materials',
			'compression' => 'lz-string: utf16',
			'data' => Materials::all()
		]);
	}


	/**
	 * Get materials for internal NCMRs
	 *
	 * @return Response
	 */
	public function getMaterialsRequired()
	{
		$start = Input::get('start', 0);
		$count = Input::get('count', 10);
		$predicate = Input::get('predicate', null);

		$ret = [];

		// get headers
		$sql = '
			SELECT
				project_info.ProjectNumber AS project_number,
				project_info.ProjectName AS project_name,
				LPAD(orders.processreference, 3, "0") AS list_number,
				orders.ordernumber AS order_number,
				orders.customername AS customer_name,
				orders.deliveryinstructions AS delivery_instructions,
				orders.dateprocessed AS date_processed
			FROM
				workorders.orders, projects.project_info, workorders.materialrequired
			WHERE
				orders.prefix = "P" AND project_info.ProjectNumber = orders.project_id AND
				materialrequired.listnum = orders.processreference
		';

		$qParams = [];

		if ($predicate) {
			$predicate = json_decode($predicate);
			foreach ($predicate as $k => $v) {
				switch ($k) {
					case 'list_number':
						$k = 'processreference';
						break;
					case 'order_number':
						$k = 'ordernumber';
						break;
					default:
						$k = 'ProjectName';
						break;
				}

				$sql .= "
					AND `{$k}` LIKE CONCAT('%', ?, '%')
				";

				$qParams[] = $v;
			}
		}

		$sql .= '
			GROUP BY
				orders.ordernumber
			ORDER BY
				orders.ordernumber DESC
			LIMIT ?, ?
		';

		$qParams[] = $start;
		$qParams[] = $count;

		$headers = DB::connection('archdb-wm')->select($sql, $qParams);
		foreach ($headers as $header) {
			$sql = '
				SELECT
					IF(M.rolledprof = "NA", M.snglprof, M.rolledprof) AS `profile`,
				  M.`length` AS stock_length, M.quantity, M.snglprof,
				  GROUP_CONCAT(DISTINCT CONCAT_WS("|", M.snglprof, M.color) ORDER BY M.location) AS extrusions,
				  CONCAT_WS(",", GROUP_CONCAT(DISTINCT REPLACE(AF.framecode, "_", "")), "Misc") AS frame_types
				FROM
					workorders.materialrequired M
				LEFT OUTER JOIN
					mfg.profiles_allowedframes AF
						ON AF.framecode != "IMAGINARY" AND AF.`code` = SUBSTRING_INDEX(M.snglprof, " - ", 1)
				WHERE
					M.listnum = ?
				GROUP BY
			    IF(rolledprof = "NA", CONCAT(snglprof, location), rolledprof),
			    stock_length
			';

			$rows = DB::connection('archdb-wm')->select($sql, [$header->list_number]);
			$materials = [];
			foreach ($rows as $row) {
				// Convert string representation of extrusion/colour pairs to objects
				$extrusions = array_map(function ($extrusion) {
					$kvp = explode('|', $extrusion);
					return [
						'profile' => $kvp[0],
						'color'		=> $kvp[1]
					];
				}, explode(',', $row->extrusions));

                // Create material object and push into array
                $materials[] = [
                    // hash_code: hashCode,
                    'profile' 		=> $row->profile,
                    'stock_length'	=> $row->stock_length,
                    'frame_types'	=> explode(',', $row->frame_types),
                    'quantity'		=> $row->quantity,
                    'extrusions'	=> $extrusions
                ];
			}

			$ret[] = [
				'header' 	=> $header,
				'materials' => $materials
			];
		}

		return Response::prettyjson($ret);
	}

	public function getPanels()
	{
		$tableState = json_decode(Input::get('tableState', false));

		if ($tableState) {
			$start = isset($tableState->pagination->start)
				? $tableState->pagination->start
				: 0;

			$number = isset($tableState->pagination->number)
				? $tableState->pagination->number
				: 10;

			$where = function ($query) use ($tableState) {
				if (! (isset($tableState->search) && isset($tableState->search->predicateObject) && isset($tableState->search->predicateObject->header))) {
					return;
				}

				foreach ($tableState->search->predicateObject->header as $key => $value) {
					if ($key === 'list_number') {
						$key = 'processreference';
					} elseif ($key === 'order_number') {
						$key = 'ordernumber';
					} elseif ($key === 'project_name') {
						$key = 'project_info.ProjectName';
					}
					$query->where($key, 'like', "%{$value}%");
				}
			};
		} else {
			$order_by = 'orders.ordernumber';
			$order_dir = 'desc';
			$start = 0;
			$number = 100;

			$where = function () {
				// Nothin' to see here
			};
		}

		$query = DB::connection('archdb')
			->table('workorders.orders')
			->join('workorders.panels', 'orders.ordernumber', '=', 'panels.ordernumber')
			->join('projects.project_info', 'project_info.ProjectID', '=', 'orders.project_id')
			->whereRaw('orders.dateprocessed >= DATE_ADD(CURRENT_DATE(), INTERVAL -6 MONTH)')
			->where('orders.prefix', '=', 'P')
			->where($where);

		$total = $query
			->select(DB::raw('COUNT(DISTINCT processreference, dateprocessed) AS records'))
			->remember(2)
			->get()[0]->records;

		$list_numbers = $query
			->select([
				'orders.processreference',
				'orders.dateprocessed',
				'project_info.ProjectID',
				'project_info.ProjectName',
				DB::raw('GROUP_CONCAT(DISTINCT orders.ordernumber) AS ordernumbers')
			])
			->orderBy('orders.ordernumber', 'DESC')
			->groupBy('orders.processreference', 'orders.dateprocessed')
			->skip($start)
			->take($number)
			->remember(2)
			->get();

		$ret = [];
		foreach ($list_numbers as $list_number) {
			$list_number->ordernumbers = explode(',', $list_number->ordernumbers);

			$data = DB::connection('archdb')
				->table('workorders.panels')
				->leftJoin('workorders.frameproducts', function ($join) {
					$join
						->on('frameproducts.ordernumber', '=', 'panels.ordernumber')
						->on('frameproducts.linenumber', '=', 'panels.linenumber')
						->on('frameproducts.frameindex', '=', 'panels.frameindex');
				})
				->select([
					'panels.ordernumber',
					'panels.frameindex',
					'panels.compindex',
					'panels.panelindex',
					'panels.paneltypeid',
					'panels.materialcode',
					'panels.color',
					'panels.width',
					'panels.height',
					'panels.parameters',
					'frameproducts.framenumberfrom',
					'frameproducts.framenumberto'
				])
				->whereIn('panels.ordernumber', $list_number->ordernumbers)
                ->orderBy('frameproducts.framenumberfrom')
				->orderBy('panels.materialcode', 'asc')
				->orderBy('panels.color', 'asc')
				->orderBy('panels.width', 'asc')
				->orderBy('panels.height', 'asc')
				->get();

			$panels = [];
			foreach ($data as $row) {
				$params = explode('=', $row->parameters);
				if (count($params) > 1) {
					$depth = (float)explode('=', $row->parameters)[1];
				} else {
					$depth = 0;
				}

				$panels[] = [
					'ordernumber'	=> $row->ordernumber,
					'frameindex'	=> $row->frameindex,
					'compindex'		=> $row->compindex,
					'panelindex'	=> $row->panelindex,
					'paneltypeid'	=> $row->paneltypeid,
					'material'		=> $row->materialcode,
					'colour'		=> $row->color,
					'width'			=> $row->width,
					'height'		=> $row->height,
					'depth'			=> $depth,
					'frame_numbers'	=> ($row->framenumberfrom) ? range($row->framenumberfrom, $row->framenumberto) : ['NA']
				];
			}

			$ret[] = [
				'header' => [
					'list_number'	=> $list_number->processreference,
					'project_id'	=> $list_number->ProjectID,
					'project_name'	=> $list_number->ProjectName,
					'order_numbers'	=> $list_number->ordernumbers
				],
				'panels' => $panels
			];
		}

		return Response::prettyjson([
			'data' => $ret,
			'total' => $total
		]);
	}

	public function processAllAttachments()
	{
		$ncmrs = NcmrExternal::all();
		foreach ($ncmrs as $ncmr) {
			if ($ncmr->attachments && is_array($ncmr->attachments)) {
				$ncmr->attachments = NcmrController::ProcessAttachments($ncmr->attachments);
				$ncmr->save();
			}
		}

		$ncmrs = NcmrInternal::all();
		foreach ($ncmrs as $ncmr) {
			if ($ncmr->attachments && is_array($ncmr->attachments)) {
				$ncmr->attachments = NcmrController::ProcessAttachments($ncmr->attachments);
				$ncmr->save();
			}
		}

		$ncmrs = NcmrFabrication::all();
		foreach ($ncmrs as $ncmr) {
			if ($ncmr->attachments && is_array($ncmr->attachments)) {
				$ncmr->attachments = NcmrController::ProcessAttachments($ncmr->attachments);
				$ncmr->save();
			}
		}

		return Response::prettyjson('All attachments processed');
	}

	private function loadTemplate($template, $data)
	{
		$storage_path = storage_path();
		$path = "$storage_path/templates/$template.html";

		if (File::exists($path)) {
			$html = file_get_contents($path);
			foreach ($data as $key => $value) {
				$html = str_replace('{{'.$key.'}}', $value, $html);
			}
			return $html;
		}

		return false;
	}
}
