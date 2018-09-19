<?php namespace QualityWeb;

use BaseController, DB, Input, Response;

class PQPLookupController extends BaseController {

    /**
     * Get employee locations
     *
     * @return Response
     */
    public function getEmployeeLocations()
    {
        return Response::prettyjson(PQPEmployeeLocation::remember(10)->get());
    }

    /**
     * Get extrusion quality categories
     *
     * @return Response
     */
    public function getExtrusionQualityCategories()
    {
        return Response::prettyjson(PQPExtrusionQualityCategory::remember(10)->get());
    }

    /**
     * Get fabrication types
     *
     * @return Response
     */
    public function getFabricationTypes()
    {
        return Response::prettyjson(PQPFabricationType::remember(10)->get());
    }

    /**
     * Get frame series
     *
     * @return Response
     */
    public function getFrameSeries()
    {
        return Response::prettyjson(PQPFrameSeries::remember(10)->get());
    }

    /**
     * Get inventory categories
     *
     * @return Response
     */
    public function getInventoryCategories()
    {
        return Response::prettyjson(PQPInventoryCategory::remember(10)->get());
    }

    /**
     * Get inventory types
     *
     * @return Response
     */
    public function getInventoryTypes()
    {
        return Response::prettyjson(PQPInventoryType::remember(10)->get());
    }

    /**
     * Get material handling categories
     *
     * @return Response
     */
    public function getMaterialHandlingCategories()
    {
        return Response::prettyjson(PQPMaterialHandlingCategory::remember(10)->get());
    }

    /**
     * Get Non-conformance departments
     *
     * @return Response
     */
    public function getNonConformanceDepartments()
    {
        return Response::prettyjson(PQPNonConformanceDepartment::remember(10)->get());
    }

    /**
     * Get productivity departments
     *
     * @return Response
     */
    public function getProductivityDepartments()
    {
        return Response::prettyjson(PQPProductivityDepartment::remember(10)->get());
    }

    /**
     * Get sealed unit categories
     *
     * @return Response
     */
    public function getSealedUnitCategories()
    {
        return Response::prettyjson(PQPSealedUnitCategory::remember(10)->get());
    }

    public function getBoothTests()
    {
        $year = Input::get('year', date('Y'));
        $month = Input::get('month', date('m'));
        $result = DB::connection('archdb-admin')->select('call pqp_reports.lookup_booth_tests(?, ?)', [$year, $month]);
        return Response::prettyjson($result);
    }

    public function getFieldWaterFrameTests()
    {
        $year = Input::get('year', date('Y'));
        $month = Input::get('month', date('m'));
        $result = DB::connection('archdb-admin')->select('call pqp_reports.lookup_field_water_frame_tests(?, ?)', [$year, $month]);
        return Response::prettyjson($result);
    }

    public function getFieldWaterOpeningTests()
    {
        $year = Input::get('year', date('Y'));
        $month = Input::get('month', date('m'));
        $result = DB::connection('archdb-admin')->select('call pqp_reports.lookup_field_water_opening_tests(?, ?)', [$year, $month]);
        if ($result && count($result === 1))
        {
            $result = $result[0];
        }
        return Response::prettyjson($result);
    }

    public function getForwardLoad()
    {
        $date = Input::get('date', date('Y-m-01'));
        $result = DB::connection('archdb-admin')->select('call pqp_reports.lookup_forward_load(?)', [$date]);
        return Response::prettyjson($result);
    }

    public function getProductionQuality()
    {
        $year = Input::get('year', date('Y'));
        $month = Input::get('month', date('m'));
        $result = DB::connection('archdb-admin')->select('call pqp_reports.lookup_production_quality(?, ?)', [$year, $month]);

        $ret = ['inspections' => 0, 'failures' => 0];
        foreach ($result as $row)
        {
            $ret['inspections'] += $row->inspections;
            $ret['failures'] += $row->failures;
        }
        return Response::prettyjson($ret);
    }
}
