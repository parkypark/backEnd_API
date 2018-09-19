<?php namespace ProjectsWeb;

use BaseController, DB, Input, Response, MongoDate;

class CalendarController extends BaseController {

    public function __construct()
    {
        $this->beforeFilter('jwt-auth.hasAccess:projects.calendar.read', ['on' => 'get']);
    }

    public function index()
    {
        $field = Input::get('field');
        if (! $field)
        {
            return Response::prettyjson(['errors' => ['Missing required parameter: field']], 400);
        }

        $start = Input::get('start');
        if (! $start)
        {
            return Response::prettyjson(['errors' => ['Missing required parameter: start']], 400);
        }
        $start = new MongoDate(strtotime($start));

        $end = Input::get('end');
        if (! $end)
        {
            return Response::prettyjson(['errors' => ['Missing required parameter: end']], 400);
        }
        $end = new MongoDate(strtotime($end));

        $data = Project::whereBetween($field, [$start, $end])->get();
        $ret = [];

        foreach ($data as $row)
        {
            $ret[] = [
                $field              => (string)$row->{$field},
                '_id'               => $row->_id,
                'project_id'        => $row->project_id,
                'project_name'      => $row->project_name,
                'project_type'      => $row->project_type['description'],
                'project_status'    => $row->project_status['description']
            ];
        }

        return Response::prettyjson($ret);
    }

}
