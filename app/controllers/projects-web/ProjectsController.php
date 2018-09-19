<?php namespace ProjectsWeb;

use BaseController, DB, Input, Response, Exception;
use MongoDate, MongoDuplicateKeyException;

class ProjectsController extends BaseController {

    public function __construct()
    {
        $what = 'projects';
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.read", ['on' => 'get']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.create", ['on' => 'post']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.update", ['on' => 'put']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.delete", ['on' => 'delete']);
    }

    public function index()
    {
        $tableState = json_decode(Input::get('tableState', null));
        if ($tableState !== null)
        {
            $start = isset($tableState->pagination->start)
                ? $tableState->pagination->start
                : 0;

            $number = isset($tableState->pagination->number)
                ? $tableState->pagination->number
                : 10;

            if (isset($tableState->sort->predicate))
            {
                $order_by = $tableState->sort->predicate;
                $order_dir = $tableState->sort->reverse ? 'desc' : 'asc';
            }
            else
            {
                $order_by = 'updated_at';
                $order_dir = 'desc';
            }

            $where = function($query) use ($tableState)
            {
                if (! (isset($tableState->search) && isset($tableState->search->predicateObject)))
                {
                    return;
                }

                foreach ($tableState->search->predicateObject as $key => $value)
                {
                    switch($key)
                    {
                        case "global":
                        case '*':
                           $query
                                ->where('project_id', 'like', "%{$value}%")
                                ->orWhere('project_name', 'like', "%{$value}%")
                                ->orWhere('bid_status.description', 'like', "%{$value}%")
                                ->orWhere('project_status.description', 'like', "%{$value}%")
                                ->orWhere('customers.customer.name', 'like', "%{$value}%")
                                ->orWhere('customers.salesperson.description', 'like', "%{$value}%")
                                ->orWhere('customers.contact_name', 'like', "%{$value}%")
                                ->orWhere('contract_manager.description', 'like', "%{$value}%");

                            break;
                        case 'project':
                            $query
                                ->where('project_id', 'like', "%{$value}%")
                                ->orWhere('project_name', 'like', "%{$value}%");
                            break;
                        case 'customers':
                            $query->where('customers.customer.name', 'like', "%{$value}%");
                            break;
                        case 'salespeople':
                            $query->where('customers.salesperson.description', 'like', "%{$value}%");
                            break;
                        default:
                            $query->where($key, 'like', "%{$value}%");
                            break;
                    }
                }
            };
        }
        else
        {
            $order_by = 'updated_at';
            $order_dir = 'desc';
            $start = 0;
            $number = 10000; // Unlimited

            $where = function()
            {
                // Nothin' to see here
            };
        }

        $projects = Project::where($where);
        $total = $projects->count();
        $data = $projects
            ->orderBy($order_by, $order_dir)
            ->skip($start)
            ->take($number)
            ->get();

        return Response::prettyjson([
            'total' => $total,
            'data'  => $data
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $project = $this->getProject($id);
        return Response::prettyjson($project);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        try
        {
            $project = new Project();
            $data = Input::except([
                '_id',
                'created_at',
                'created_by',
                'deleted_at',
                'deleted_by',
                'updated_at',
                'updated_by'
            ]);
            $this->processData($project, $data);
            $project->save();
            return Response::prettyjson($project);
        }
        catch (MongoDuplicateKeyException $ex)
        {
            return Response::prettyjson(['error' => 'Project already exists'], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        try
        {
            $project = Project::find($id);
            $data = Input::except([
                '_id',
                'created_at',
                'created_by',
                'deleted_at',
                'deleted_by',
                'updated_at',
                'updated_by'
            ]);
            $this->processData($project, $data);
            $project->save();
            return Response::prettyjson($project);
        }
        catch (Exception $ex)
        {
            return Response::prettyjson([
                'error' => [
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine(),
                    'message' => $ex->getMessage()
                ]
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        try
        {
            $project = Project::find($id);
            if (! $project)
            {
                return Response::prettyjson(['error' => 'Project does not exist'], 400);
            }

            $project->delete();
            return Response::json(true);
        }
        catch (Exception $e)
        {
            return Response::prettyjson(['error' => true], 500);
        }
    }

    private function processData(&$project, $data)
    {
      foreach ($data as $key => $value)
      {
        if ($value && strpos($key, 'date') !== false)
        {
          $project->{$key} = new MongoDate(strtotime($value));
        }
        elseif ($key === 'documents' && is_array($value))
        {
          /*
           *  documents: [
           *    { type: "", date_received: "", description: "" }
           *  ]
          */
          for ($i = 0; $i < count($value); ++$i)
          {
            if ($value[$i]['date_received'])
            {
              $value[$i]['date_received'] = new MongoDate(strtotime($value[$i]['date_received']));
            }
          }
          $project->documents = $value;
        }
        elseif ($key === 'tasks')
        {
          /*
           *  tasks: {
           *    drafting-finals: { from: "", to: "" }
           *    engineer-prelims: {
           *      engineer-prelims-1: { from: "", to: "" }
           *    }
           *  }
          */
          foreach ($value as $taskGroupId => $taskGroup)
          {
            foreach($taskGroup as $taskId => $task)
            {
              if (is_array($task))
              {
                foreach ($task as $fKey => $fValue)
                {
                  $fValue = new MongoDate(strtotime($fValue));
                  $value[$taskGroupId][$taskId][$fKey] = $fValue;
                }
              }
              elseif ($task)
              {
                dd($task);
                foreach ($task as $fkey => $fValue)
                {
                  $value[$taskGroupId][$taskId][$fkey] = new MongoDate(strtotime($fValue));
                }
              }
            }
          }
          $project->tasks = $value;
        }
        else
        {
          $project->{$key} = $value;
        }
      }
    }

    private function getProject($id)
    {
        $project = Project::find($id);
        $change_orders = ChangeOrder::where('project_id', $id)->get()->toArray();

        if ($change_orders)
        {
            usort($change_orders, function($a, $b)
            {
                $comp_a = (int)explode('.', $a['change_order_id'])[1];
                $comp_b = (int)explode('.', $b['change_order_id'])[1];

                if ($comp_a === $comp_b)
                {
                    return 0;
                }

                return $comp_a < $comp_b ? -1 : 1;
            });

            $project->change_orders = $change_orders;
        }

        $project->rebids = $this->getRebids($project->project_id);

        return $project;
    }

    private function getRebids($project_id)
    {
        if (strlen($project_id) < 3 || substr_compare($project_id, '.D', -2, 2) === 0)
        {
            return [];
        }

        $project_id_parts = explode('.', $project_id);
        if (count($project_id_parts) < 2)
        {
            return [];
        }

        $result = Project
            ::where('project_id', 'like', "{$project_id_parts[0]}.%")
            ->get();

        $ret = [];
        if (count($result) > 0)
        {
            foreach($result as $row)
            {
                if (strlen($row->project_id) < 3 || substr_compare($row->project_id, '.D', -2, 2) === 0)
                {
                    continue;
                }

                $ret[] = [
                    '_id' => (string)$row->_id,
                    'project_id' => $row->project_id
                ];
            }

            usort($ret, function($a, $b)
            {
                return strcmp($a['project_id'], $b['project_id']);
            });
        }
        return $ret;
    }
}
