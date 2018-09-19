<?php

class PortalController extends \BaseController
{

    /**
     * Get report types
     *
     * @return mixed
     */
    public function getReportTypes()
    {
        return Response::prettyjson(PortalReportTypes::orderBy('id')->get());
    }

    /**
     * Get reports
     *
     * @param null $type_id
     * @return Response
     */
    public function getReports($type_id)
    {
        $data = Portal
            ::where('report_type_id', '=', $type_id)
            ->orderBy('report_year', 'asc')
            ->orderBy('report_month', 'asc')
            ->orderBy('name')
            ->get();

        return Response::prettyjson($data);
    }

}