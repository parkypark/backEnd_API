<?php namespace ProcessingWeb;

use BaseController;
use DateTime;
use DB;
use Input;
use Response;
use MongoDate;

class ProcessingLogController extends BaseController
{

    private $filter_fields = [
        'project_id',
        'processreference',
        'ordernumber',
        'customername',
        'prioritymemo',
        'deliveryinstructions',
        'poreference',
        'documentreference',
        'buildingname',
        'deliverycity',
        'operator',
        'location',
        'project.ProjectName',
        'buildings',
        'floors',
        'window_names'
    ];

    public function index()
    {
        $criteria = [
            'project_id'  => Input::get('project_id', null),
            'building_id' => Input::get('building_id', null),
            'floor_id'    => Input::get('floor_id', null)
        ];

        $sort             = Input::get('sort', false);
        $filters          = Input::get('filters', false);
        $processed_before = Input::get('processedBefore', false);
        $start            = Input::get('start', 0);
        $count            = Input::get('count', 25);
        $no_group         = Input::get('no_group', false);
        $remake_only      = Input::get('remake_only', false);

        $start = (int)$start;
        $count = (int)$count;

        if ($sort !== false) {
            $sort = json_decode($sort);
        } else {
            $sort = new \stdClass();
            $sort->predicate = 'ordernumber';
            $sort->reverse = true;
        }

        if ($filters !== false) {
            $filters = json_decode($filters);
        }

        $where = function ($query) use ($criteria, $filters, $processed_before) {
            if ($criteria['project_id']) {
                $query->where('project.ProjectNumber', $criteria['project_id']);
            }

            if ($criteria['building_id']) {
                $query->where('buildings', $criteria['building_id']);
            }

            if ($criteria['floor_id']) {
                $floor_id = $criteria['floor_id'];
                $query->where(function ($query) use ($floor_id) {
                    $query
                        ->where('floors', $floor_id)
                        ->orWhere('floors', "%{$floor_id},%")
                        ->orWhere('floors', "%,{$floor_id}%");
                });
            }

            if ($filters !== false) {
                foreach ($filters as $k => $v) {
                    if (strlen($v) === 0) {
                        continue;
                    }

                    switch ($k) {
                        case 'global':
                            $query->where(function ($query) use ($v) {
                                $first = true;
                                foreach ($this->filter_fields as $field) {
                                    if ($first) {
                                        $query->where($field, 'like', "%{$v}%");
                                        $first = false;
                                    } else {
                                        $query->orWhere($field, 'like', "%{$v}%");
                                    }
                                }
                            });

                            break;

                        case 'processreference':
                        case 'ordernumber':
                            $query->where($k, (int) $v);
                            break;

                        case '[meta-project]':
                            $query->where('project.ProjectName', 'like', "%{$v}%")
                                  ->orWhere('project.ProjectNumber', 'like', "%{$v}%");
                            break;

                        case '[meta-info]':
                            $query->where('deliveryinstructions', 'like', "%{$v}%")
                                  ->orWhere('prioritymemo', 'like', "%{$v}%");
                            break;

                        default:
                            if ($k === 'processreference' || $k === 'ordernumber') {
                                $v = (int)$v;
                            }
                            $query->where($k, 'like', "%{$v}%");
                            break;
                    }
                }
            }

            if ($processed_before !== false) {
                $query->where('dateprocessed', '<=', $processed_before);
            }
        };

        /*if ($remake_only) {
        } else {
        }*/

        $query = OrdersProcessed::where($where);
        $total = $query->count();
        $data = $query
            ->orderBy($sort->predicate, $sort->reverse ? 'DESC': 'ASC')
            ->orderBy('ordernumber', 'DESC')
            ->skip($start)->take($count)
            ->get();

        if ($no_group) {
            return Response::prettyjson(['total' => $total, 'data' => $data]);
        }

        $grouped = [];
        foreach ($data as $row) {
            if (! isset($grouped[$row->dateprocessed])) {
                $grouped[$row->dateprocessed] = [];
            }
            $grouped[$row->dateprocessed][] = $row;
        }
        return Response::prettyjson(['total' => $total, 'data' => $grouped]);
    }

    public function getCalendar()
    {
      $start = Input::get('start', false);
      $end = Input::get('end', false);

      if ($start === false || $end === false)
      {
        return Response::prettyjson('Invalid input', 400);
      }

      $start = substr(explode('T', $start)[0], 1);
      $end = substr(explode('T', $end)[0], 1);

      // processed
      $processed = OrdersProcessed
        ::where('dateprocessed', '>=', $start)
        ->where('dateprocessed', '<=', $end)
        ->get();

      // { title: 'Feed Me ' + m, start: s + (50000), end: s + (100000), allDay: false }

      $ret = [];
      foreach ($processed as $row)
      {
        $title = $row->project['ProjectName'] . ' (#' . $row->project['ProjectID'] . ')';
        $date_processed = $row->dateprocessed;
        $key = $date_processed . '-' . $row->project['ProjectID'];

        if (!array_key_exists($key, $ret))
        {
          $ret[$key] = [
            'title' => $title,
            'start' => $date_processed,
            'end' => $date_processed,
            'allDay' => true,
            'data' => []
          ];
        }

        $ret[$key]['data'][] = $row;
      }

      $ret = array_values($ret);

      return Response::prettyjson($ret);
    }
}
