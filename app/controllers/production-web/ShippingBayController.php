<?php namespace ProductionWeb;

use BaseController;
use DB;
use Input;
use Response;

class ShippingBayController extends BaseController
{
    public function index()
    {
        return Response::prettyjson(BayArea::get());
    }

    public function listBays($division)
    {
        $data = DB::connection('archdb-wm')
            ->table('production.bay_area_list')
            ->where('division', $division)
            ->get();

        $ret = [];
        foreach ($data as $row) {
            if (! array_key_exists($row->bay_type, $ret)) {
                $ret[$row->bay_type] = [];
            }
            $ret[$row->bay_type][] = $row;
        }

        return Response::prettyjson($ret);
    }

    public function getBay($rack_number)
    {
        $data = BayArea::where('rack_number', $rack_number)->get();
        return count($data) > 0 ? Response::json($data[0]) : null;
    }

    public function getBayNumber($rack_number)
    {
        $data = BayArea::where('rack_number', $rack_number)->get();
        if (isset($data) && count($data) > 0) {
            $ret = $data[0]->bay_number;
        } else {
            $ret = null;
        }
        return Response::prettyjson($ret);
    }

    public function getShippingBays($bay_area = null)
    {
        if (!$bay_area) {
            $rack_number = Input::get('rack_number');
            if ($rack_number) {
                $data = BayArea::where('rack_number', $rack_number)->get();
            } else {
                $data = BayArea::orderBy('bay_number')->get();
            }
        } else {
            $data = BayArea::where('bay_area', $bay_area)->orderBy('bay_number')->get();
        }

        return Response::prettyjson($data);
    }

    public function update()
    {
        $rack_number = Input::get('rackNumber', null);
        if (!$rack_number) {
            return Response::prettyjson(['error' => 'bad input'], 400);
        }

        $bay_number = Input::get('bayNumber', null);
        $order_number = Input::get('orderNumber', null);
        $employee_id = Input::get('employeeId', null);

        $rack = BayArea::find($rack_number);
        if (is_null($rack)) {
            $rack = new BayArea;
            $rack->rack_number = $rack_number;
        }

        $rack->bay_number = $bay_number;
        $rack->delivery_order_number = $order_number;
        $rack->delivery_driver_id = $employee_id;

        return Response::prettyjson($rack->save());
    }
}
