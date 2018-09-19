<?php

class ProductionQualityController extends \BaseController {

    public function getStatusCodes()
    {
        $start_code = Input::get('start');
        $end_code = Input::get('end');

        $data = DB::connection('archdb')
            ->table('production.status')
            ->where('isactive', '=', 1)
            ->where(function($query) use ($start_code, $end_code)
            {
                if ($start_code)
                {
                    $query->where('statusid', '>=', $start_code);
                }

                if ($end_code)
                {
                    $query->where('statusid', '<=', $end_code);
                }
            })
            ->orderby('statusid')
            ->get();

        Return Response::prettyjson($data);
    }

}