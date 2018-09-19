<?php

class WMProjectsController extends \BaseController {

    public function getActiveProjectList()
    {
        return Response::prettyjson(
            WMProjectInfo
                ::select([
                    'project_info.ProjectID',
                    'project_info.ProjectName',
                    'project_info.SiteContact',
                    DB::raw('CONCAT_WS(", ", TRIM(project_info.ShipAddress), TRIM(project_info.ShipCity), TRIM(project_info.ShipProvince)) AS project_address'),
                    DB::raw('IFNULL(project_info.coordinator, CONCAT(coordinators.firstname, " ", coordinators.lastname)) AS coordinator'),
                    DB::raw('GROUP_CONCAT(DISTINCT CONCAT(project_colours.ColourCode, " - ", project_colours.ColourDesc)) AS project_colours')
                ])
                ->leftJoin('project_colours', 'project_colours.ProjectNumber', '=', 'project_info.ProjectID')
                ->leftJoin('project_coordinator', 'project_coordinator.ProjectNumber', '=', 'project_info.ProjectID')
                ->leftJoin('accounting.coordinators', 'project_coordinator.CoordinatorID', '=', 'coordinators.ID')
                ->whereIn('project_info.status', ['Data Entry', 'Sent to Vinyl'])
                ->groupBy('project_info.ProjectID')
                ->orderBy('project_info.ProjectName')
                ->get()
        );
    }

    public function getProjectInfo()
    {
        $filter = Input::get('filter', null);
        $status = null;//Input::get('status', null);

        if ($filter !== null)
        {
          $filter = json_decode($filter);
        }

        $where = function($query) use ($filter, $status)
        {
          if ($filter !== null)
          {
            foreach ($filter as $filter_key => $filter_value)
            {
                $query->where($filter_key, 'like', '%' . $filter_value . '%');
            }
          }

          if ($status !== null)
          {
              if (is_array($status))
              {
                  $query->whereIn('status', $status);
              }
              else
              {
                  $query->where('status', '=', $status);
              }
          }
          else
          {
              $query->whereIn('status', ['Data Entry', 'Completed', 'Sent to Vinyl']);
          }

        };

        return Response::prettyjson([
            'projectInfo' => WMProjectInfo::where($where)->orderBy('ProjectName')->get()
        ]);
    }

    public function getBuildingInfo()
    {
        $filter = Input::get('filter', []);
        foreach ($filter as $filter_item) {
            ProjectInfo::where($filter_item[0], 'like', '%' . $filter_item[1] . '%');
        }

        $project_id = Input::get('project_id');

        return Response::prettyjson(array(
                'buildingInfo' => WMBuildingInfo::where('ProjectNumber', '=', $project_id)
                    ->orderBy('BuildingNumber')
                    ->get())
        );
    }

    public function getFloorInfo()
    {
        $filter = Input::get('filter', []);
        foreach ($filter as $filter_item) {
            ProjectInfo::where($filter_item[0], 'like', '%' . $filter_item[1] . '%');
        }

        $project_id = Input::get('project_id');
        $building_id = Input::get('building_id');

        return Response::prettyjson(array(
                'floorInfo' => WMFloorInfo
                    ::where('ProjectNumber', '=', $project_id)
                    ->where('BuildingNumber', '=', $building_id)
                    ->where('FloorName', 'not like', '%NO%')
                    ->orderBy(\DB::raw('CAST(FloorNumber AS UNSIGNED)'))
                    ->get())
        );
    }

    public function getOrdersNotProcessed()
    {
        $window_orders = WMWindowsAssignedToFloor
            ::select('Project_ID', 'ProjectName', 'Building_ID', 'BuildingName', 'Location')
            ->addSelect(DB::raw('GROUP_CONCAT(DISTINCT FloorName ORDER BY CAST(Floor_ID AS UNSIGNED)) AS floors'))
            ->addSelect(DB::raw('SUM(Qty_Required - Qty_Processed) AS outstanding'))
            ->addSelect(DB::raw('MAX(Expected_Delivery_Date) AS delivery_date'))
            ->addSelect(DB::raw('MAX(LastModified) AS last_modified'))
            ->join('projects.project_info', function($join) {
                $join->on('project_info.ProjectNumber', '=', 'info_windows_assigned_to_floor.Project_ID');
            })
            ->join('projects.building_info', function($join) {
                $join->on('building_info.ProjectNumber', '=', 'info_windows_assigned_to_floor.Project_ID');
                $join->on('building_info.BuildingNumber', '=', 'info_windows_assigned_to_floor.Building_ID');
            })
            ->join('projects.floor_info', function($join) {
                $join->on('floor_info.ProjectNumber', '=', 'info_windows_assigned_to_floor.Project_ID');
                $join->on('floor_info.BuildingNumber', '=', 'info_windows_assigned_to_floor.Building_ID');
                $join->on('floor_info.FloorNumber', '=', 'info_windows_assigned_to_floor.Floor_ID');
            })
            ->whereRaw('info_windows_assigned_to_floor.Expected_Delivery_Date >= CURRENT_DATE()')
            ->where(DB::raw('LENGTH(info_windows_assigned_to_floor.Location)'), '>', 0)
            ->whereNotIn('info_windows_assigned_to_floor.Location', [
                'ON-HOLD', 'PRACTICE', 'skiprun'
            ])
            ->where('info_windows_assigned_to_floor.Location', 'not like', '%north%')
            ->where('info_windows_assigned_to_floor.Location', 'not like', '%east%')
            ->where('info_windows_assigned_to_floor.Location', 'not like', '%south%')
            ->where('info_windows_assigned_to_floor.Location', 'not like', '%west%')
            ->where(function($query) {
                $query->where('project_info.status', '=', 'Data Entry');
                $query->orWhere('project_info.status', '=', 'Sent to Vinyl');
            })
            ->where(DB::raw('Qty_Required - Qty_Processed'), '>', 0)
            ->groupBy('Project_ID')->groupBy('Building_ID')->groupBy('Location')
            ->orderBy('delivery_date', 'asc')
            ->get();

        foreach ($window_orders as $order)
        {
          $colours = WMWindowsAssignedToFloor
            ::select(DB::raw('GROUP_CONCAT(DISTINCT CONCAT_WS("/", ExtColor, IntColor)) AS Colours'))
            ->join('wmweb.wm_frame', function($join)
            {
                $join->on('wm_frame.ProjectNum', '=', 'info_windows_assigned_to_floor.Project_ID');
                $join->on('wm_frame.WindowNum', '=', 'info_windows_assigned_to_floor.Window_Name');
            })
            ->where('Project_ID', '=', $order->Project_ID)
            ->where('Building_ID', '=', $order->Building_ID)
            ->where('Location', '=', $order->Location)
            ->where('FrameType', '!=', 'IMAGINARY')
            ->get();

          if ($colours && count($colours) === 1)
          {
            $order->colours = str_replace(',', ', ', $colours[0]->Colours);
          }

          $order->frames = WMWindowsAssignedToFloor
            ::select('FrameType')
            ->addSelect(DB::raw('GROUP_CONCAT(DISTINCT CONCAT_WS("/", ExtColor, IntColor)) AS Colours'))
            ->addSelect(DB::raw('SUM(Qty_Required - Qty_Processed) AS TotalFrames'))
            ->join('wmweb.wm_frame', function($join)
            {
                $join->on('wm_frame.ProjectNum', '=', 'info_windows_assigned_to_floor.Project_ID');
                $join->on('wm_frame.WindowNum', '=', 'info_windows_assigned_to_floor.Window_Name');
            })
            ->where('Project_ID', '=', $order->Project_ID)
            ->where('Building_ID', '=', $order->Building_ID)
            ->where('Location', '=', $order->Location)
            ->where('FrameType', '!=', 'IMAGINARY')
            ->groupBy('FrameType')
            ->get();
        }

        $oe_orders = WMOEOrdersAssigned
            ::where('ProcessReference', '=', 0)
            ->whereRaw('Entered >= DATE_ADD(CURRENT_DATE(), INTERVAL -14 DAY)')
            ->orderBy('DeliveryDate', 'ASC')
            ->get();

        return Response::prettyjson([
            'window_orders' => $window_orders,
            'oe_orders' => $oe_orders
        ]);
    }

    public function getProductionSummary($order_number)
    {
        $sql = "CALL workorders.GetProductionSummary(?)";

        try {
            $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=workorders';
            $dbh = new \PDO($connection_string, 'wm2017', '3699DEC5-504d-4b30-a95b-52a12074d2b8', array( \PDO::ATTR_PERSISTENT => false));
            $stmt = $dbh->prepare($sql);

            // call the stored procedure
            $stmt->execute(array($order_number));
            $data = $stmt->fetchObject();

            return \Response::prettyjson(array(
                'success' => true,
                'productionSummary' => $data
            ));
        } catch (\PDOException $e) {
            return \Response::prettyjson(array(
                'success' => false,
                'error' => $e->getMessage()
            ));
        }
    }

    public function getProcessingTotals()
    {
        $start = 'CURRENT_DATE()';
        if (Input::has('start')) {
            $start = new DateTime(Input::get('start'));
            $start = '"'.$start->format('Y-m-d').'"';
        }

        $end = 'CURRENT_TIMESTAMP()';
        if (Input::has('end')) {
            $end = new DateTime(Input::get('end'));
            $end = '"'.$end->format('Y-m-d').' 23:59:59"';
        }

        $sql = join("\r\n", [
            '  SELECT dateprocessed, frameseries, ',
            '         SUM(total_frames)    AS total_frames, ',
            '         SUM(total_su)        AS total_su, ',
            '         SUM(total_panels)    AS total_panels, ',
            '         SUM(total_spandrel)  AS total_spandrel, ',
            '         SUM(total_laminated) AS total_laminated ',
            '    FROM (',

            // Frames
            '                 SELECT O.dateprocessed, FP.frameseries, ',
            '                        SUM(L.quantity) AS total_frames, ',
            '                        0 AS total_su, ',
            '                        0 AS total_panels, ',
            '                        0 AS total_spandrel, ',
            '                        0 AS total_laminated ',
            '                   FROM workorders.orders AS O ',
            '             INNER JOIN workorders.`lines` AS L ',
            '                     ON L.ordernumber = O.ordernumber ',
            '             INNER JOIN workorders.frameproducts AS FP ',
            '                     ON FP.ordernumber = L.ordernumber AND FP.linenumber = L.linenumber',
            '        LEFT OUTER JOIN projects.workorder_cancel_log AS CL ',
            '                     ON CL.listnumber = O.processreference AND CL.wonumber LIKE CONCAT("%", O.ordernumber, "%")',
            '                  WHERE O.dateprocessed BETWEEN '.$start.' AND '.$end,
            '                    AND CL.wonumber IS NULL AND O.invoiceonly != 88',
            '               GROUP BY O.dateprocessed, FP.frameseries ',

            '                  UNION ',

            // Sealed Units
            '                 SELECT O.dateprocessed, FP.frameseries, ',
            '                        0 AS total_frames, ',
            '                        SUM(L.quantity) AS total_su, ',
            '                        0 AS total_panels, ',
            '                        0 AS total_spandrel, ',
            '                        0 AS total_laminated ',
            '                   FROM workorders.orders O ',
            '             INNER JOIN workorders.`lines` L ',
            '                     ON L.ordernumber = O.ordernumber',
            '             INNER JOIN workorders.frameproducts AS FP ',
            '                     ON FP.ordernumber = L.ordernumber AND FP.linenumber = L.linenumber',
            '             INNER JOIN workorders.sealedunits S ',
            '                     ON S.ordernumber = FP.ordernumber AND S.linenumber = FP.linenumber',
            '                    AND S.frameindex = FP.frameindex',
            '        LEFT OUTER JOIN projects.workorder_cancel_log AS CL ',
            '                     ON CL.listnumber = O.processreference AND CL.wonumber LIKE CONCAT("%", O.ordernumber, "%")',
            '                  WHERE O.dateprocessed BETWEEN '.$start.' AND '.$end,
            '                    AND CL.wonumber IS NULL',
            '                    AND ( ( ( S.glass_outside != "NA" ) ',
            '                            AND ( S.glass_outside IN (SELECT G.`code` ',
            '                                                        FROM mfg.glasstypes G ',
            '                                                       WHERE G.gtype = "GLASS" ',
            '                                                         AND G.category != "SPANDRL" ',
            '                                                         AND G.description NOT LIKE "%LAM%") ) ) ',
            '                            OR ( ( S.glass_inside != "NA" ) ',
            '                                    AND ( S.glass_inside IN (SELECT G.`code` ',
            '                                                               FROM mfg.glasstypes G ',
            '                                                              WHERE  G.gtype = "GLASS" ',
            '                                                                AND G.category != "SPANDRL" ',
            '                                                                AND G.description NOT LIKE "%LAM%") ',
            '                               ) ) ) ',
            '               GROUP BY O.dateprocessed, FP.frameseries ',

            '                  UNION ',

            // Panels
            '                 SELECT O.dateprocessed, FP.frameseries, ',
            '                        0 AS total_frames, ',
            '                        0 AS total_su, ',
            '                        SUM(L.quantity) AS total_panels, ',
            '                        0 AS total_spandrel, ',
            '                        0 AS total_laminated ',
            '                   FROM workorders.orders AS O ',
            '             INNER JOIN workorders.`lines` AS L ',
            '                     ON L.ordernumber = O.ordernumber ',
            '             INNER JOIN workorders.frameproducts AS FP ',
            '                     ON FP.ordernumber = L.ordernumber AND FP.linenumber = L.linenumber ',
            '             INNER JOIN workorders.panels AS P ',
            '                     ON P.ordernumber = FP.ordernumber AND P.linenumber = FP.linenumber ',
            '                    AND P.frameindex = FP.frameindex',
            '        LEFT OUTER JOIN projects.workorder_cancel_log AS CL ',
            '                     ON CL.listnumber = O.processreference AND CL.wonumber LIKE CONCAT("%", O.ordernumber, "%")',
            '                  WHERE O.dateprocessed BETWEEN '.$start.' AND '.$end,
            '                    AND CL.wonumber IS NULL',
            '               GROUP BY O.dateprocessed, FP.frameseries ',

            '                  UNION ',

            // Spandrel
            '                 SELECT O.dateprocessed, FP.frameseries, ',
            '                        0 AS total_frames, ',
            '                        0 AS total_su, ',
            '                        0 AS total_panels, ',
            '                        SUM(IF (G.`category` = "SPANDRL" OR G.`category` LIKE "%FRIT%", L.`quantity`, 0)) AS total_spandrel, ',
            '                        0 AS total_laminated ',
            '                   FROM workorders.orders AS O ',
            '             INNER JOIN workorders.`lines` AS L ',
            '                     ON L.ordernumber = O.ordernumber',
            '             INNER JOIN workorders.frameproducts AS FP ',
            '                     ON FP.ordernumber = L.ordernumber AND FP.linenumber = L.linenumber',
            '             INNER JOIN workorders.sealedunits AS S ',
            '                     ON S.ordernumber = FP.ordernumber AND S.linenumber = FP.linenumber ',
            '                    AND S.frameindex = FP.frameindex',
            '             INNER JOIN mfg.glasstypes AS G ',
            '                     ON G.`code` = S.glass_inside OR G.`code` = S.glass_outside',
            '        LEFT OUTER JOIN projects.workorder_cancel_log AS CL ',
            '                     ON CL.listnumber = O.processreference AND CL.wonumber LIKE CONCAT("%", O.ordernumber, "%")',
            '                  WHERE O.dateprocessed BETWEEN '.$start.' AND '.$end,
            '                    AND CL.wonumber IS NULL',
            '               GROUP BY O.dateprocessed, FP.frameseries ',

            '                  UNION ',

            // Lami
            '                 SELECT O.dateprocessed, FP.frameseries, ',
            '                        0 AS total_frames, ',
            '                        0 AS total_su, ',
            '                        0 AS total_panels, ',
            '                        0 AS total_spandrel, ',
            '                        SUM(IF (G.description LIKE "%LAM%", L.quantity, 0)) AS total_laminated ',
            '                   FROM workorders.orders AS O ',
            '             INNER JOIN workorders.`lines` AS L ',
            '                     ON L.ordernumber = O.ordernumber',
            '             INNER JOIN workorders.frameproducts AS FP ',
            '                     ON FP.ordernumber = L.ordernumber AND FP.linenumber = L.linenumber',
            '             INNER JOIN workorders.sealedunits AS S ',
            '                     ON S.ordernumber = FP.ordernumber AND S.linenumber = FP.linenumber ',
            '                    AND S.frameindex = FP.frameindex',
            '             INNER JOIN mfg.glasstypes AS G ',
            '                     ON G.`code` = S.glass_inside OR G.`code` = S.glass_outside',
            '        LEFT OUTER JOIN projects.workorder_cancel_log AS CL ',
            '                     ON CL.listnumber = O.processreference AND CL.wonumber LIKE CONCAT("%", O.ordernumber, "%")',
            '                  WHERE O.dateprocessed BETWEEN '.$start.' AND '.$end,
            '                    AND CL.wonumber IS NULL',
            '               GROUP BY O.dateprocessed, FP.frameseries ',

            '         ) AS totals ',
            'GROUP BY dateprocessed, frameseries',
            'ORDER BY dateprocessed DESC, frameseries'
        ]);
        $processingTotals = DB::connection('work-orders')->select(DB::raw($sql));

        $sql = "
          SELECT
            date_processed,
            order_type,
            CONCAT('[', G.`code`, '] ', SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(G.description, ' ON ', 1), ' on ', 1), ' with ', 1)) as colour,
            SUM(total_qty) AS total_qty
          FROM (
            SELECT
              DATE_FORMAT(O.dateprocessed, '%Y-%m-%d') as date_processed,
              IF(O.processreference < 100, 'Rush', 'Regular') AS order_type,
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(specification, 'Primary Glass=', -1), ',', 1) AS CHAR) AS primary_glass,
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(specification, 'Secondary Glass=', -1), ',', 1) AS CHAR) AS secondary_glass,
              SUM(L.quantity) AS total_qty
            FROM
              workorders.orders AS O JOIN
              workorders.`lines` AS L ON L.ordernumber = O.ordernumber JOIN
              oe.order_details AS S ON S.ordernumber = O.ordernumber AND S.linenumber = L.linenumber
            WHERE
              O.prefix = 'P' AND O.dateprocessed BETWEEN {$start} AND {$end} AND S.product = 'Sealed Unit'
            GROUP BY
              date_processed, order_type, primary_glass, secondary_glass
            UNION
            SELECT
              DATE_FORMAT(O.dateprocessed, '%Y-%m-%d') as date_processed,
              IF(O.processreference < 100, 'Rush', 'Regular') AS order_type,
              S.glass_outside AS primary_glass, S.glass_inside AS secondary_glass,
              SUM(L.quantity) AS total_qty
            FROM workorders.orders AS O JOIN
                 workorders.`lines` AS L ON L.ordernumber = O.ordernumber JOIN
                 workorders.sealedunits AS S ON S.ordernumber = O.ordernumber AND S.linenumber = L.linenumber
            WHERE
              O.prefix = 'P' AND O.dateprocessed BETWEEN {$start} AND {$end}
            GROUP BY
              date_processed, order_type, primary_glass, secondary_glass
          ) AS TMP
          JOIN
            mfg.glasstypes AS G ON G.`code` IN (TMP.primary_glass, TMP.secondary_glass)
          WHERE
            G.`category` = 'SPANDRL'
          GROUP BY
            date_processed, order_type, colour
          ORDER BY
            date_processed DESC, SUBSTRING_INDEX(colour, '] ', -1), order_type
        ";
        $spandrelTotals = DB::connection('work-orders')->select(DB::raw($sql));

        return Response::prettyjson([
            'processingTotals' => $processingTotals,
            'spandrelTotals' => $spandrelTotals
        ]);
    }

    public function getWorkOrders3()
    {
        $project_id = Input::get('project_id', null);
        $building_id = Input::get('building_id', null);
        $floor_id = Input::get('floor_id', null);
        $sort = Input::get('sort', false);
        $filters = Input::get('filters', false);
        $start = Input::get('start', 0);
        $count = Input::get('count', 25);

        if ($sort !== false) {
            $sort = json_decode($sort);
        } else {
            $sort = new stdClass();
            $sort->predicate = 'dateprocessed';
            $sort->reverse = true;
        }

        if ($filters !== false) {
            $filters = json_decode($filters);
        }

        $getFilter = function($key) use ($filters) {
            if (isset($filters[$key])) {
                return $filters[$key];
            }
            return null;
        };

        $sql = 'CALL workorders.GetWorkOrderSummary(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';

        $param_array = array(
            $project_id,                    // projectid
            $building_id,                   // buildingid
            $floor_id,                      // floorid
            $getFilter('processreference'), // filter sul
            $getFilter('ordernumber'),      // filter order#
            $getFilter('projectid'),        // filter projectid
            $getFilter('location'),         // filter location
            $getFilter('operator'),         // filter operator
            $getFilter('dateprocessed'),    // filter date proc
            $getFilter('deliverydate'),     // filter date deliv
            $getFilter('su_arrival_date'),  // filter date su arriv
        );

        // Get total
        if ($filters) {
            $orders = \DB::connection('archdb-wm')->select(\DB::raw($sql), $param_array + [ null, null, null, null ]);
            $total = count($orders);
        } else {

        }

        // Get page
        $param_array = $param_array + [
                $sort->predicate,               // predicate
                $sort->reverse,                 // reverse
                $start,                         // start
                $count                          // limit
            ];
        $orders = \DB::connection('archdb-wm')->select(\DB::raw($sql), $param_array);

        return Response::prettyjson(array(
            'total' => $total,
            'pageTotal' => count($orders),
            'workOrders' => $this->_getOrderDetails($orders)
        ));
    }

    public function getWorkOrders()
    {
        $project_id = Input::get('project_id', null);
        $building_id = Input::get('building_id', null);
        $floor_id = Input::get('floor_id', null);
        $sort = Input::get('sort', false);
        $filters = Input::get('filters', false);
        $start = Input::get('start', 0);
        $count = Input::get('count', 25);
        $filter_fields = [
            'O.project_id',
            'O.processreference',
            'O.ordernumber',
            'O.customername',
            'O.prioritymemo',
            'O.deliveryinstructions',
            'O.poreference',
            'O.documentreference',
            'O.buildingname',
            'O.deliverycity',
            'O.operator',
            'I.Location',
            'P.ProjectName',
            'I.buildings',
            'I.floors'
        ];

        if ($sort !== false)
        {
            $sort = json_decode($sort);
        }
        else
        {
            $sort = new stdClass();
            $sort->predicate = 'dateprocessed';
            $sort->reverse = true;
        }

        if ($filters !== false)
        {
            $filters = json_decode($filters);
        }

        // Build where clause
        $where = "WHERE\r\n";
        if ($project_id !== null)
        {
            $where .= "    O.project_id = '{$project_id}' AND O.dateprocessed IS NOT NULL\r\n";
        }
        else
        {
            $where .= "O.dateprocessed > DATE_ADD(CURRENT_DATE(), INTERVAL -6 MONTH)\r\n";
        }

        if ($building_id !== null)
        {
            $where .= " AND FIND_IN_SET('{$building_id}', I.buildings)";
        }

        if ($floor_id !== null)
        {
            $where .= " AND FIND_IN_SET('{$floor_id}', I.floors)";
        }

        // 'Like' filters
        if ($filters !== false)
        {
            foreach ($filters as $k => $v)
            {
                if (strlen($v) === 0) {
                    continue;
                }

                switch($k)
                {
                    case 'global':
                        $first = true;
                        $where .= " AND (\r\n";

                        foreach ($filter_fields as $field)
                        {
                            if (! $first)
                            {
                                $where .= ' OR';
                            }
                            $first = false;

                            $where .= " {$field} LIKE '%".$filters->global."%'\r\n";
                        }

                        $where .= ")\r\n";
                        break;

                    case '[meta-project]':
                        $where .= " AND (
                            P.ProjectName LIKE '%{$filters->{$k}}%' OR
                            P.ProjectNumber LIKE '%{$filters->{$k}}%'
                        )\r\n";
                        break;

                    case '[meta-info]':
                        $where .= " AND (
                            O.DeliveryInstructions LIKE '%{$filters->{$k}}%' OR
                            O.PriorityMemo LIKE '%{$filters->{$k}}%'
                        )\r\n";
                        break;

                    default:
                        $where .= ' AND '.$k." LIKE '%".$v."%'\r\n";
                        break;
                }
            }
        }

        $sql = "SELECT";

        if (strtolower($sort->predicate) === 'location')
        {
            $sql .= "
                CAST(TRIM(REPLACE(SUBSTRING_INDEX(I.location, ' ', -1), '#', '')) AS UNSIGNED) AS sort_value,
             ";
        }
        elseif ($sort->predicate === 'su_arrival_date')
        {
            $sql .= "
                I.su_arrival_date AS sort_value,
            ";
        }
        else
        {
            $sql .= "
                {$sort->predicate} AS sort_value,
            ";
        }

        $sql .= "
            I.*,
            GROUP_CONCAT(FI.FloorName) AS floors,
            O.*
        ";

        $sql .= '
            FROM workorders.orders AS O
            LEFT JOIN workorders.orders_info AS I ON I.order_number = O.ordernumber
            LEFT JOIN projects.floor_info AS FI ON FI.ProjectNumber = O.project_id AND FI.BuildingNumber = I.buildings AND FIND_IN_SET(FI.FloorNumber, I.floors)
            INNER JOIN projects.project_info AS P ON P.ProjectID = O.project_id
        ';

        $sql .= $where;

        $sql .= 'GROUP BY
            O.ordernumber
        ';

        $total = count(\DB::connection('archdb')->select(\DB::raw($sql)));

        $sql .= "ORDER BY
            {$sort->predicate}
        ";

        if ($sort->reverse)
        {
            $sql .= " DESC";
        }
        else
        {
            $sql .= "ASC";
        }

        if ($start > 0 || $count > -1)
        {
            $limit = 'LIMIT' . PHP_EOL . "\t";
            if ($start > 0)
            {
                $limit .= $start;
                if ($count > -1)
                {
                    $limit .= ', ' . $count;
                }
            }
            else if ($count > -1)
            {
                $limit .= $count;
            }

            $sql .= PHP_EOL . $limit;
        }

        $orders = \DB::connection('archdb')->select(\DB::raw($sql));

        return Response::prettyjson([
            'total' => $total,
            'pageTotal' => count($orders),
            'workOrders' => $this->_getOrderDetails($orders)
        ]);
    }

    public function updateExpectedDeliveryDate($project_id)
    {
      $user = JWTAuth::parseToken()->toUser();
      $authorizations = $user->getMergedPermissions();
      $groups = explode(',', $user->member_of);

      if (! (in_array('ArchWebsiteAdmin', $groups) || in_array('Managers', $groups)))
      {
        if (!array_key_exists('projects.forward-load.edit', $authorizations))
        {
          return Response::prettyjson('Not authorized', 403);
        }
      }

      $sql = '
        UPDATE projects.info_windows_assigned_to_floor
        SET Expected_Delivery_Date = ?
        WHERE Project_ID = ? AND Expected_Delivery_Date = ?
      ';

      $to_date = Input::get('toDate', null);
      $from_date = Input::get('fromDate', null);
      if (!$new_date || !$old_date)
      {
        return Response.prettyjson('Bad input', 400);
      }

      WMWindowsAssignedToFloor::statement(DB::raw($sql), [$to_date, $project_id, $from_date]);
      return 'Ok';
    }

    private function _getOrderDetails($orders)
    {
        $ret = [];
        foreach ($orders as $order)
        {
            $order_number = $order->ordernumber;
            $order_type = str_replace('WM_PROC_', '', $order->operator);

            // Get project
            $order->project = WMProjectInfo::where('ProjectNumber', '=', $order->project_id)->first();

            // Get production summary
            if ($order_type === 'REG' || $order_type === 'SVC' || $order_type === 'SPEC')
            {

                $sql = "CALL workorders.GetProductionSummary(?)";

                try
                {
                    // connect to db and prepare statement
                    $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=workorders';
                    $dbh = new \PDO($connection_string, 'wm2017', '3699DEC5-504d-4b30-a95b-52a12074d2b8', array( \PDO::ATTR_PERSISTENT => false));
                    $stmt = $dbh->prepare($sql);

                    // call the stored procedure
                    $stmt->execute(array($order_number));
                    $order->production_summary = $stmt->fetchObject();
                    $order->su_arrival_date = $order->production_summary->su_arrival_date;
                }
                catch (\PDOException $e)
                {
                    $order->productionSummary = null;
                }
            }

            // Get location (OE #)
            if ($order_type === 'REG' || $order_type === 'MISC' || $order_type === 'SPEC')
            {
                $locations = WMWindowsAssignedToFloor
                    ::where('Project_ID', '=', $order->project_id)
                    ->where(function ($query) use ($order_number) {
                        $query
                            ->where('wonumber', '=', $order_number)
                            ->orWhere('miscwonumber', '=', $order_number)
                            ->orWhere('miscwonumber2', '=', $order_number)
                            ->orWhere('miscwonumber3', '=', $order_number);
                    })
                    ->limit(1)
                    ->get(['Location']);

                if ($locations && count($locations) > 0)
                {
                    $location = $locations[0]->Location;
                    if ($location)
                    {
                        $order->Location = $location;
                    }
                }
            }
            else if ($order_type === 'OE')
            {
                $locations = WMOEOrderDetails
                    ::where('ordernumber', '=', $order_number)
                    ->limit(1)
                    ->get(['oenumber']);

                if ($locations && count($locations) > 0)
                {
                    $location = $locations[0]->oenumber;
                    if ($location)
                    {
                        $order->Location = $location;
                    }
                }
            }

            // Get cancel reason (if applicable)
            if ($order->removed > 0)
            {
                $sql = '
                    SELECT memo
                      FROM projects.workorder_cancel_log
                     WHERE listnumber = ? AND wonumber LIKE CONCAT("%", ?, "%")
                     LIMIT 1
                ';

                $cancel_reason = \DB::connection('archdb')
                    ->select(\DB::raw($sql), array($order->processreference, $order->ordernumber));

                if ($cancel_reason && count($cancel_reason) > 0)
                {
                    $cancel_reason = $cancel_reason[0]->memo;
                    $cancel_reason = str_replace("\r\n", '<br>', $cancel_reason);
                    $cancel_reason = str_replace(str_repeat('=', 72), '', $cancel_reason); // So ugly
                    $cancel_reason = str_replace(str_repeat('=', 21), '', $cancel_reason); // So ugly
                    $cancel_reason = str_replace('<br><br>', '<br>', $cancel_reason);

                    $order->cancel_reason = $cancel_reason;
                }
            }

            // Separate processing date and time
            $date_processed = DateTime::createFromFormat('Y-m-d H:i:s', $order->dateprocessed);
            $order->timeprocessed = $order->dateprocessed;
            $order->dateprocessed = $order->dateprocessed ? $date_processed->format('Y-m-d') : null;

            $ret[] = $order;
        }

        return $ret;
    }
}
