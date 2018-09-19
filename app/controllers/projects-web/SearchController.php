<?php namespace ProjectsWeb;

use BaseController, DB, Input, Response;

class SearchController extends BaseController {

    public function __construct()
    {
        // Just going to check read access to projects to allow searching...
        $this->beforeFilter("jwt-auth.hasAccess:projects.projects.read", ['on' => 'get']);
    }

    public function search($query)
    {
        return Response::prettyjson([
            'customers' => $this->searchCustomers($query),
            'projects' => $this->searchProjects($query),
            'salesPeople' => $this->searchSalesPeople($query),
            'subcontractors' => $this->searchSubcontractors($query)
        ]);
    }

    private function searchCustomers($search_term)
    {
        $result = Customer
            ::where('name', 'like', "%{$search_term}%")
            ->orderBy('name')
            ->take(25)
            ->get();

        $ret = [];
        foreach ($result as $row)
        {
            $ret[] = [
                '_id'           => (string)$row->_id,
                'title'         => $row->name,
                'description'   => "Sales Rep: {$row->salesrep['description']}"
            ];
        }
        return $ret;
    }

    private function searchProjects($search_term)
    {
        $result = Project
            ::where(function($query) use ($search_term)
            {
                $query->where('project_id', 'like', "%$search_term%");
                $query->orWhere('project_name', 'like', "%$search_term%");
                $query->orWhere('address', 'like', "%$search_term%");
                $query->orWhere('city', 'like', "%$search_term%");
                $query->orWhere('province', 'like', "%$search_term%");
            })
            ->orderBy('project_name')
            ->take(25)
            ->get();

        $ret = [];
        foreach($result as $row)
        {
            $description = [];
            if ($row->address)
            {
                $description[] = $row->address;
            }
            if ($row->city)
            {
                $description[] = $row->city;
            }
            if ($row->province)
            {
                $description[] = $row->province;
            }

            $ret[] = [
                '_id'           => (string)$row->_id,
                'title'         => "{$row->project_name} (#{$row->project_id})",
                'description'   => join(', ', $description)
            ];
        }
        return $ret;
    }

    private function searchSalesPeople($search_term)
    {
        $result = SalesPerson
            ::where('description', 'like', "%$search_term%")
            ->orderBy('description')
            ->take(25)
            ->get();

        $ret = [];
        foreach($result as $row)
        {
            $ret[] = [
                '_id'           => (string)$row->_id,
                'title'         => $row->description,
            ];
        }
        return $ret;
    }

    private function searchSubcontractors($search_term)
    {
        $result = Subcontractor
            ::where('name', 'like', "%$search_term%")
            ->orderBy('name')
            ->take(25)
            ->get();

        $ret = [];
        foreach($result as $row)
        {
            $ret[] = [
                '_id'           => (string)$row->_id,
                'title'         => $row->name,
            ];
        }
        return $ret;
    }
}
