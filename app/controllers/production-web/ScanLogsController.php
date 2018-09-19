<?php

class ScanLogsController extends \BaseController {

	public function index()
	{
		$filter = [
            'date_start' => Input::get('date_start'),
            'date_end' => Input::get('date_end'),
            'employee_id' => Input::get('employee_id'),
            'station_id' => Input::get('station_id'),
            'status_code' => Input::get('status_code'),
        ];

        if ($filter['date_start']) ScanLog::where('scan_date', '>=', $filter['date_start']);
        if ($filter['date_end']) ScanLog::where('scan_date', '<=', $filter['date_end']);
        return Response::json(ScanLog::get());
	}

	public function store()
	{
        $validator = Validator::make(
            Input::all(),
            [
                'scan_date' => 'required|date',
                'employee_id' => 'required|numeric',
                'order_number' => 'required|numeric',
                'frame_number' => 'required|numeric|min:101',
                'station_id' => 'required|numeric',
                'status_code' => 'required|numeric',
                'rack_number' => 'numeric'
            ]
        );

        if ($validator->fails()) {
            return Response::json([ 'error' => $validator->messages()->toArray() ], 500);
        }

        $scan = new ScanLog;
        $scan->scan_date = Input::get('scan_date');
        $scan->employee_id = Input::get('employee_id');
        $scan->order_number = Input::get('order_number');
        $scan->frame_number = Input::get('frame_number');
        $scan->station_id = Input::get('station_id');
        $scan->status_code = Input::get('status_code');
        $scan->rack_number = Input::get('rack_number');
        $scan->save();

        return Response::json($scan);
    }

	public function show($id)
	{
		return Response::json(ScanLog::on('production')->find($id));
	}

	public function edit($id)
	{
		//
	}

	public function update($id)
	{
		//
	}

	public function destroy($id)
	{
		ScanLog::find($id)->delete();
        return Response::json(true);
	}

}