<?php namespace ProjectsWeb;

use BaseController, Input, Response, Validator;

class SalesPeopleController extends BaseController {

    public function __construct()
    {
        $what = 'salespeople';
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.read", ['on' => 'get']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.create", ['on' => 'post']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.update", ['on' => 'put']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.delete", ['on' => 'delete']);
    }

    private $rules = [
        'description' => 'required|min:2|unique:projects_web.salesperson,description,NULL,id,deleted_at,NULL'
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

        $sales_people = SalesPerson::where($where);
        $total = $sales_people->count();
        $data = $sales_people
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
        return Response::prettyjson(SalesPerson::find($id));
    }

    public function store()
    {
        $data = Input::only(['description']);
        $validator = Validator::make($data, $this->rules);

        if ($validator->fails())
        {
            return Response::prettyjson(['errors' => $validator->messages()], 400);
        }

        $sales_person = new SalesPerson();
        foreach ($data as $k => $v)
        {
            $sales_person->$k = $v;
        }
        $sales_person->save();

        return Response::prettyjson($sales_person);
    }

    public function update($id)
    {
        try
        {
            $sales_person = SalesPerson::find($id);
            if (! $sales_person)
            {
                return Response::prettyjson(['error' => 'Sales person does not exist'], 400);
            }

            $data = Input::only(['description']);
            foreach ($data as $k => $v)
            {
                $sales_person->$k = $v;
            }
            $sales_person->save();

            return Response::prettyjson($sales_person);
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
            $sales_person = SalesPerson::find($id);
            if (! $sales_person)
            {
                return Response::prettyjson(['error' => 'Sales person does not exist'], 400);
            }
            $sales_person->delete();

            return Response::json(true);
        }
        catch (Exception $e)
        {
            return Response::prettyjson(['error' => true], 500);
        }
    }
}
