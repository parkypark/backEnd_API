<?php namespace ProductionWeb;

use BaseController, DB, Input, Response;

class PickListNewController extends BaseController {

    public function index()
    {
      $query = OrderStatus
        ::select('picklistid', 'ordernumber', 'linenumber', 'boxnumber', 'racknumber', 'status', 'itemdescription')
        ->where('status', '>=', 80)
        ->where('status', '<', 1400)
        ->where('picklistid', '!=', 0)
        ->where(DB::raw('DATEDIFF(CURRENT_DATE(), lastupdate)'), '<', 365);

      $lastUpdate = Input::get('lastupdate', null);
      if ($lastUpdate)
      {
        $query->where(DB::raw('UNIX_TIMESTAMP(lastupdate)'), '>=', $lastUpdate);
      }

      $count = $query->count();

      $rows = $query
        ->orderBy('picklistid', 'asc')
        ->orderBy('ordernumber', 'asc')
        ->orderBy('boxnumber', 'asc')
        ->orderBy('linenumber', 'asc')
        ->get();

      $picklist_count = 0;
      $data = [];
      foreach($rows as $row)
      {
        if (!array_key_exists($row->picklistid, $data))
        {
          $data[$row->picklistid] = [];
          $picklist_count++;
        }

        if($row->boxnumber < 101)
        {
          $line_number = str_pad($row->linenumber, 3, '0', STR_PAD_LEFT);
          $row->barcode = "M{$row->ordernumber}{$line_number}V";
        }
        else
        {
          $row->barcode = "X{$row->ordernumber}{$row->boxnumber}V";
        }
        
        $data[$row->picklistid][] = $row;
      }

      $ret = [
        'itemCount' => $count,
        'picklistCount' => $picklist_count,
        'data' => $data
      ];

      return Response::prettyjson($ret);
    }

    public function get($id)
    {
      $data = OrderStatus::where('picklistid', $id)
        ->orderBy('ordernumber', 'asc')
        ->orderBy('linenumber', 'asc')
        ->get();

      return Response::prettyjson($data);
    }

    public function getItem()
    {
      $order_number = Input::get('orderNumber');
      $box_or_line_number = Input::get('boxOrLineNumber');

      $data = OrderStatus::where('ordernumber', $order_number)
        ->where(function($query) use ($box_or_line_number)
        {
          $query->where('boxnumber', $box_or_line_number)->orWhere('linenumber', $box_or_line_number);
        })
        ->get();

      if (isset($data) && count($data) > 0)
      {
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
      $item = Input::get('item', null);
      if (! ($item && $item['what'] && $item['ordernumber'] && ($item['boxnumber'] || $item['linenumber'])))
      {
        return Response::prettyjson(['error' => 'required input missing', 'item' => $item], 400);
      }

      try
      {
        $dbh = new \PDO('mysql:host=archdb.starlinewindows.com;port=3306;dbname=production', 'wm2017', '3699DEC5-504d-4b30-a95b-52a12074d2b8', [\PDO::ATTR_PERSISTENT => false]);

        if ($item['what'] === 'rack')
        {
          $stmt = $dbh->prepare('CALL production.updateRackNumber(?, ?, ?);');
          return Response::prettyjson($stmt->execute([$item['ordernumber'], $item['linenumber'], $item['racknumber']]));
        }

        $stmt = $dbh->prepare('CALL production.updatePickListStatus(?, ?, ?, ?, ?);');
        return Response::prettyjson($stmt->execute([$item['status'], $item['picklistid'], $item['racknumber'], $item['ordernumber'], $item['boxnumber']]));
      }
      catch (\PDOException $e)
      {
        return Response::prettyjson(['error' => $e->getMessage()], 500);
      }
    }

    public function update2()
    {
      try
      {
        $dbh = new \PDO('mysql:host=archdb.starlinewindows.com;port=3306;dbname=production', 'wm2017', '3699DEC5-504d-4b30-a95b-52a12074d2b8', [\PDO::ATTR_PERSISTENT => false]);

        $items = Input::get('items', []);
        $ret = true;

        foreach($items as $item)
        {
          if ($item['what'] === 'status')
          {
            $stmt = $dbh->prepare('CALL production.updatePickListStatus(?, ?, ?, ?, ?);');
            $ret = $ret && $stmt->execute([$item['status'], $item['picklistid'], $item['racknumber'], $item['ordernumber'], $item['boxnumber']]);
          }
          else if ($item['what'] === 'rack')
          {
            $stmt = $dbh->prepare('CALL production.updateRackNumber(?, ?, ?);');
            $ret = $ret && $stmt->execute([$item['ordernumber'], $item['linenumber'], $item['racknumber']]);
          }
        }

        return Response::prettyjson($ret);
      }
      catch (\Exception $e)
      {
        return Response::prettyjson(['error' => $e->getMessage()], 500);
      }
    }

    public function test()
    {

      $data = OrderStatus
        ::select('picklistid', 'ordernumber', 'boxnumber', 'racknumber', 'status', 'itemdescription')
        ->where('status', '<', 1400)
        ->where(DB::raw('DATEDIFF(CURRENT_DATE(), lastupdate)'), '<', 365);

      return Response::prettyjson([
        'total' => $data->count(),
        'data' => $data
          ->orderBy('picklistid', 'asc')
          ->orderBy('ordernumber', 'asc')
          ->orderBy('boxnumber', 'asc')
          ->get()
      ]);
    }
}
