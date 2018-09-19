<?php namespace ProductionWeb;

use BaseController, DB, Input, Response;

class ScheduleTesting2Controller extends BaseController {
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

  public function details($department)
  {
    $category1 = Input::get('category1');
    $category2 = Input::get('category2');
    $category3 = Input::get('category3');
    $category4 = Input::get('category4');

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

    $data = DB::connection('archdb-wm')->select(DB::raw('CALL production.scheduleDetails(?, ?, ?, ?, ?);'), [
      $department,
      $category1,
      $category2,
      $category3,
      $category4
    ]);

    return Response::prettyjson($data);
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
    return Response::prettyjson($data);
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

    $sql = 'CALL production.scheduleHeaders(?, ?, ?, ?);';
    $data = DB::connection('archdb-wm')->select(DB::raw($sql), [$department, $subdepartment, $colour, $search]);
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
}
