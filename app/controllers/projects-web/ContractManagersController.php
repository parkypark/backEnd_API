<?php namespace ProjectsWeb;

use BaseController, Input, Response, Validator;

class ContractManagersController extends BaseController {

    public function __construct()
    {
        $what = 'contract-managers';
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.read", ['on' => 'get']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.create", ['on' => 'post']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.update", ['on' => 'put']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.delete", ['on' => 'delete']);
    }

    private $rules = [
        'description' => 'required|min:2|unique:projects_web.technicalcoordinator,description,NULL,id,deleted_at,NULL'
    ];

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

            if (isset($tableState->sort->predicate)) {
                $order_by = $tableState->sort->predicate;
                $order_dir = $tableState->sort->reverse ? 'desc' : 'asc';
            } else {
                $order_by = 'description';
                $order_dir = 'asc';
            }

            $where = function($query) use ($tableState)
            {
                if (! (isset($tableState->search) && isset($tableState->search->predicateObject)))
                {
                    return;
                }

                foreach ($tableState->search->predicateObject as $key => $value)
                {
                    $query->where($key, 'like', "%{$value}%");
                }
            };
        }
        else
        {
            $order_by = 'description';
            $order_dir = 'asc';
            $start = 0;
            $number = 100;

            $where = function()
            {
                // Nothin' to see here
            };
        }

        $contract_managers = ContractManager::where($where);

        $total = $contract_managers->count();
        $data = $contract_managers
            ->orderBy($order_by, $order_dir)
            ->skip($start)
            ->take($number)
            ->get();

        return Response::prettyjson([
            'total' => $total,
            'data'  => $data
        ]);
    }

    public function show($id)
    {
        return Response::prettyjson(ContractManager::find($id));
    }

    public function store()
    {
        $data = Input::only(['description']);
        $validator = Validator::make($data, $this->rules);

        if ($validator->fails())
        {
            return Response::prettyjson(['errors' => $validator->messages()], 400);
        }

        $cm = new ContractManager();
        foreach ($data as $k => $v)
        {
            $cm->$k = $v;
        }
        $cm->save();

        return Response::prettyjson($cm);
    }

    public function update($id)
    {
        try
        {
            $cm = ContractManager::find($id);
            if (! $cm)
            {
                return Response::prettyjson(['error' => 'Contract manager does not exist'], 400);
            }

            $data = Input::only(['description']);
            foreach ($data as $k => $v)
            {
                $cm->$k = $v;
            }
            $cm->save();

            return Response::prettyjson($cm);
        }
        catch (Exception $e)
        {
            return Response::prettyjson(['error' => true], 500);
        }
    }

    public function destroy($id)
    {
        try
        {
            $cm = ContractManager::find($id);
            if (! $cm)
            {
                return Response::prettyjson(['error' => 'Contract manager does not exist'], 400);
            }

            $cm->delete();
            return Response::json(true);
        }
        catch (Exception $e)
        {
            return Response::prettyjson(['error' => $e->getMessage()], 500);
        }
    }
}
