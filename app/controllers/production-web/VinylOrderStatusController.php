<?php namespace ProductionWeb;

use BaseController, DB, Input, Response, WMOrderStatus;

class VinylOrderStatusController extends BaseController {

    public function index()
    {
        $table_state = json_decode(Input::get('tableState', null));
        $filters = [
            'glazing_complete_start' => Input::get('glazing_complete_start', null),
            'glazing_complete_end'   => Input::get('glazing_complete_end', null)
        ];

        if ($table_state !== null)
        {
            $start = isset($table_state->pagination->start)
                ? $table_state->pagination->start
                : 0;

            $number = isset($table_state->pagination->number)
                ? $table_state->pagination->number
                : 10;

            if (isset($table_state->sort->predicate)) {
                $order_by = $table_state->sort->predicate;
                $order_dir = $table_state->sort->reverse ? 'desc' : 'asc';
            } else {
                $order_by = 'ordernumber';
                $order_dir = 'asc';
            }

            $where = function($query) use ($table_state, $filters)
            {
                if (! (isset($table_state->search) && isset($table_state->search->predicateObject)))
                {
                    return;
                }

                foreach ($table_state->search->predicateObject as $key => $value)
                {
                    if ($key === 'racknumber')
                    {
                        $query->where(DB::raw("CAST($key AS UNSIGNED)"), $value);
                    }
                    else if ($key === 'picklistid')
                    {
                        $query->where($key, $value);
                        $query->where('boxnumber', '>', 0);
                        $query->whereRaw('DATEDIFF(CURRENT_DATE(), lastupdate) < 365');
                    }
                    else
                    {
                        $query->where($key, 'like', "%{$value}%");
                    }
                }

                if ($filters['glazing_complete_start'] !== null)
                {
                    $query->where('glazingcomplete', '>=', $filters['glazing_complete_start']);
                }

                if ($filters['glazing_complete_end'] !== null)
                {
                    $query->where('glazingcomplete', '<=', $filters['glazing_complete_end']);
                }
            };
        }
        else
        {
            $order_by = 'ordernumber';
            $order_dir = 'asc';
            $start = 0;
            $number = 9001;

            $where = function($query) use ($filters)
            {
                if ($filters['glazing_complete_start'] !== null)
                {
                    $query->where('glazingcomplete', '>=', $filters['glazing_complete_start']);
                }

                if ($filters['glazing_complete_end'] !== null)
                {
                    $query->where('glazingcomplete', '<=', $filters['glazing_complete_end']);
                }
            };
        }

        $data = VinylOrderStatus::where($where);

        return Response::prettyjson([
           'total' => $data->count(),
           'data' => $data
               ->orderBy($order_by, $order_dir)
               ->orderBy('ordernumber', 'asc')
               ->orderBy('boxnumber', 'asc')
               ->skip($start)
               ->take($number)->get()
        ]);
    }

    public function show($ordernumber)
    {
      return null;

      $data = VinylOrderStatus
          ::select('orderstatus.*', DB::raw('IF(orders.project_id = "0029", CONCAT("[", project_info.ProjectName, "] ", orders.customername), project_info.ProjectName) AS project_name'))
          ->join('workorders.orders', 'orders.ordernumber', '=', 'orderstatus.ordernumber')
          ->join('projects.project_info', 'project_info.ProjectID', '=', 'orders.project_id')
          ->where('orderstatus.ordernumber', $ordernumber);

      return Response::prettyjson([
         'total' => $data->count(),
         'data' => $data->orderBy('orderstatus.ordernumber', 'asc')->orderBy('orderstatus.boxnumber', 'asc')->get()
      ]);
    }
}
