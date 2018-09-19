<?php namespace ProjectsWeb;

use BaseController, Input, Response, Validator;

class CustomersController extends BaseController {

    public function __construct()
    {
        $what = 'customers';
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.read", ['on' => 'get']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.create", ['on' => 'post']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.update", ['on' => 'put']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.delete", ['on' => 'delete']);
    }

    private $columns = ['name', 'title', 'address', 'phone', 'fax', 'cell', 'email', 'web', 'salesrep', 'contacts'];

    private function getCustomer($id) {
        $customer = Customer::find($id);
        if (! $customer)
        {
            return Response::prettyjson(['error' => 'Not found'], 404);
        }
        return Response::prettyjson($customer);
    }

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
                $order_by = 'name';
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
            $order_by = 'name';
            $order_dir = 'asc';
            $start = 0;
            $number = 65535; // ALL OF THEM!!
            $where = function() {};
        }

        $customers = Customer::where($where);
        $total = $customers->count();
        $data = $customers
            ->orderBy($order_by, $order_dir)
            ->skip($start)
            ->take($number)
            ->get();

        if (Input::has('download'))
        {
            $ret = [];
            $ret[] = '"First Name","Last Name","Company","Job Title","Business Street","Business Fax","Business Phone","E-mail Address","Web Page"';

            foreach($data as $row)
            {
                if ($row->contacts && count($row->contacts) > 0)
                {
                    $ret = array_merge($ret, $this->customerToCSV($row));
                }
            }

            $response = Response::make(implode("\r\n", $ret), 200);
            $response->header('Content-type', 'text/csv');
            $response->header('Content-Disposition', 'attachment; filename=customers.csv');
            $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', 0);
            return $response;
        }

        return Response::prettyjson([
            'total' => $total,
            'data'  => $data
        ]);
    }

    public function show($id)
    {
        return $this->getCustomer($id);
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
            $data = Input::only($this->columns);

            $customer = new Customer();
            foreach ($data as $k => $v)
            {
                $customer->$k = $v;
            }
            $customer->save();

            return Response::prettyjson($customer);
        }
        catch (Exception $e)
        {
            return Response::prettyjson(['error' => $e->getMessage()], 500);
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
            $customer = Customer::find($id);
            if (! $customer)
            {
                return Response::prettyjson(['error' => 'Customer does not exist'], 400);
            }

            $data = Input::only($this->columns);
            foreach ($data as $k => $v)
            {
                $customer->$k = $v;
            }

            $customer->save();
            return Response::prettyjson($customer);
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
            $customer = Customer::find($id);
            if (! $customer)
            {
                return Response::prettyjson(['error' => 'Customer does not exist'], 400);
            }

            $customer->delete();
            return Response::json(true);
        }
        catch (Exception $e)
        {
            return Response::prettyjson(['error' => $e->getMessage()], 500);
        }
    }

    private function customerToCSV(&$row) {
        $ret = [];

        foreach($row->contacts as $contact)
        {
            $line = [];
            $line[] = trim($contact['first_name'] ? : '');            // First Name
            $line[] = trim($contact['last_name'] ? : '');             // Last Name
            $line[] = trim($row->name ? : '');                        // Company
            $line[] = trim($contact['title'] ? : '');                 // Job Title
            $line[] = trim(str_replace(',,', ',', $row->address));    // Business Street
            $line[] = trim($row->fax ? : '');                         // Business Fax
            $line[] = trim($contact['phone'] ? : $row->phone ? : ''); // Business Phone
            $line[] = trim($contact['email'] ? : '');                 // E-mail Address
            $line[] = trim($row->web ? : '');                         // Web Page

            $ret[] = '"' . implode('","', $line) . '"';
        }

        return $ret;
    }
}
