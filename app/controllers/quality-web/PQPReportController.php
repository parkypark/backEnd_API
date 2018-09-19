<?php namespace QualityWeb;

use BaseController, DB, Exception, Input, Response;

class PQPReportController extends BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return Response::prettyjson(PQPReport::all());
    }


    /**
     * Display the specified resource.
     *
     * @param  string  $report_date
     * @return Response
     */
    public function show($report_date)
    {

        $report = PQPReport::where('Date', $report_date)

            ->with('aluminumRecycling')

            ->with(array('boothTests' => function($query)
            {
                $query->with('frameSeries');
            }))

            ->with(array('employees' => function($query)
            {
                $query->with('location');
            }))

            ->with(array('extrusionQuality' => function($query)
            {
                $query->with('category');
            }))

            ->with(array('fabrication' => function($query)
            {
                $query->with('type');
            }))

            ->with(array('fieldWaterFrameTests' => function($query)
            {
                $query->with('frameSeries');
            }))

            ->with('fieldWaterOpeningTests')

            ->with('forwardLoad')

            ->with(array('inventory' => function($query)
            {
                $query->with('category')->with('type');
            }))

            ->with(array('materialHandling' => function($query)
            {
                $query->with('category');
            }))

            ->with('productionQuality')

            ->with(array('productivity' => function($query)
            {
                $query->with('department');
            }))

            ->with(array('sealedUnits' => function($query)
            {
                $query->with('category');
            }));

        return Response::prettyjson($report->first());

    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        DB::connection('archdb-admin')->beginTransaction();

        try
        {
            $data = $this->getInputData();
            $report = new PQPReport(['Date' => $data['Date']]);

            if ($report->save())
            {
                if ($data['aluminum_recycling'])
                {
                    $report->aluminumRecycling()->create($data['aluminum_recycling']);
                }

                if ($data['booth_tests'])
                {
                  foreach ($data['booth_tests'] as $d)
                  {
                      $report->boothTests()->create($d);
                  }
                }

                if ($data['employees'])
                {
                  foreach ($data['employees'] as $d)
                  {
                      $report->employees()->create($d);
                  }
                }

                if ($data['extrusion_quality'])
                {
                  foreach ($data['extrusion_quality'] as $d)
                  {
                      $report->extrusionQuality()->create($d);
                  }
                }

                if ($data['fabrication'])
                {
                  foreach ($data['fabrication'] as $d)
                  {
                      $report->fabrication()->create($d);
                  }
                }

                if ($data['field_water_frame_tests'])
                {
                  foreach ($data['field_water_frame_tests'] as $d)
                  {
                      $report->fieldWaterFrameTests()->create($d);
                  }
                }

                if ($data['field_water_opening_tests'])
                {
                    $report->fieldWaterOpeningTests()->create($data['field_water_opening_tests']);
                }

                if ($data['forward_load'])
                {
                  foreach ($data['forward_load'] as $d)
                  {
                      $d['Employees'] = $this->getEmployeesRequired($d);
                      $report->forwardLoad()->create($d);
                  }
                }

                if ($data['inventory'])
                {
                  foreach ($data['inventory'] as $d)
                  {
                      $report->inventory()->create($d);
                  }
                }

                if ($data['material_handling'])
                {
                  foreach ($data['material_handling'] as $d)
                  {
                      $report->materialHandling()->create($d);
                  }
                }

                if ($data['production_quality'])
                {
                    $report->productionQuality()->create($data['production_quality']);
                }

                if ($data['productivity'])
                {
                  foreach ($data['productivity'] as $d)
                  {
                      $report->productivity()->create($d);
                  }
                }

                if ($data['sealed_units'])
                {
                  foreach ($data['sealed_units'] as $d)
                  {
                      $report->sealedUnits()->create($d);
                  }
                }
            }

            DB::connection('archdb-admin')->commit();
            return Response::prettyjson($this->findById($report->Id));
        }
        catch (Exception $e)
        {
            DB::connection('archdb-admin')->rollback();
            throw $e;
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
        DB::connection('archdb-admin')->beginTransaction();

        try
        {
            $data = $this->getInputData();
            $report = $this->findById($id);

            $updateOrCreate = function($data, $model) use ($report)
            {
                if (isset($data['id']))
                {
                    $o = $model::find($data['id']);
                    $o->update($data);
                }
                else
                {
                    $o = new $model($data);
                    $o->ReportId = $report->Id;
                    $o->save();
                }
            };

            if ($report)
            {
                // Aluminum Recycling
                if ($data['aluminum_recycling'])
                {
                    $updateOrCreate($data['aluminum_recycling'], 'QualityWeb\PQPAluminumRecycling');
                }
                else
                {
                    $report->aluminumRecycling()->delete();
                }

                // Booth Tests
                if ($data['booth_tests'] && count($data['booth_tests']))
                {
                    foreach ($data['booth_tests'] as $value)
                    {
                        $updateOrCreate($value, 'QualityWeb\PQPBoothTest');
                    }
                }
                else
                {
                    $report->boothTests()->delete();
                }

                // Employees
                if ($data['employees'] && count($data['employees']))
                {
                    foreach ($data['employees'] as $value)
                    {
                        $updateOrCreate($value, 'QualityWeb\PQPEmployee');
                    }
                }
                else
                {
                    $report->employees()->delete();
                }

                // Extrusion Quality
                if ($data['extrusion_quality'] && count($data['extrusion_quality']))
                {
                    foreach ($data['extrusion_quality'] as $value)
                    {
                        $updateOrCreate($value, 'QualityWeb\PQPExtrusionQuality');
                    }
                }
                else
                {
                    $report->extrusionQuality()->delete();
                }

                // Fabrication
                if ($data['fabrication'] && count($data['fabrication']))
                {
                    foreach ($data['fabrication'] as $value)
                    {
                        $updateOrCreate($value, 'QualityWeb\PQPFabrication');
                    }
                }
                else
                {
                    $report->fabrication()->delete();
                }

                // Field Water Tests
                if ($data['field_water_frame_tests'] && count($data['field_water_frame_tests']))
                {
                    foreach ($data['field_water_frame_tests'] as $value)
                    {
                        $updateOrCreate($value, 'QualityWeb\PQPFieldWaterFrameTest');
                    }
                }
                else
                {
                    $report->fieldWaterFrameTests()->delete();
                }

                if ($data['field_water_opening_tests'])
                {
                    $updateOrCreate($data['field_water_opening_tests'], 'QualityWeb\PQPFieldWaterOpeningTest');
                }
                else
                {
                    $report->fieldWaterOpeningTests()->delete();
                }

                // Forward Load
                $report->forwardLoad()->delete();
                if ($data['forward_load'] && count($data['forward_load']))
                {
                    foreach ($data['forward_load'] as $value)
                    {
                        $value['Employees'] = $this->getEmployeesRequired($value);

                        $o = new PQPForwardLoad($value);
                        $o->ReportId = $report->Id;
                        $o->save();
                    }
                }

                // Inventory
                if ($data['inventory'] && count($data['inventory']))
                {
                    foreach ($data['inventory'] as $value)
                    {
                        $updateOrCreate($value, 'QualityWeb\PQPInventory');
                    }
                }
                else
                {
                    $report->inventory()->delete();
                }

                // Material Handling
                if ($data['material_handling'] && count($data['material_handling']))
                {
                    foreach ($data['material_handling'] as $value)
                    {
                        $updateOrCreate($value, 'QualityWeb\PQPMaterialHandling');
                    }
                }
                else
                {
                    $report->materialHandling()->delete();
                }

                // Production Quality
                if ($data['production_quality'])
                {
                    $updateOrCreate($data['production_quality'], 'QualityWeb\PQPProductionQuality');
                }
                else
                {
                    $report->productionQuality()->delete();
                }

                // Productivity
                if ($data['productivity'] && count($data['productivity']))
                {
                    foreach ($data['productivity'] as $value)
                    {
                        $updateOrCreate($value, 'QualityWeb\PQPProductivity');
                    }
                }
                else
                {
                    $report->productivity()->delete();
                }

                // Sealed Units
                if ($data['sealed_units'] && count($data['sealed_units']))
                {
                    foreach ($data['sealed_units'] as $value)
                    {
                        $updateOrCreate($value, 'QualityWeb\PQPSealedUnit');
                    }
                }
                else
                {
                    $report->sealedUnits()->delete();
                }
            }

            DB::connection('archdb-admin')->commit();
            return Response::prettyjson($this->findById($id));
        }
        catch (Exception $e)
        {
            DB::connection('archdb-admin')->rollback();
            throw $e;
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
        return Response::prettyjson(true);
    }


    private function findById($id)
    {
        return PQPReport
            ::with('aluminumRecycling')
            ->with(array('boothTests' => function($query)
            {
                $query->with('frameSeries');
            }))
            ->with(array('employees' => function($query)
            {
                $query->with('location');
            }))
            ->with(array('extrusionQuality' => function($query)
            {
                $query->with('category');
            }))
            ->with(array('fabrication' => function($query)
            {
                $query->with('type');
            }))
            ->with(array('fieldWaterFrameTests' => function($query)
            {
                $query->with('frameSeries');
            }))
            ->with('fieldWaterOpeningTests')
            ->with('forwardLoad')
            ->with(array('inventory' => function($query)
            {
                $query->with('category')->with('type');
            }))
            ->with(array('materialHandling' => function($query)
            {
                $query->with('category');
            }))
            ->with('productionQuality')
            ->with(array('productivity' => function($query)
            {
                $query->with('department');
            }))
            ->with(array('sealedUnits' => function($query)
            {
                $query->with('category');
            }))
            ->where('Id', $id)
            ->first();
    }

    private function getInputData()
    {
        return Input::only([
            'Date',
            'aluminum_recycling',
            'booth_tests',
            'employees',
            'extrusion_quality',
            'fabrication',
            'field_water_frame_tests',
            'field_water_opening_tests',
            'forward_load',
            'inventory',
            'material_handling',
            'production_quality',
            'productivity',
            'sealed_units'
        ]);
    }

    private function getEmployeesRequired($data)
    {
        $patio_doors  = isset($data['PatioDoors'])  ? (int)$data['PatioDoors']  : 0;
        $swing_doors  = isset($data['SwingDoors'])  ? (int)$data['SwingDoors']  : 0;
        $windows      = isset($data['Windows'])     ? (int)$data['Windows']     : 0;
        $curtain_wall = isset($data['CurtainWall']) ? (int)$data['CurtainWall'] : 0;
        $employees    = ceil(($patio_doors + $swing_doors + $windows + $curtain_wall) * 250 / 9450);

        return $employees;
    }

}
