<?php namespace ProductionWeb;

use BaseController, DB, Input, Response;

class OrderStatusController extends BaseController {

    public function index()
    {
        $table_state = json_decode(Input::get('tableState', null));
        $filters = [
            'glazing_complete_start' => Input::get('glazing_complete_start', null),
            'glazing_complete_end'   => Input::get('glazing_complete_end', null),
            'glazing_station_id' => Input::get('glazing_station_id', null)
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

                if ($filters['glazing_station_id'] !== null)
                {
                  $query->where('glazingstationid', $filters['glazing_station_id']);
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

                if ($filters['glazing_station_id'] !== null)
                {
                  $query->where('glazingstationid', $filters['glazing_station_id']);
                }
            };
        }

        $data = OrderStatus
            ::select('orderstatus.*', 'project_info.ProjectName AS project_name')
            ->join('workorders.orders', 'orders.ordernumber', '=', 'orderstatus.ordernumber')
            ->join('projects.project_info', 'project_info.ProjectID', '=', 'orders.project_id')
            ->where($where);

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
      $data = OrderStatus
          ::select('orderstatus.*', DB::raw('IF(orders.project_id = "0029", CONCAT("[", project_info.ProjectName, "] ", orders.customername), project_info.ProjectName) AS project_name'))
          ->join('workorders.orders', 'orders.ordernumber', '=', 'orderstatus.ordernumber')
          ->join('projects.project_info', 'project_info.ProjectID', '=', 'orders.project_id')
          ->where('orderstatus.ordernumber', $ordernumber);

      return Response::prettyjson([
         'total' => $data->count(),
         'data' => $data->orderBy('orderstatus.ordernumber', 'asc')->orderBy('orderstatus.boxnumber', 'asc')->get()
      ]);
    }

    public function getProductionCounters()
    {
    	$ret = [];

        // aluminum data
        $sql = '
            SELECT StationID, ScanDate as _date, FrameCount, Name
            FROM production_app.vw_ScanlogsGlazing_Smry_Date_Station
            JOIN leanenterprise.departments ON stationid = stationidlist
            GROUP BY stationid, ScanDate
            HAVING Name IS NOT NULL AND LENGTH(Name) > 0
        ';

        $data = DB::connection('archdb-wm')->select(DB::raw($sql));
        $ret['alu'] = $this->_processCounterData($data);

        // vinyl data
        $sql = '
    		  SELECT glazingstationid AS StationID, stationdescription AS Name, DATE(glazingcomplete) AS _date, COUNT(1) AS FrameCount
    		    FROM production.orderstatus
    		    JOIN production.scanstation ON production.orderstatus.glazingstationid = production.scanstation.stationid
    		   WHERE glazingcomplete > (CURDATE() - INTERVAL 100 DAY) AND stationdescription IS NOT NULL AND stationdescription != "VBROBOT"
    		GROUP BY StationID, _date
        ';

        $data = DB::connection('production-vinyl')->select(DB::raw($sql));
        $ret['vinyl'] = $this->_processCounterData($data);

        return Response::prettyjson($ret);
    }

    private function _processCounterData($data)
    {
        $rows = [];
        $last = "";

        foreach ($data as $row)
        {
            if ($last == $row->StationID)
            {
                $rows[$row->StationID]['trend'][]=$row->FrameCount;
                $rows[$row->StationID]['dates'][]=$row->_date;
            }
            else
            {
                // new entry
                $entry['desc'] = $row->Name;
                $entry['trend'] = [];
                $entry['dates'] = [];
                $entry['plot'] = [];
                $entry['labels'] = [];

                if($last != "" && end($rows[$last]['dates']) != date("Y-m-d"))
                {
                    //add a zero
                    $rows[$last]['trend'][]=0;
                    $rows[$last]['dates'][]=date("Y-m-d");
                }

                $last = $row->StationID;
                $entry['trend'][] = $row->FrameCount;
                $entry['dates'][] = $row->_date;

                $rows[$row->StationID] = $entry;
            }
        }

        // add zero for last entry
        if($last!="" && (end($rows[$last]['dates'])!= date("Y-m-d"))){
            $rows[$last]['trend'][]=0;
            $rows[$last]['dates'][]=date("Y-m-d");

        }

        return $rows;
    }
}
