<?php namespace ProjectsWeb;

use BaseController, DB, Response, Input;
use MongoId;

class ChangeOrdersController extends BaseController {

    public function __construct()
    {
        $what = 'change-orders';
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.read", ['on' => 'get']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.create", ['on' => 'post']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.update", ['on' => 'put']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.delete", ['on' => 'delete']);
    }

    public function index()
    {
        $filter_fields = [
            'change_order_id',
            'project_id',
            'requested_by',
            'status',
            'service_reference'
        ];

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
                $order_by = 'date_submitted';
                $order_dir = 'desc';
            }

            $where = function($query) use ($tableState, $filter_fields)
            {
                if (! (isset($tableState->search) && isset($tableState->search->predicateObject)))
                {
                    return;
                }

                foreach ($tableState->search->predicateObject as $key => $value)
                {
                    if ($key === '$' || $key === '*')
                    {
                        $query->where(function($query) use ($filter_fields, $value)
                        {
                            $first = true;
                            foreach ($filter_fields as $field)
                            {
                                if ($first)
                                {
                                    $query->where($field, 'like', "%{$value}%");
                                }
                                else
                                {
                                    $query->orWhere($field, 'like', "%{$value}%");
                                }
                                $first = false;
                            }
                        });
                    }
                    else if ($key === 'status')
                    {
                        $query->where('status', $value);
                        $query->whereNotNull('date_submitted');
                    }
                    else
                    {
                        $query->where($key, 'like', "%{$value}%");
                    }
                }
            };
        }
        else
        {
            $order_by = 'date_submitted';
            $order_dir = 'desc';
            $start = 0;
            $number = 100;

            $where = function()
            {
                // Nothin' to see here
            };
        }

        $change_orders = ChangeOrder::where($where);
        $total = $change_orders->count();
        $data = $total > 0
            ? $change_orders
                ->orderBy($order_by, $order_dir)
                ->skip($start)
                ->take($number)
                ->get()
            : [];

        foreach ($data as $row)
        {
            $row->project = Project::find($row->project_id);
        }

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
        return Response::prettyjson($this->getChangeOrder($id));
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
            $change_order = new ChangeOrder();
            $data = Input::all();
            unset($data['project_info']);

            if (isset($data['status']))
            {
                $change_order->status_id = $data['status']['id'];
                unset($data['status']);
            }

            foreach ($data as $k => $v)
            {
                if ($k === 'project_id')
                {
                    $change_order->$k = new MongoId($v);
                }
                else
                {
                    $change_order->$k = $v;
                }
            }

            $change_order->save();
            return Response::prettyjson($change_order);
        }
        catch (Exception $e)
        {
            return Response::prettyjson(['error' => true], 500);
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
            $change_order = ChangeOrder::with('projectInfo')->with('status')->find($id);
            if (! $change_order)
            {
                return Response::prettyjson(['error' => 'Change order does not exist'], 400);
            }

            $data = Input::only($change_order->getFillable());
            foreach ($data as $k => $v)
            {
                if ($k === 'project_id')
                {
                    $change_order->$k = new MongoId($v);
                }
                else
                {
                    $change_order->$k = $v;
                }
            }

            if (Input::has('status'))
            {
                $status = Input::get('status');
                $change_order->status_id = $status['id'];
            }

            // Save changes
            $change_order->save();

            // Return updated model
            return Response::prettyjson($this->getChangeOrder($id));
        }
        catch (Exception $e)
        {
            return Response::prettyjson(['error' => true], 500);
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
            $change_order = ChangeOrder::find($id);
            if (! $change_order)
            {
                return Response::prettyjson(['error' => 'Change order does not exist'], 400);
            }

            $change_order->delete();
            return Response::json(true);
        }
        catch (Exception $e)
        {
            return Response::prettyjson(['error' => true], 500);
        }
    }

    private function getChangeOrder($id)
    {
        $ret = ChangeOrder::find($id);
        $ret->project = Project::find($ret->project_id);
        return $ret;
    }
}
