<?php

use Carbon\Carbon;

class NcmrInternalController extends \BaseController {

	private $header_validation_rules = [
		'report_number'	=> ['required'],
		'report_date'		=> ['required', 'date'],
		'inspector'			=> ['required', 'text'],
		'details'				=> ['array'],
		'attachments'		=> ['array']
	];

	private $detail_validation_rules = [
		'hash_code'						=> ['required', 'numeric'],
		'color'								=> ['required'],
		'list_number'					=> ['required', 'numeric'],
		'project_number'			=> ['required'],
		'project_name'				=> ['required'],
		'product_type'				=> ['required'],
		'profile'							=> ['required', 'text'],
		'discrepancy'					=> ['required', 'text'],
    'failure_source'			=> ['text'],
		'found_by_department'	=> ['required', 'text'],
		'rejected'						=> ['required', 'numeric'],
		'rush_code'						=> ['required', 'text'],
		'stock_length'				=> ['required', 'numeric']
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
						$query->orWhere('inspector', 'like', "%{$value}%");
						$query->orWhere('report_date', $value);
						$query->orWhere('details.list_number', 'like', "%{$value}%");
						$query->orWhere('details.project_number', 'like', "%{$value}%");
						$query->orWhere('details.project_name', 'like', "%{$value}%");
						$query->orWhere('details.profile', 'like', "%{$value}%");
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

		$query = NcmrInternal::where($where);
		$total = $query->count();
		$data = $query->orderBy('report_number', 'DESC')->skip($start)->take($number)->get();
		$new_hash = md5(json_encode($data));

		if ($hash && $hash === $new_hash)
		{
			return Response::make(null, 204); // Should use 304 but it breaks CORS
		}

		return Response::prettyjson([
			'name' => 'ncmr_internal',
			'compression' => 'none',
			'data' => $data,
			'hash' => $new_hash,
			'total' => $total,
			'time' => microtime(true) - $time_start
		]);
	}

	public function show($id)
	{
		$ncmr = NcmrInternal::find($id);
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

		// Validate header
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
						'message' => 'Failed to save NCMR detail.',
						'detail' =>  $ncmr_data['details'][$i],
						'errors' => $validation->messages()
					]);
				}

				$details[] = [
					'hash_code' => $ncmr_data['details'][$i]['hash_code'],
					'color' => $ncmr_data['details'][$i]['color'],
					'list_number' => $ncmr_data['details'][$i]['list_number'],
					'project_number' => $ncmr_data['details'][$i]['project_number'],
					'project_name' => $ncmr_data['details'][$i]['project_name'],
					'product_type' => $ncmr_data['details'][$i]['product_type'],
					'profile' => $ncmr_data['details'][$i]['profile'],
					'discrepancy' => $ncmr_data['details'][$i]['discrepancy'],
          'failure_source' => $ncmr_data['details'][$i]['failure_source'],
					'found_by_department' => $ncmr_data['details'][$i]['found_by_department'],
					'rejected' => $ncmr_data['details'][$i]['rejected'],
					'rush_code' => $ncmr_data['details'][$i]['rush_code'],
					'stock_length' => $ncmr_data['details'][$i]['stock_length']
				];
			}
		}

		$ncmr = new NcmrInternal;
		$ncmr->report_number = $ncmr_data['report_number'];
		$ncmr->report_date = $ncmr_data['report_date'];
		$ncmr->inspector = $ncmr_data['inspector'];
		$ncmr->comment = mb_convert_encoding($ncmr_data['comment'], 'UTF-8', 'UTF-8');
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
				// Filter out deleted items
				if (isset($ncmr_data['details'][$i]['status']) &&  $ncmr_data['details'][$i]['status'] == 'deleted')
				{
					continue;
				}

				$validation = Validator::make($ncmr_data['details'][$i], $this->detail_validation_rules);
				if ($validation->fails())
				{
					return Response::prettyjson([
						'message' => 'Failed to save NCMR details.',
						'detail' =>  $ncmr_data['details'][$i],
						'errors' => $validation->messages()
					]);
				}

				$details[] = [
					'hash_code' => $ncmr_data['details'][$i]['hash_code'],
					'color' => $ncmr_data['details'][$i]['color'],
					'list_number' => $ncmr_data['details'][$i]['list_number'],
					'project_number' => $ncmr_data['details'][$i]['project_number'],
					'project_name' => $ncmr_data['details'][$i]['project_name'],
					'product_type' => $ncmr_data['details'][$i]['product_type'],
					'profile' => $ncmr_data['details'][$i]['profile'],
					'discrepancy' => $ncmr_data['details'][$i]['discrepancy'],
          'failure_source' => $ncmr_data['details'][$i]['failure_source'],
					'found_by_department' => $ncmr_data['details'][$i]['found_by_department'],
					'rejected' => $ncmr_data['details'][$i]['rejected'],
					'rush_code' => $ncmr_data['details'][$i]['rush_code'],
					'stock_length' => $ncmr_data['details'][$i]['stock_length']
				];
			}
		}

		$ncmr = NcmrInternal::find($id);
		$ncmr->report_number = $ncmr_data['report_number'];
		$ncmr->report_date = $ncmr_data['report_date'];
		$ncmr->inspector = $ncmr_data['inspector'];
		$ncmr->comment = mb_convert_encoding($ncmr_data['comment'], 'UTF-8', 'UTF-8');
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
			$ncmr = NcmrInternal::find($id);
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
		$max = 0 + NcmrInternal::max('report_number');
		return Response::json($max + 1);
	}

	public function download($id)
	{
		$ncmr = NcmrInternal::find($id)->toArray();
		$ncmr['comment'] = mb_convert_encoding($ncmr['comment'], 'HTML-ENTITIES');
		$ncmr['report_date'] = Carbon::parse($ncmr['report_date'])->toFormattedDateString();

		$total_rejected = 0;
		for ($i = 0; $i < count($ncmr['details']); ++$i)
		{
			$total_rejected += $ncmr['details'][$i]['rejected'];
		}

		$view = View::make('ncmr.internal', [
			'ncmr' => $ncmr,
			'total_rejected' => $total_rejected
		]);

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
				if (file_exists("$tmp.html"))
				{
					unlink("$tmp.html");
				}

				if (file_exists("$tmp.pdf"))
				{
					unlink("$tmp.pdf");
				}

				if (file_exists($tmp))
				{
					unlink($tmp);
				}
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
		$ncmrs = NcmrInternal::all();

		$id2desc = function(&$data, $key, $source, $dest)
		{
			if(array_key_exists($key, $data))
			{
				$data[$dest] = DB::connection('archdb')
					->table('quality_web.ncmr_' . $source)
					->where('id', $data[$key])
					->first()->description;
				unset($data[$key]);
			}
		};

		foreach($ncmrs as $ncmr)
		{
			$hash_codes = [];
			$details = [];

			if (isset($ncmr->inspector_id))
			{
				$ncmr->inspector = DB::connection('archdb')
					->table('quality_web.ncmr_inspectors')
					->where('id', $ncmr->inspector_id)
					->first()->description;
				unset($ncmr->inspector_id);
			}

			foreach($ncmr->details as $detail)
			{
				if (isset($detail['header_id']))
				{
					unset($detail['header_id']);
				}

				if (! in_array($detail['hash_code'], $hash_codes))
				{
					//								KEY (id)									SOURCE (table)			DEST (property)
					$id2desc($detail, 'discrepancy_id',					'discrepancies',		'discrepancy');
					$id2desc($detail, 'failure_source_id',			'failure_sources',	'failure_source');
					$id2desc($detail, 'found_by_department_id',	'departments',			'found_by_department');
					$id2desc($detail, 'rush_code_id',						'rush_codes',				'rush_code');

					$hash_codes[] = $detail['hash_code'];
					$details[] = $detail;
				}
			}

			$ncmr->details = $details;
			$ncmr->save();

			echo "Saved $ncmr->report_number...\n";
			ob_flush();
			flush();
		}

		echo "\nDone.\n";
	}

}
