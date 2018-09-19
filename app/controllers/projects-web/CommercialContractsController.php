<?php namespace ProjectsWeb;

use BaseController, DB, Response, Input;

class CommercialContractsController extends BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $filter_fields = [
            'commercial.projectid',
            'commercial.requestedby',
            'project_info.projectname',
            'project_info.contractmanagername'
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
                $order_by = 'commercial.datesubmitted';
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
                    if ($key === '$')
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
                    else
                    {
                        $query->where($key, 'like', "%{$value}%");
                    }
                }
            };
        }
        else
        {
            $order_by = 'commercial.datesubmitted';
            $order_dir = 'desc';
            $start = 0;
            $number = 100;

            $where = function()
            {
                // Nothin' to see here
            };
        }

        $commercial_contracts = Commercial
            ::join('project_info', 'commercial.projectid', '=', 'project_info.projectid')
            ->where('commercial.deleted', 0)
            ->where($where);

        return Response::prettyjson($commercial_contracts
            ->with(['project' => function($query)
            {
                $query->with(['commercialSubcontractors' => function($query)
                {
                    $query->with('subcontractor');
                    $query->whereDeleted(0);
                }]);
            }])
            ->with(['contractManager' => function($query)
            {
                $query->whereDeleted(0);
            }])
            ->orderBy($order_by, $order_dir)
            ->skip($start)
            ->take($number)
            ->get([
                'commercial.*',
                'project_info.*'
            ]));

    }

    public function getCount()
    {
        $filter_fields = [
            'commercial.projectid',
            'commercial.requestedby',
            'project_info.projectname',
            'project_info.contractmanagername'
        ];

        $tableState = json_decode(Input::get('tableState', null));
        if ($tableState !== null)
        {
            $where = function($query) use ($tableState, $filter_fields)
            {
                if (! (isset($tableState->search) && isset($tableState->search->predicateObject)))
                {
                    return;
                }

                foreach ($tableState->search->predicateObject as $key => $value)
                {
                    if ($key === '$')
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
                    else
                    {
                        $query->where($key, 'like', "%{$value}%");
                    }
                }
            };
        }
        else
        {
            $where = function()
            {
                // Nothin' to see here
            };
        }

        $total = Commercial
            ::where('commercial.deleted', 0)
            ->where($where)
            ->count();

        return Response::json(compact('total'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return Response::prettyjson($this->getCommercialContract($id));
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
            $commercial_contract = new Commercial();
            $data = Input::all();
            unset($data['project_info']);
            
            foreach ($data as $k => $v)
            {
                $commercial_contract->$k = $v;
            }

            $commercial_contract->save();
            return Response::prettyjson($commercial_contract);
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
            $commercial_contract = Commercial::with('projectInfo')->find($id);
            if (! $commercial_contract)
            {
                return Response::prettyjson(['error' => 'Change order does not exist'], 400);
            }

            $data = Input::only($commercial_contract->getFillable());
            foreach ($data as $k => $v)
            {
                $commercial_contract->$k = $v;
            }

            // Save changes
            $commercial_contract->save();

            // Return updated model
            return Response::prettyjson($this->getCommercialContract($id));
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
            $commercial_contract = Commercial::find($id);
            if (! $commercial_contract)
            {
                return Response::prettyjson(['error' => 'Commercial contract does not exist'], 400);
            }

            $commercial_contract->delete();
            return Response::json(true);
        }
        catch (Exception $e)
        {
            return Response::prettyjson(['error' => true], 500);
        }
    }

    private function getCommercialContract($id)
    {
        return Commercial
            ::with(['project' => function($query)
            {
                $query->with('projectInfo');
                $query->with(['commercialSubcontractors' => function($query)
                {
                    $query->with('subcontractor');
                    $query->whereDeleted(0);
                }]);
            }])
            ->with('contractManager')
            ->find($id);
    }
}