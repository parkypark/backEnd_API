<?php namespace ProjectsWeb;

use BaseController, DB, Input, Response, DateTime, MongoDate;

class ChartController extends BaseController {

    public function __construct()
    {
        $what = 'dashboard';
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.read", ['on' => 'get']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.create", ['on' => 'post']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.update", ['on' => 'put']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.delete", ['on' => 'delete']);
    }

    public function getProjectsTendered()
    {
        $start = Input::has('start')
            ? new MongoDate(strtotime(Input::get('start')))
            : new MongoDate(date_sub(new DateTime(), date_interval_create_from_date_string('1 years')));

        $end = Input::has('end')
            ? new MongoDate(strtotime(Input::get('end')))
            : new MongoDate();

        $response = DB::connection('pw2')->collection('projects')->raw(function($collection) use ($start, $end) {
            return $collection->aggregate([
                ['$match' => [
                    'bid_date' => [ '$gte' => $start, '$lt' => $end ]
                ]],
                ['$group' => [
                    '_id' => [
                        'date' => [
                            'year' => ['$year' => '$bid_date'],
                            'month' => ['$month' => '$bid_date']
                        ]
                    ],
                    'total' => ['$sum' => 1]
                ]]
            ]);
        });

        $data = [];
        foreach ($response['result'] as $row)
        {
            $yr = $row['_id']['date']['year'];
            $mn = $row['_id']['date']['month'];

            $data[] = [
                'date' => mktime(0, 0, 0, $mn, 1, $yr),
                'total' => $row['total']
            ];
        }

        usort($data, function($a, $b)
        {
            return $a['date'] - $b['date'];
        });

        return Response::prettyjson($data);
    }

}
