<?php namespace ProductionWeb;

use App;
use BaseController;
use DB;
use Input;
use Response;

class PickListController extends BaseController
{

    private $select =
        'SELECT P.branch, O.id, O.ordernumber, O.linenumber, O.boxnumber, L.productcode, O.picklistid, O.racknumber, O.status, O.lastupdate,
         IF(L.productcode = "R", CONCAT("RETURN - ", O.itemdescription), O.itemdescription) AS itemdescription,
         O.loadingcomplete, O.shippingcomplete, SU.FV_UnitID, S.description AS statusdescription, S.isnotexception';

    private $from =
        'FROM production.orderstatus O
         JOIN production.linestatus L ON L.ordernumber = O.ordernumber AND L.linenumber = O.linenumber
         JOIN production.status AS S ON S.statusid = O.status
         LEFT JOIN production.picklist P ON P.id = O.picklistid
         LEFT JOIN production.sustatus AS SU ON
             SU.OrderNumber = O.ordernumber AND
             SU.LineNumber = O.linenumber AND
             O.boxnumber = 0';

    private $orderby = 'ORDER BY O.ordernumber, O.boxnumber, O.linenumber';

    public function index()
    {
        $time_start = microtime(true);

        $query = OrderStatus
            ::select('picklistid', 'ordernumber', 'linenumber', 'boxnumber', 'racknumber', 'status', 'itemdescription')
            ->where('status', '>=', 80)
            ->where('picklistid', '!=', 0)
            ->where(DB::raw('DATEDIFF(CURRENT_DATE(), lastupdate)'), '<', 120);

        $lastUpdate = Input::get('lastupdate', null);
        if ($lastUpdate) {
            $query->where(DB::raw('UNIX_TIMESTAMP(lastupdate)'), '>=', $lastUpdate);
        }

        $data = $query
            ->orderBy('picklistid', 'asc')
            ->orderBy('racknumber', 'asc')
            ->orderBy('status', 'asc')
            ->orderBy('ordernumber', 'asc')
            ->orderBy('boxnumber', 'asc')
            ->orderBy('linenumber', 'asc')
            ->get();

        $picklists = [];
        $racks = [];
        $grouped = [];
        $picklistIdx = -1;
        $rackIdx = -1;

        foreach ($data as $row) {
            $picklistid = $row->picklistid;

            if (! array_key_exists($picklistid, $picklists)) {
                $picklistIdx++;
                $picklists[$picklistid] = $picklistIdx;
                $grouped[$picklistIdx] = [
                    'picklistid' => $picklistid,
                    'racks' => []
                ];
            }

            $racknumber = $row->racknumber;
            if ($racknumber < 1) {
                $racknumber = 'N/A';
            }

            if (! array_key_exists($picklistid.$racknumber, $racks)) {
                $rackIdx++;
                $racks[$picklistid.$racknumber] = $rackIdx;
                $grouped[$picklistIdx]['racks'][$rackIdx] = [
                    'racknumber' => $racknumber,
                    'location' => $this->fetchBayLocation($racknumber),
                    'data' => []
                ];
            }

            $grouped[$picklistIdx]['racks'][$rackIdx]['data'][] = $row;
        }

        $time_end = microtime(true);

        $ret = [
            'elapsed' => $time_end - $time_start,
            'total' => $query->count(),
            'data' => $grouped
        ];

        return Response::prettyjson($ret);
    }

    public function get($id)
    {
        if (! is_numeric($id)) {
            return Response::prettyjson(['error' => 'picklistid is not a number'], 400);
        }

        $sql = "
            $this->select
            FROM production.picklist AS P
            JOIN production.orderstatus AS O ON O.picklistid = P.id
            JOIN production.status AS S ON S.statusid = O.status
            JOIN production.linestatus AS L ON L.ordernumber = O.ordernumber AND L.linenumber = O.linenumber
            LEFT JOIN production.sustatus AS SU ON
                SU.OrderNumber = O.ordernumber AND
                SU.LineNumber = O.linenumber AND
                O.boxnumber = 0
            WHERE P.id = ? AND (O.status = 0 OR O.status > 80)
            GROUP BY O.ordernumber, O.boxnumber, O.linenumber
            ORDER BY O.racknumber, O.ordernumber, O.boxnumber, O.linenumber
        ";

        $data = DB::connection('production')->select($sql, [$id]);
        $count = count($data);

        if ($count < 1) {
            return Response::prettyjson(['error' => 'not found'], 404);
        }

        $racks = [];
        $grouped = [];
        $index = -1;
        foreach ($data as $row) {
            if (! array_key_exists($row->racknumber, $racks)) {
                $index++;
                $racks[$row->racknumber] = $index;
                $grouped[$index] = [
                    'racknumber' => $row->racknumber,
                    'location' => $this->fetchBayLocation($row->racknumber),
                    'data' => []
                ];
            }

            $grouped[$index]['data'][] = $row;
        }

        return Response::prettyjson([
            'count' => $count,
            'data' => $grouped
        ]);
    }

    public function getDatawedgeConfig()
    {
        $storage_path = storage_path();
        $file_path = "{$storage_path}/datawedge-shipping.db";
        return Response::download($file_path);
    }

    public function getItemsByOrderNumber($order_number)
    {
        if (! is_numeric($order_number)) {
            return Response::prettyjson(['error' => 'not a number'], 400);
        }

        $sql = "
            $this->select
            $this->from
            WHERE O.ordernumber = ? AND (O.status = 0 OR O.status >= 80)
            $this->orderby
        ";

        $data = DB::connection('production')->select($sql, [$order_number]);
        $count = count($data);

        if ($count < 1) {
            return Response::prettyjson(['error' => 'not found'], 404);
        }
        return Response::prettyjson(['count' => $count, 'data' => $data]);
    }

    public function getItemsByRackNumber($rack_number)
    {
        $sql = "
            $this->select
            $this->from
            WHERE O.racknumber = ? AND O.status != 1400
            $this->orderby
        ";

        $data = DB::connection('production')->select($sql, [$rack_number]);
        $count = count($data);

        if ($count < 1) {
            return Response::prettyjson(['count' => 0, 'data' => []]);
        }
        return Response::prettyjson(['count' => $count, 'data' => $data]);
    }

    public function getItem()
    {
        $order_number = Input::get('orderNumber');
        $box_or_line_number = Input::get('boxOrLineNumber');

        $sql = "
            $this->select
            $this->from
            WHERE O.ordernumber = ? AND ? IN (O.boxnumber, O.linenumber)
            $this->orderby
            LIMIT 1
        ";

        $data = DB::connection('production')->select($sql, [$order_number, $box_or_line_number]);
        if (isset($data) && count($data) > 0) {
            return Response::prettyjson($data[0]);
        }

        return Response::prettyjson([
            'input' => [
                $order_number,
                $box_or_line_number
            ],
            'error' => 'Not found'
        ], 400);
    }

    public function update()
    {
        $employeeId = Input::get('employeeId', 0);
        $item = Input::get('item', null);

        if (! ($item && $item['what'] && $item['ordernumber'] && ($item['boxnumber'] || $item['linenumber']))) {
            return Response::prettyjson(['error' => 'required input missing', 'item' => $item], 400);
        }

        $dbh = new \PDO('mysql:host=archdb.starlinewindows.com;port=3306;dbname=production', 'wm2017', '3699DEC5-504d-4b30-a95b-52a12074d2b8', [\PDO::ATTR_PERSISTENT => false]);

        try {
            if ($item['what'] === 'rack') {
                $stmt = $dbh->prepare('CALL production.updateRackNumber(?, ?, ?);');
                return Response::prettyjson($stmt->execute([$item['ordernumber'], $item['linenumber'], $item['racknumber']]));
            }

            $stmt = $dbh->prepare('CALL production.updatePickListStatus(?, ?, ?, ?, ?);');
            $ret = $stmt->execute([$item['status'], $item['picklistid'], $item['racknumber'], $item['ordernumber'], $item['boxnumber']]);

            $this->logMobileScan($employeeId, $item, false);

            return Response::prettyjson($ret);
        } catch (\PDOException $e) {
            $this->logMobileScan($employeeId, $item, true);
            return Response::prettyjson(['error' => $e->getMessage()], 500);
        }
    }

    public function update2()
    {
        $employeeId = Input::get('employeeId', 0);
        $items = Input::get('items', []);

        App::finish(function ($request, $response) use ($employeeId, $items) {
            $dbh = new \PDO('mysql:host=archdb.starlinewindows.com;port=3306;dbname=production', 'wm2017', '3699DEC5-504d-4b30-a95b-52a12074d2b8', [
                \PDO::ATTR_PERSISTENT => false
            ]);

            try {
                foreach ($items as $item) {
                    if ($item['racknumber'] === 'N/A') {
                        $item['racknumber'] = 0;
                    }

                    if ($item['what'] === 'status') {
                        $stmt = $dbh->prepare('CALL production.updatePickListStatus(?, ?, ?, ?, ?);');
                        $stmt->execute([$item['status'], $item['picklistid'], $item['racknumber'], $item['ordernumber'], $item['boxnumber']]);
                    } elseif ($item['what'] === 'rack') {
                        $stmt = $dbh->prepare('CALL production.updateRackNumber(?, ?, ?);');
                        $stmt->execute([$item['ordernumber'], $item['linenumber'], $item['racknumber']]);
                    }
                }

                $this->logMobileScan($employeeId, $items, false);
            } catch (\Exception $e) {
                file_put_contents('/tmp/arch-update-multi.error.log', $e->getMessage());
                $this->logMobileScan($employeeId, $items, true);
            }
        });

        return Response::prettyjson(true);
    }

    public function writeScanLog()
    {
        $operator = Input::get('operator', 0);
        $division = Input::get('division_id', -1);
        $payload = Input::get('payload', null);
        $isError = Input::get('isError', false);

        if ($division > -1 && !is_null($payload) && array_key_exists('scan', $payload) && array_key_exists('valid', $payload['scan'])) {
            $dbh = new \PDO('mysql:host=archdb.starlinewindows.com;port=3306;dbname=production', 'wm2017', '3699DEC5-504d-4b30-a95b-52a12074d2b8', [
                \PDO::ATTR_PERSISTENT => false
            ]);
            $stmt = $dbh->prepare('CALL production_app.sp_Log_MobileScan(?, ?, ?, ?);');
            $stmt->execute([$division, $operator, json_encode($payload), $isError]);
        }

        return 'ok';
    }

    private function fetchBayLocation($rack_number)
    {
        if ($rack_number < 1) {
            return null;
        }

        $rows = BayArea::where('rack_number', $rack_number)->get();
        if (isset($rows) && count($rows) > 0) {
            return $rows[0]->bay_number;
        }
        return null;
    }

    private function logMobileScan($operator, $data, $is_error)
    {
        $dbh = new \PDO('mysql:host=archdb.starlinewindows.com;port=3306;dbname=production', 'wm2017', '3699DEC5-504d-4b30-a95b-52a12074d2b8', [\PDO::ATTR_PERSISTENT => false]);
        $stmt = $dbh->prepare('CALL production_app.sp_Log_MobileScan(0, ?, ?, ?);');
        $stmt->execute([$operator, json_encode($data), $is_error]);
    }
}
