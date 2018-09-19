<?php namespace ProductionWeb;

use BaseController, DB, Input, Response, JWTAuth;

class ScheduleController extends BaseController {
  public function addComment()
  {
    $key = Input::get('key');
    if (!$key)
    {
      return Response::prettyjson(['error' => 'invalid input'], 400);
    }

    $comment = Input::get('comment');
    if (!$comment)
    {
      return Response::prettyjson(['error' => 'invalid input'], 400);
    }

    $sql = 'INSERT INTO production.schedules_comments (`key`, `comment`, `created_by`, `created_at`) VALUES (?, ?, ?, ?)';

    try
    {
      DB::connection('archdb-wm')->insert($sql, [$key, $comment['comment'], $comment['created_by'], date('Y-m-d H:i:s')]);
      return Response::prettyjson(true);
    }
    catch (Exception $ex)
    {
      return Response::prettyjson(false);
    }
  }

  public function colours($department)
  {
    $sql = '
      SELECT DISTINCT S.Color AS colour, C.description
      FROM production.schedules AS S
      LEFT JOIN ic.colors AS C ON C.colorcode = S.Color AND C.deleted = 0
      WHERE S.Department = ? AND (? = "All" OR FIND_IN_SET(S.SubDept1, ?))
      AND LENGTH(S.Color) > 0 AND S.DateScheduled IS NOT NULL
      AND S.QtyFinished < S.QtyRequired
      ORDER BY Colour
    ';

    $subdepartment = Input::get('subdepartment', 'All');
    $data = DB::connection('archdb-wm')->select(DB::raw($sql), [$department, $subdepartment, $subdepartment]);
    return Response::prettyjson($data);
  }

  public function colours2($department)
  {
    return $this->colours($department);
    /*
    $subdepartments = Input::get('subdepartment', []);
    $collection = DB::connection('cache')->table("productionSchedules{$department}");
    $colours = $collection
      ->whereNotNull('colorCode')
      ->whereIn('subDept1', explode(',', $subdepartments))
      ->select(['colorCode', 'colorName'])
      ->orderBy('colorCode')
      ->groupBy('colorCode')
      ->get();

    $ret = [];
    foreach($colours as $colour)
    {
      $ret[] = [
        'colour' => $colour['colorCode'],
        'description' => $colour['colorName']
      ];
    }
    return Response::prettyjson($ret);
    */
  }

  public function departments2()
  {
    $data = DB::connection('archdb-wm')->select('SELECT * FROM production.schedules_departments');

    $ret = [];
    foreach ($data as $row)
    {
      if (!array_key_exists($row->department, $ret))
      {
        $ret[$row->department] = [
          'name' => $row->department,
          'subdepartments' => []
        ];
      }

      if ($row->subdept1)
      {
        if (!array_key_exists($row->subdept1, $ret[$row->department]['subdepartments']))
        {
          $ret[$row->department]['subdepartments'][$row->subdept1] = [
            'name' => $row->subdept1,
            'subdepartments' => []
          ];
        }

        if ($row->subdept2 && !array_key_exists($row->subdept2, $ret[$row->department]['subdepartments'][$row->subdept1]['subdepartments']))
        {
          $ret[$row->department]['subdepartments'][$row->subdept1]['subdepartments'][$row->subdept2] = [
            'name' => $row->subdept2
          ];
        }
      }

    }
    return Response::prettyjson($ret);
  }

  public function departments()
  {
    $sql = 'SELECT DISTINCT Department FROM production.schedules WHERE LENGTH(Department) > 0';
    $data = DB::connection('archdb-wm')->select(DB::raw($sql), []);

    $ret = [];
    for ($i = 0; $i < count($data); ++$i)
    {
      $ret[] = $data[$i]->Department;
    }

    return Response::prettyjson($ret);
  }

  public function subdepartments()
  {
    $sql = '
      SELECT Department, SubDept1
      FROM production.schedules
      WHERE LENGTH(Department) > 0 AND LENGTH(SubDept1) > 0
      GROUP BY Department, SubDept1
      ORDER BY Department, SubDept1
    ';

    $data = DB::connection('archdb-wm')->select(DB::raw($sql), []);

    $ret = [];
    for ($i = 0; $i < count($data); ++$i)
    {
      if (!array_key_exists($data[$i]->Department, $ret))
      {
        $ret[$data[$i]->Department] = [];
      }
      $ret[$data[$i]->Department][] = $data[$i]->SubDept1;
    }

    return Response::prettyjson($ret);
  }

  public function scheduleDates2($department)
  {
    $subdepartments = Input::get('subdepartment');
    $mongo = new \MongoDB\Client('mongodb://reader:reader@127.0.0.1/cache');
    $db = $mongo->cache;
    $collection = "productionSchedules{$department}";
    $filter = $subdepartments ? ['subDept1' => ['$in' => explode(',', $subdepartments)]] : [];
    $dates = $db->{$collection}->distinct('dateScheduled', $filter);
    return Response::prettyjson($dates);
  }

  public function scheduleDates($department)
  {
    $sql = '
      SELECT DISTINCT DATE(DateScheduled) AS DateScheduled
      FROM production.schedules
      WHERE Department = ? AND (? = "All" OR FIND_IN_SET(SubDept1, ?))
      AND (DateScheduled >= CURRENT_DATE() OR QtyFinished < QtyRequired)
      AND DateScheduled IS NOT NULL
      ORDER BY DateScheduled
    ';

    $subdepartment = Input::get('subdepartment', 'All');
    $data = DB::connection('archdb-wm')->select(DB::raw($sql), [$department, $subdepartment, $subdepartment]);
    $ret = [];
    foreach ($data as $row)
    {
      $ret[] = $row->DateScheduled;
    }
    return Response::prettyjson($ret);
  }

  public function headers2($department)
  {
    $subdepartments = Input::get('subdepartment', null);
    $colour = Input::get('colour', null);
    $search = Input::get('search', null);
    $ready_only = Input::get('ready_only', 0);

    /*$mongo = new \MongoDB\Client('mongodb://reader:reader@127.0.0.1/cache');
    $db = $mongo->cache;
    $cursor = $collection->find(['$where' => 'this.qtyFinished < this.qtyRequired']);*/
    $collection = "productionSchedules{$department}";
    $data = DB::connection('cache')->table($collection)->where(function($query) use ($subdepartments, $colour, $search, $ready_only)
    {
      if ($subdepartments)
      {
        $query->whereIn('subDept1', explode(',', $subdepartments));
      }

      if ($colour)
      {
        $colour = json_decode($colour);
        $query->where('colorCode', $colour->colour);
      }

      if ($search)
      {
        // S.PicklistNum = pItemSearch OR S.PrdListNum = pItemSearch OR S.OrderNumber = pItemSearch OR S.JobDescription LIKE CONCAT('%', pItemSearch, '%')
        $query->where(function($q) use ($search)
        {
          $q->where('picklistReference', $search);
          $q->orWhere('sulNumber', $search);
          $q->orWhere('jobDescription', 'LIKE', $search);
        });
      }
    })
    ->get();

    $grouped = [
      'Data' => [],
      'TotalCategories' => 0,
      'TotalRequired' => 0,
      'TotalFinished' => 0
    ];

    foreach ($data as $document)
    {
      $key = $document['category1'];
      if ($key && $grouped['TotalCategories'] < 1)
      {
        $grouped['TotalCategories'] = 1;
      }
      if (! array_key_exists($key, $grouped['Data']))
      {
        $grouped['Data'][$key] = [
          'Data' => [],
          'TotalRequired' => 0,
          'TotalFinished' => 0
        ];
      }

      $key2 = $document['category2'];
      if ($key2 && $grouped['TotalCategories'] < 2)
      {
        $grouped['TotalCategories'] = 2;
      }
      if (! array_key_exists($key2, $grouped['Data'][$key]['Data']))
      {
        $grouped['Data'][$key]['Data'][$key2] = [
          'Comments' => $this->_getComments("{$key}-{$key2}"),
          'Data' => [],
          'TotalRequired' => 0,
          'TotalFinished' => 0
        ];
      }

      $key3 = $document['category3'];
      if ($key3 && $grouped['TotalCategories'] < 3)
      {
        $grouped['TotalCategories'] = 3;
      }
      if (! array_key_exists($key3, $grouped['Data'][$key]['Data'][$key2]['Data']))
      {
        $grouped['Data'][$key]['Data'][$key2]['Data'][$key3] = [
          'Data' => [],
          'TotalRequired' => 0,
          'TotalFinished' => 0
        ];
      }

      $key4 = $document['category4'];
      if ($key4 && $grouped['TotalCategories'] < 4)
      {
        $grouped['TotalCategories'] = 4;
      }
      if (! array_key_exists($key4, $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data']))
      {
        $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data'][$key4] = [
          'Data' => [],
          'PickReason' => '',
          'TotalRequired' => 0,
          'TotalFinished' => 0
        ];
      }

      $grouped['TotalRequired'] += $document['qtyRequired'];
      $grouped['TotalFinished'] += $document['qtyFinished'];

      $grouped['Data'][$key]['TotalRequired'] += $document['qtyRequired'];
      $grouped['Data'][$key]['TotalFinished'] += $document['qtyFinished'];

      $grouped['Data'][$key]['Data'][$key2]['TotalRequired'] += $document['qtyRequired'];
      $grouped['Data'][$key]['Data'][$key2]['TotalFinished'] += $document['qtyFinished'];

      $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['TotalRequired'] += $document['qtyRequired'];
      $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['TotalFinished'] += $document['qtyFinished'];

      $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data'][$key4]['TotalRequired'] += $document['qtyRequired'];
      $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data'][$key4]['TotalFinished'] += $document['qtyFinished'];

      if ($document['picklistReference'])
      {
        $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data'][$key4]['PickReason'] = $document['picklistReference'] . ' - ' . $document['pickReason'];
      }

      $document['Completed'] = $document['qtyRequired'] === $document['qtyFinished'];
      $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data'][$key4]['Data'][] = $document;
    }

    return Response::prettyjson($grouped);
  }

  public function headers($department)
  {
    $subdepartment = Input::get('subdepartment', 'All');
    $colour = Input::get('colour');
    if ($colour)
    {
      $colour = json_decode($colour);
      $colour = $colour->colour;
    }
    else
    {
      $colour = 'All';
    }
    $search = Input::get('search', null);
    $ready_only = Input::get('ready_only', 0);

    $sql = 'CALL production_app.sp_Get_ScheduleHeaders(?, ?, ?, ?, ?);';
    $data = DB::connection('archdb-wm')->select(DB::raw($sql), [$department, $subdepartment, $colour, $search, $ready_only]);
    $dataCount = count($data);

    $grouped = [
      'Data' => [],
      'TotalCategories' => 0,
      'TotalRequired' => 0,
      'TotalFinished' => 0
    ];

    if ($dataCount)
    {
      $grouped['TotalCategories'] = $data[0]->TotalCategories;

      for ($i = 0; $i < $dataCount; ++$i)
      {
        $key = $data[$i]->Category1;
        if (! array_key_exists($key, $grouped['Data']))
        {
          $grouped['Data'][$key] = [
            'Data' => [],
            'TotalRequired' => 0,
            'TotalFinished' => 0
          ];
        }

        $key2 = $data[$i]->Category2;
        if (! array_key_exists($key2, $grouped['Data'][$key]['Data']))
        {
          $grouped['Data'][$key]['Data'][$key2] = [
            'Comments' => $this->_getComments("{$key}-{$key2}"),
            'Data' => [],
            'TotalRequired' => 0,
            'TotalFinished' => 0
          ];
        }

        $key3 = $data[$i]->Category3;
        if (! array_key_exists($key3, $grouped['Data'][$key]['Data'][$key2]['Data']))
        {
          $grouped['Data'][$key]['Data'][$key2]['Data'][$key3] = [
            'Data' => [],
            'TotalRequired' => 0,
            'TotalFinished' => 0
          ];
        }

        $key4 = $data[$i]->Category4;
        if (! array_key_exists($key4, $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data']))
        {
          $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data'][$key4] = [
            'Data' => [],
            'PickReason' => '',
            'TotalRequired' => 0,
            'TotalFinished' => 0
          ];
        }

        $grouped['TotalRequired'] += $data[$i]->TotalRequired;
        $grouped['TotalFinished'] += $data[$i]->TotalFinished;

        $grouped['Data'][$key]['TotalRequired'] += $data[$i]->TotalRequired;
        $grouped['Data'][$key]['TotalFinished'] += $data[$i]->TotalFinished;

        $grouped['Data'][$key]['Data'][$key2]['TotalRequired'] += $data[$i]->TotalRequired;
        $grouped['Data'][$key]['Data'][$key2]['TotalFinished'] += $data[$i]->TotalFinished;

        $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['TotalRequired'] += $data[$i]->TotalRequired;
        $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['TotalFinished'] += $data[$i]->TotalFinished;

        $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data'][$key4]['TotalRequired'] += $data[$i]->TotalRequired;
        $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data'][$key4]['TotalFinished'] += $data[$i]->TotalFinished;

        if ($data[$i]->PickReference)
        {
          $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data'][$key4]['PickReason'] = $data[$i]->PickReference.' - '.$data[$i]->PickReason;
        }

        $data[$i]->Completed = $data[$i]->TotalRequired === $data[$i]->TotalFinished;
        $grouped['Data'][$key]['Data'][$key2]['Data'][$key3]['Data'][$key4]['Data'][] = $data[$i];
      }
    }

    return Response::prettyjson($grouped);
  }

  public function details($department)
  {
    $category1 = Input::get('category1');
    $category2 = Input::get('category2');
    $category3 = Input::get('category3');
    $category4 = Input::get('category4');
    $ready_only = Input::get('ready_only', 0);

    if (!$category1)
    {
      $category1 = null;
    }
    if (!$category2)
    {
      $category2 = null;
    }
    if (!$category3)
    {
      $category3 = null;
    }
    if (!$category4)
    {
      $category4 = null;
    }

    $data = DB::connection('archdb-wm')->select(DB::raw('CALL production_app.sp_Get_ScheduleDetails(?, ?, ?, ?, ?, ?);'), [
      $department,
      $category1,
      $category2,
      $category3,
      $category4,
      $ready_only
    ]);

    return Response::prettyjson($data);
  }

  public function lineItems($schedule_ids)
  {
    $data = DB::connection('archdb-wm')
      ->table('production.schedules')
      ->whereRaw('FIND_IN_SET(SchedulesID, ?)', [$schedule_ids])
      ->orderBy('OrderNumber')
      ->orderBy('FrameNumber')
      ->get();

    return Response::prettyjson($data);
  }

  public function updateComplete()
  {
    $completed = Input::get('completed');
    $station = Input::get('station');
    $employee = JWTAuth::parseToken()->toUser();

    if (!$completed || !is_array($completed) || !$station)
    {
      return Response::prettyjson(['error' => 'Bad input'], 400);
    }

    try
    {
      foreach ($completed as $id)
      {
        DB::connection('archdb-wm')->select(DB::raw('CALL production_app.sp_Update_ProductionScheduleById(?, ?, ?);'), [$id, $station, $employee->username]);
      }
      return Response::prettyjson(true);
    }
    catch (Exception $ex)
    {
      return Response::prettyjson(false);
    }
  }

  public function glassTotals($order_numbers)
  {
    $sql = "
      SELECT SUType, COUNT(1) AS Required, SUM(IF(VerifiedOn IS NOT NULL, 1, 0)) AS Ready
      FROM production.sustatus
      WHERE OrderNumber IN ({$order_numbers})
      GROUP BY SUType
    ";
    $data = DB::connection('archdb-wm')->select(DB::raw($sql));
    return Response::prettyjson($data);
  }

  public function glassDetails($order_numbers)
  {
    $sql = "
      SELECT ListNumber, SUType, OrderNumber, FrameNumber, Width, Height, GlassIn, GlassOut, GlassInt, FV_LastRack, FV_LastStation, VerifiedOn
      FROM production.sustatus
      WHERE OrderNumber IN ({$order_numbers})
      ORDER BY IF(VerifiedOn IS NULL, -1, 0), OrderNumber, FrameNumber
    ";
    $data = DB::connection('archdb-wm')->select(DB::raw($sql));

    $grouped = [];
    foreach ($data as $row)
    {
      if (! array_key_exists($row->SUType, $grouped))
      {
        $grouped[$row->SUType] = [];
      }
      $grouped[$row->SUType][] = $row;
    }

    return Response::prettyjson([
      'data' => $grouped,
      'listNumber' => $data[0]->ListNumber
    ]);
  }

  private function _getComments($key)
  {
    $data = DB::connection('archdb-wm')
      ->table('production.schedules_comments')
      ->where('key', $key)
      ->orderBy('created_at', 'desc')
      ->get();
    return $data;
  }
}
