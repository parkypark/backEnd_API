<?php namespace ProjectsWeb;

use BaseController, Cache, DB, Input, Response;
use MongoDate;

class ReportsController extends BaseController {

    public function getProjects()
    {
        $contract_manager = Input::get('contract_manager');
        if ($contract_manager)
        {
            $contract_manager = json_decode($contract_manager);
        }

        $project_status = Input::get('project_status');
        if ($project_status)
        {
            $project_status = json_decode($project_status);
        }

        $salesperson = Input::get('salesperson');
        if ($salesperson)
        {
            $salesperson = json_decode($salesperson);
        }

        $date_filters = Input::get('dateFilters');
        if ($date_filters)
        {
            $date_filters = json_decode($date_filters);
        }

        $keyword = Input::get('keyword');
        $keyword_fields = [
            'project_id',
            'project_name',
            'province',
            'city',
            'location',
            'supply_alum',
            'supply_vinyl',
            'customers.salesperson.description'
        ];

        $ret = Project
            ::where(function($query) use ($contract_manager, $project_status, $salesperson, $date_filters, $keyword, $keyword_fields)
            {
                if ($contract_manager)
                {
                    $query->where('contract_manager.description', $contract_manager->description);
                }

                if ($project_status)
                {
                    $query->where('project_status.description', $project_status->description);
                }

                if ($salesperson)
                {
                    $query->where('customers.salesperson.description', $salesperson->description);
                }

                if ($date_filters)
                {
                    foreach ($date_filters as $k => $v)
                    {
                        if (isset($v->start) && $v->start)
                        {
                            $query->where($k, '>=', new MongoDate(strtotime($v->start)));
                        }

                        if (isset($v->end) && $v->end)
                        {
                            $query->where($k, '<=', new MongoDate(strtotime($v->end)));
                        }
                    }
                }

                if ($keyword)
                {
                    $first = true;
                    foreach($keyword_fields as $field)
                    {
                        if ($first)
                        {
                            $query->where($field, 'like', "%{$keyword}%");
                        }
                        else
                        {
                            $query->orWhere($field, 'like', "%{$keyword}%");
                        }
                        $first = false;
                    }
                }
            })
            ->whereNotNull('project_name')
            ->get();

        return Response::prettyjson($ret);
    }

    public function getChangeOrders()
    {
        $contract_manager = Input::get('contract_manager');
        if ($contract_manager)
        {
            $contract_manager = json_decode($contract_manager);
        }

        $change_order_status = Input::get('change_order_status');
        if ($change_order_status)
        {
            $change_order_status = json_decode($change_order_status);
        }

        $dateFilters = Input::get('dateFilters');
        if ($dateFilters)
        {
            $dateFilters = json_decode($dateFilters);
            if (isset($dateFilters->start) && $dateFilters->start)
            {
                $dateFilters->start = new MongoDate(strtotime($dateFilters->start));
            }
            if (isset($dateFilters->end) && $dateFilters->end)
            {
                $dateFilters->end = new MongoDate(strtotime($dateFilters->end));
            }

            if ($change_order_status)
            {
                if ($change_order_status->description === 'Approved')
                {
                    $dateFilters->field = 'date_approved';
                }
                else if ($change_order_status->description === 'Cancelled')
                {
                    $dateFilters->field = 'date_cancelled';
                }
                else if ($change_order_status->description === 'Rejected')
                {
                    $dateFilters->field = 'date_rejected';
                }
                else
                {
                    return Response::prettyjson(['error' => 'Bad request'], 400);
                }
            }
        }

        $include_zero = Input::get('include_zero', false);

        $result = Project
            ::where(function($query) use ($contract_manager)
            {
                if ($contract_manager)
                {
                    $query->where('contract_manager.description', $contract_manager->description);
                }
            })
            ->get(['project_id', 'project_name']);

        // Get projects by contract manager
        $projects = [];
        foreach ($result as $row)
        {
            $projects[$row->_id] = [
                'id' => $row->project_id,
                'name' => $row->project_name
            ];
        }

        // Get all change orders for projects previously loaded
        $result = ChangeOrder
            ::whereIn('project_id', array_keys($projects))
            ->where(function($q) use ($change_order_status, $dateFilters, $include_zero)
            {
                if ($include_zero !== true)
                {
                    $q->where('value', '<>', 0);
                }

                if ($change_order_status)
                {
                    $q->where('status', $change_order_status->description);
                }

                if ($dateFilters && isset($dateFilters->field) && strlen($date_filters->field) > 0)
                {
                    if ($dateFilters->start)
                    {
                        $q->where($dateFilters->field, '>=', $dateFilters->start);
                    }

                    if ($dateFilters->end)
                    {
                        $q->where($dateFilters->field, '<=', $dateFilters->end);
                    }
                }
            })
            ->get();

        // Append project name to change orders and return result
        foreach ($result as $row)
        {
            $row->project = [
                'project_id' => $projects[$row->project_id]['id'],
                'project_name' => $projects[$row->project_id]['name']
            ];
        }
        return Response::prettyjson($result);
    }

    public function getRedBook()
    {
        $date_filters = Input::get('dateFilters');
        if ($date_filters)
        {
            $date_filters = json_decode($date_filters);

            if (property_exists($date_filters, 'start') && $date_filters->start)
            {
                $date_filters->start = new MongoDate(strtotime($date_filters->start));
            }

            if (property_exists($date_filters, 'end') && $date_filters->end)
            {
                $date_filters->end = new MongoDate(strtotime($date_filters->end));
            }
        }

        $result = Project
            ::where('project_status.description', 'Secured')
            ->where(function($q) use ($date_filters)
            {
                if ($date_filters)
                {
                    if (property_exists($date_filters, 'start') && $date_filters->start)
                    {
                        $q->where('secured_date', '>=', $date_filters->start);
                    }

                    if (property_exists($date_filters, 'end') && $date_filters->end)
                    {
                        $q->where('secured_date', '<=', $date_filters->end);
                    }
                }
            })
            ->get();

        return Response::prettyjson($result);
    }

    public function getRedBookOld()
    {
        $date_filters = Input::get('dateFilters');
        $conditions = '';
        $q_params = [];

        if ($date_filters)
        {
            $date_filters = json_decode($date_filters);

            if (property_exists($date_filters, 'start'))
            {
                $conditions .= '    AND H.secureddate >= ?' . "\r\n";
                $q_params[] = $date_filters->start;
            }

            if (property_exists($date_filters, 'end'))
            {
                $conditions .= '    AND H.secureddate <= ?' . "\r\n";
                $q_params[] = $date_filters->end;
            }
        }

        $sql = join("\r\n", [
            '  SELECT H.projectid, H.secureddate,',
            '         H.contractcdn, H.contractus,',
            '         PI.salesrep, PI.projectname, PI.province',
            '    FROM projects_web.headers H, projects_web.status S,',
            '         projects_web.project_info PI',
            '   WHERE H.deleted_at IS NULL AND H.deleted = 0 AND H.vinylproject = 0 AND H.secureddate IS NOT NULL',
            $conditions,
            '     AND S.id = H.status AND S.description = "Secured"',
            '     AND PI.projectid = H.projectid',
            'ORDER BY YEAR(H.secureddate) DESC, MONTH(H.secureddate) DESC,',
            '         PI.salesrep, PI.projectname'
        ]);

        $ret = DB::connection('archdb')->select(DB::raw($sql), $q_params);
        return Response::prettyjson($ret);
    }
}
