<?php namespace ProductionWeb;

use App;
use BaseController;
use DB;
use Input;
use Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class VinylPickListController extends BaseController
{
    private $select =
        'SELECT P.branch, O.id, O.ordernumber, O.linenumber, O.boxnumber, L.productcode, O.picklistid, O.racknumber, O.status, O.lastupdate,
                IF(L.productcode = "R", CONCAT("RETURN - ", O.itemdescription), O.itemdescription) AS itemdescription,
                O.loadingcomplete, O.shippingcomplete, SU.FV_UnitID, SCR.screentype, S.description AS statusdescription, S.isnotexception';

    private $from =
        'FROM production.orderstatus AS O
         JOIN production.linestatus AS L ON L.ordernumber = O.ordernumber AND L.linenumber = O.linenumber
         JOIN production.status AS S ON S.statusid = O.status
         LEFT JOIN production.picklist AS P ON P.id = O.picklistid
         LEFT JOIN production.sustatus AS SU ON
        	 SU.OrderNumber = O.ordernumber AND
        	 SU.LineNumber = O.linenumber AND
        	 SU.FrameNumber = "SU" AND
        	 O.boxnumber = 0
         LEFT JOIN screens.screens AS SCR ON SCR.ordernumber = O.ordernumber AND SCR.LineNumber = O.linenumber AND O.boxnumber = 0';

    public function index()
    {
        $query = VinylOrderStatus
            ::select('picklistid', 'ordernumber', 'linenumber', 'boxnumber', 'racknumber', 'status', 'itemdescription')
            ->where('status', '>', 80)
            ->where('status', '<', 1400)
            ->where(DB::raw('DATEDIFF(CURRENT_DATE(), lastupdate)'), '<', 365);

        $lastUpdate = Input::get('lastupdate', null);
        if ($lastUpdate) {
            $query->where(DB::raw('UNIX_TIMESTAMP(lastupdate)'), '>=', $lastUpdate);
        }

        $ret = [
            'total' => $query->count(),
            'data' => $query
                ->orderBy('picklistid', 'asc')
                ->orderBy('racknumber', 'asc')
                ->orderBy('status', 'asc')
                ->orderBy('ordernumber', 'asc')
                ->orderBy('boxnumber', 'asc')
                ->orderBy('linenumber', 'asc')
                ->get()
        ];

        return Response::prettyjson($ret);
    }

    public function get($id)
    {
        $dbh = new \PDO('mysql:host=mysql.starlinewindows.com;dbname=production', 'barcodescanner', 'scanB@rcode');
        $stmt = $dbh->prepare("
            $this->select
            FROM production.picklist AS P
            JOIN production.orderstatus AS O ON O.picklistid = P.id
            JOIN production.status AS S ON S.statusid = O.status
            JOIN production.linestatus AS L ON L.ordernumber = O.ordernumber AND L.linenumber = O.linenumber
            LEFT JOIN production.sustatus AS SU ON
                SU.OrderNumber = O.ordernumber AND
                SU.LineNumber = O.linenumber AND
                SU.FrameNumber = 'SU' AND
                O.boxnumber = 0
            LEFT JOIN screens.screens AS SCR ON SCR.ordernumber = O.ordernumber AND SCR.LineNumber = O.linenumber AND O.boxnumber = 0
            WHERE P.id = ? AND L.productcode NOT IN ('S', 'I', 'R') AND (O.status = 0 OR O.status > 80)
            GROUP BY O.ordernumber, O.boxnumber, O.linenumber
            ORDER BY O.racknumber, O.ordernumber, O.boxnumber, O.linenumber
        ");
        $stmt->execute([$id]);

        $count = 0;
        $index = -1;
        $racks = [];
        $grouped = [];

        while ($row = $stmt->fetchObject()) {
            if (! array_key_exists($row->racknumber, $racks)) {
                $index++;
                $racks[$row->racknumber] = $index;
                $grouped[$index] = [
                    'racknumber' => $row->racknumber,
                    'location' => $this->_getBayLocation($row->racknumber),
                    'data' => []
                ];
            }

            ++$count;
            $grouped[$index]['data'][] = $row;
        }

        return Response::prettyjson([
            'count' => $count,
            'data' => $grouped
        ]);
    }

    public function getItem()
    {
        $order_number = Input::get('orderNumber');
        $box_or_line_number = Input::get('boxOrLineNumber');

        $sql = "
            $this->select
            $this->from
            WHERE O.ordernumber = ? AND ? IN (O.boxnumber, O.linenumber)
            LIMIT 1
        ";

        $data = DB::connection('production-vinyl')->select($sql, [$order_number, $box_or_line_number]);
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

    public function getItemFromScreenBarcode($barcode)
    {
        $order_number = substr($barcode, 3, 6);
        $screen_type = substr($barcode, 9);

        $sql = "
            $this->select
            FROM production.orderstatus AS O
            JOIN production.linestatus L ON L.ordernumber = O.ordernumber AND L.linenumber = O.linenumber
            JOIN production.status AS S ON S.statusid = O.status
            JOIN screens.screens AS SCR ON SCR.ordernumber = O.ordernumber AND SCR.LineNumber = O.linenumber
            LEFT JOIN production.picklist AS P ON P.id = O.picklistid
            LEFT JOIN production.sustatus AS SU ON
                SU.OrderNumber = O.ordernumber AND
                SU.LineNumber = O.linenumber AND
                SU.FrameNumber = 'SU' AND
                O.boxnumber = 0
            WHERE O.ordernumber = ? AND O.boxnumber = 0 AND (O.status = 0 OR O.status >= 80) AND SCR.screentype = ?
            LIMIT 1
        ";

        $data = DB::connection('production-vinyl')->select($sql, [$order_number, $screen_type]);
        return Response::prettyjson($data[0]);
    }

    public function getItemFromSUBarcode($barcode)
    {
        $sql = "
            $this->select
            FROM production.sustatus AS SU
            JOIN production.orderstatus AS O ON
                O.ordernumber = SU.OrderNumber AND
                O.linenumber = O.LineNumber AND
                O.boxnumber = 0
            JOIN production.linestatus AS L ON L.ordernumber = O.ordernumber AND L.linenumber = O.linenumber
            JOIN production.status AS S ON S.statusid = O.status
            LEFT JOIN production.picklist AS P ON P.id = O.picklistid
            WHERE SU.FV_UnitID = ?
            LIMIT 1
        ";

        $data = DB::connection('production-vinyl')->select($sql, [$barcode]);
        return Response::prettyjson($data[0]);
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
            ORDER BY O.ordernumber, O.boxnumber, O.linenumber
        ";

        $data = DB::connection('production-vinyl')->select($sql, [$order_number]);
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
            ORDER BY ordernumber, boxnumber, linenumber
        ";

        $data = DB::connection('production-vinyl')->select($sql, [$rack_number]);
        $count = count($data);

        if ($count < 1) {
            return Response::prettyjson(['count' => 0, 'data' => []]);
        }
        return Response::prettyjson(['count' => $count, 'data' => $data]);
    }

    public function getRack($racknumber)
    {
        $data = VinylOrderStatus::where('racknumber', $racknumber)->get();
        if ($data && count($data) > 0) {
            return Response::prettyjson($data);
        }

        return Response::prettyjson([
            'error' => 'Not found',
            'input' => ['racknumber' => $racknumber]
        ], 400);
    }

    public function update()
    {
        $employeeId = Input::get('employeeId', 0);
        $item = Input::get('item', null);
        if (! ($item && $item['ordernumber'] && ($item['boxnumber'] || $item['linenumber']))) {
            return Response::prettyjson(['error' => 'required input missing'], 400);
        }

        try {
            $scantime = array_key_exists('scantime', $item) ? date('Y-m-d H:i:s', strtotime($item['scantime'])) : date('Y-m-d H:i:s');
            $shipped = intval($item['status']) === 1400;

            if (intval($item['boxnumber']) > 0 && $shipped) {
                $racknumber = 0;
            } elseif (intval($item['boxnumber']) === 0) {
                $racknumber = "M{$item['linenumber']}V";
            } else {
                $racknumber = $item['racknumber'];
            }

            // insert into scanlogs
            App::finish(function () use ($item, $racknumber, $employeeId, $scantime) {
                $sql = '
                    INSERT INTO scanlogs (stationid, ordernumber, boxnumber, status, scantime, bluerackid, operatorid)
                    VALUES (0, ?, ?, ?, ?, ?, ?);
                ';

                $dbh = new \PDO('dblib:host=192.168.1.14\sqlmaster;dbname=production', 'barcodescanner', 'scanB@rcode');
                $stmt = $dbh->prepare($sql);

                $stmt->execute([
                    $item['ordernumber'],
                    $item['boxnumber'],
                    $item['status'],
                    $scantime,
                    $racknumber,
                    $employeeId
                ]);
            });

            // direct update of orderstatus
            $dbh = new \PDO('mysql:host=mysql.starlinewindows.com;dbname=production', 'barcodescanner', 'scanB@rcode');
            $stmt = $dbh->prepare('
                UPDATE production.orderstatus
                SET status = ?, racknumber = ?, lastupdate = CURRENT_TIMETAMP()
                WHERE ordernumber = ? AND linenumber = ? AND boxnumber = ?
                LIMIT 1
            ');

            $ret = $stmt->execute([
                $item['status'],
                ($shipped ? 0 : $item['racknumber']),
                $item['ordernumber'],
                $item['linenumber'],
                $item['boxnumber']
            ]);

            $this->_logMobileScan($employeeId, $item, false);

            return Response::prettyjson($ret);
        } catch (\PDOException $e) {
            $this->_logMobileScan($employeeId, $item, true);
            return Response::prettyjson(['error' => $e->getMessage()], 500);
        }
    }

    public function updateMulti()
    {
        $employeeId = Input::get('employeeId', 0);
        $items = Input::get('items', []);

        // preprocess items to get branch
        $branch = [];
        foreach ($items as $item) {
            if (!array_key_exists($item['picklistid'], $branch)) {
                $branch[$item['picklistid']] = $this->_getBranch($item['picklistid']);
            }
        }

        try {
            // insert into scanlogs (after response return)
            App::finish(function () use ($items, $employeeId) {
                $sql = '
                    INSERT INTO scanlogs (stationid, ordernumber, boxnumber, status, scantime, bluerackid, operatorid)
                    VALUES (0, ?, ?, ?, ?, ?, ?);
                ';

                $log = new Logger('sql');
                $log->pushHandler(new StreamHandler(storage_path().'/logs/sql-' . date('Y-m-d') . '.log', Logger::INFO));

                $dbh = new \PDO('dblib:host=192.168.1.14\sqlmaster;dbname=production', 'barcodescanner', 'scanB@rcode');
                $stmt = $dbh->prepare($sql);

                foreach ($items as $item) {
                    if ($item['boxnumber'] < 101) {
                        $item['racknumber'] = "M{$item['linenumber']}V";
                    } elseif ($item['status'] === 1400) {
                        $item['racknumber'] = 0;
                    }

                    $scantime = array_key_exists('scantime', $item) ? date('Y-m-d H:i:s', strtotime($item['scantime'])) : date('Y-m-d H:i:s');

                    $data = [
                        $item['ordernumber'],
                        $item['boxnumber'],
                        $item['status'],
                        $scantime,
                        $item['racknumber'],
                        $employeeId
                    ];

                    $log->addInfo($sql, $data);
                    $stmt->execute($data);
                }
            });

            $ret = true; // success until failure

            // direct update of orderstatus
            $dbh = new \PDO('mysql:host=mysql.starlinewindows.com;dbname=production', 'barcodescanner', 'scanB@rcode');

            $sql = '
                UPDATE production.orderstatus
                SET status = ?, racknumber = ?, lastupdate = CURRENT_TIMESTAMP()
            ';

            switch ($items[0]['status']) {
                case 1300:
                    $sql .= ', loadingcomplete = ?';
                    break;
                case 1400:
                    $sql .= ', shippingcomplete = ?';
                    break;
            }

            $sql .= 'WHERE ordernumber = ? AND linenumber = ? AND boxnumber = ?
                     LIMIT 1';

            $stmt = $dbh->prepare($sql);

            $log = new Logger('sql');
            $log->pushHandler(new StreamHandler(storage_path().'/logs/vinyl-updateMulti-' . date('Y-m-d') . '.log', Logger::INFO));

            $picklistid = null;
            foreach ($items as $item) {
                $picklistid = $item['picklistid'];
                $racknumber = $item['status'] !== 1400 ? $item['racknumber'] : 0;

                $params = $item['status'] === 1300 || $item['status'] === 1400 ? [
                    $item['status'],
                    $racknumber,
                    array_key_exists('scantime', $item) ? date('Y-m-d H:i:s', strtotime($item['scantime'])) : date('Y-m-d H:i:s'),
                    $item['ordernumber'],
                    $item['linenumber'],
                    $item['boxnumber']
                ] : [
                    $item['status'],
                    $racknumber,
                    $item['ordernumber'],
                    $item['linenumber'],
                    $item['boxnumber']
                ];

                $log->addInfo($sql, $params);
                $ret = $ret && $stmt->execute($params);
            }

            $this->_logMobileScan($employeeId, $items, false);
            return Response::prettyjson($ret);
        } catch (\Exception $e) {
            $this->_logMobileScan($employeeId, $items, true);
            return Response::prettyjson(['error' => $e->getMessage()], 500);
        }
    }

    private function _clearCompletedRacksLocation($picklistid)
    {
        $dbh = new \PDO('mysql:host=mysql.starlinewindows.com;dbname=production', 'barcodescanner', 'scanB@rcode');
        $stmt = $dbh->prepare('
            SELECT racknumber
            FROM production.orderstatus
            WHERE picklistid = ?
            GROUP BY racknumber
            HAVING SUM(IF(status >= 1300 AND status < 1500, 1, 0)) = COUNT(1)
        ');
        $stmt->execute([$picklistid]);

        while ($row = $stmt->fetch()) {
            BayArea::where('rack_number', $row->racknumber)->delete();
        }
    }

    private function _getBayLocation($rack_number)
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

    private function _getBranch($picklistid)
    {
        return VinylProductionPicklist::where('id', $picklistid)->get(['branch']);
    }

    private function _logMobileScan($operator, $data, $is_error)
    {
        $dbh = new \PDO('mysql:host=archdb.starlinewindows.com;port=3306;dbname=production', 'wm2017', '3699DEC5-504d-4b30-a95b-52a12074d2b8', [\PDO::ATTR_PERSISTENT => false]);
        $stmt = $dbh->prepare('CALL production_app.sp_Log_MobileScan(1, ?, ?, ?);');
        $stmt->execute([$operator, json_encode($data), $is_error]);
    }
}
