<?php namespace ProjectsWeb;

use BaseController, DB, Input, Response;

class LookupsController extends BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $data = [
            'bid_status'          => BidStatus::all(),
            'branch'              => Branch::all(),
            'product_series'      => ProductSeries::all(),
            'project_status'      => ProjectStatus::all(),
            'project_type'        => ProjectType::all()
        ];
        return Response::prettyjson($data);
    }

    public function projects()
    {
        return Response::prettyjson(
            Project
                ::whereNotNull('project_name')
                ->where('project_name', '!=', '')
                ->orderBy('project_name')
                ->get(['_id', 'project_id', 'project_name'])
        );
    }

    public function projectDateTypes()
    {
        return Response::prettyjson(
            ProjectDateType
                ::orderBy('category')
                ->orderBy('name')
                ->get()
        );
    }

    public function changeOrders()
    {
        return Response::prettyjson(
            ChangeOrder
                ::where('project_id', Input::get('project_id'))
                ->orderBy('change_order_id')
                ->get(['change_order_id'])
        );
    }

    public function customerContacts()
    {
        $data = Project::distinct('customers.contact_name')->get()->toArray();
        $contacts = [];

        foreach ($data as $row)
        {
            if (isset($row[0]) && strlen($row[0]) > 0)
            {
                $contacts[] = $row[0];
            }
        }

        natcasesort($contacts);
        return Response::prettyjson(array_values($contacts));
    }
}