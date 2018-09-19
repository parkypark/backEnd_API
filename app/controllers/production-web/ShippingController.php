<?php namespace ProductionWeb;

use App;
use BaseController;
use DB;
use Input;
use Mail;
use Response;
use View;

class ShippingController extends BaseController
{
    const EZCM_DOMAIN = '15F02443-68D6-4C61-A9BE-54B8AEF97705';
    const RASTERIZE_PROGRAM = '/usr/bin/xvfb-run -a /usr/bin/phantomjs /usr/share/doc/phantomjs/examples/rasterize.js ';

    public function clearRackContents($rack_number)
    {
        $sql = '
            UPDATE production.orderstatus
            SET racknumber = "9900"
            WHERE racknumber = ?
        ';

        DB::connection('production')->update($sql, [$rack_number]);
        DB::connection('production-vinyl')->update($sql, [$rack_number]);

        return Response::json(true);
    }

    public function getWorkorders($division_id, $branch)
    {
        $division_id = intval($division_id);
        $ret = [];

        $sql = '
            SELECT DISTINCT O.ordernumber
            FROM production.orderstatus O
            JOIN production.picklist P ON P.id = O.picklistid
            WHERE P.branch = ? AND (O.status = 0 OR (O.status > 10 AND O.status != 1400))
            ORDER BY O.ordernumber
        ';

        $conn = $division_id === 1 ? DB::connection('production-vinyl') : DB::connection('production');
        $workorders = $conn->select($sql, [$branch]);

        if ($division_id === 1) {
            $sql = '
                SELECT O.id, O.ordernumber, O.linenumber, O.boxnumber, L.productcode, O.picklistid, O.racknumber, O.status, O.lastupdate,
                	   O.loadingcomplete, O.shippingcomplete, O.itemdescription, SU.FV_UnitID, SCR.screentype, S.description AS statusdescription,
                       S.isnotexception
                FROM production.orderstatus AS O
                JOIN production.status AS S ON S.statusid = O.status
                JOIN production.linestatus AS L ON L.ordernumber = O.ordernumber AND L.linenumber = O.linenumber
                LEFT JOIN production.sustatus AS SU ON
                	SU.OrderNumber = O.ordernumber AND
                	SU.LineNumber = O.linenumber AND
                	SU.FrameNumber = "SU" AND
                	O.boxnumber = 0
                LEFT JOIN screens.screens AS SCR ON SCR.ordernumber = O.ordernumber AND SCR.LineNumber = O.linenumber AND O.boxnumber = 0
                WHERE O.ordernumber = ?
                ORDER BY boxnumber, linenumber
            ';
        } else {
            $sql = '
                SELECT O.id, O.listnumber, O.ordernumber, O.boxnumber, O.linenumber, O.racknumber, O.picklistid, O.itemdescription, O.status,
                       O.loadingcomplete, O.shippingcomplete, SU.FV_UnitID, S.description AS statusdescription, S.isnotexception
                FROM production.orderstatus O
                JOIN production.status S ON S.statusid = O.status
                LEFT JOIN production.sustatus AS SU ON
                	SU.OrderNumber = O.ordernumber AND
                	SU.LineNumber = O.linenumber AND
                	O.boxnumber = 0
                WHERE O.ordernumber = ?
                ORDER BY boxnumber, linenumber
            ';
        }


        foreach ($workorders as $workorder) {
            $ret[$workorder->ordernumber] = [];

            if (intval($division_id) === 1) {
                $header = $this->fetchProductionHeader($division_id, $workorder->ordernumber);
                $customer_email = $this->fetchCustomerEmail($header['customercode']);
            } else {
                $customer_email = null;
            }

            $data = $conn->select($sql, [$workorder->ordernumber]);
            foreach ($data as $row) {
                $row->branch = $branch;
                $row->customer_email = $customer_email;
                $ret[$row->ordernumber][] = $row;
            }
        }

        return Response::json($ret);
    }

    public function getCustomerEmail($division_id, $ordernumber)
    {
        $header = $this->fetchProductionHeader($division_id, $ordernumber);
        $customer_email = $this->fetchCustomerEmail($header->customercode);
        return Response::json($customer_email);
    }

    public function getServerTime()
    {
        return time() * 1000; // unix timestamp in ms * 1000 to get s
    }

    public function fixPOD()
    {
        $uri = 'https://ezcm.starlinewindows.com/ezcm/ezrest/'.self::EZCM_DOMAIN.'/docs/';
        $client = new \GuzzleHttp\Client();
        $files = glob(storage_path() . '/POD/POD*.pdf');
        $ret = [];

        foreach ($files as $file) {
            $order_number = str_replace('POD_', '', basename($file, '.pdf'));
            $header = $this->fetchProductionHeader(1, $order_number);

            $meta = [
                'order_number' => $order_number,
                'customer_number' => $header->customercode,
                'customer_name' => $header->customername,
                'customer_address' => $header->deliveryaddress,
                'delivery_date' => date('Y-m-d')
            ];

            $customer_number = array_key_exists('customer_number', $meta) ? $meta['customer_number'] : null;
            $customer_name = str_replace('&', '&amp;', $meta['customer_name']);
            $filename = "POD_{$meta['order_number']}.pdf";

            $xml = "
                <?xml version=\"1.0\" encoding=\"utf-8\"?>
                <doc xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" fileName=\"{$filename}\" convertPdf=\"none\">
                    <doctypes>
                        <doctype name=\"PD Shipping Documents\">
                            <field name=\"Order Number\" value=\"{$meta['order_number']}\" />
                            <field name=\"Document Date\" value=\"{$meta['delivery_date']}\" />
                            <field name=\"Customer Number\" value=\"{$customer_number}\" />
                            <field name=\"Customer Name\" value=\"{$customer_name}\" />
                        </doctype>
                    </doctypes>
                </doc>
            ";
            $xml = trim($xml);

            $post_data = array(
                'verify' => false, // disable ssl check
                'auth' => ['awalmsley', 'Starline1'],
                'multipart' => [
                    [
                        'name' => 'properties',
                        'contents' => $xml
                    ],
                    [
                        'name' => 'file',
                        'filename' => $filename,
                        'contents' => fopen($file, 'r')
                    ]
                ]
            );

            try {
                $response = $client->request('POST', $uri, $post_data);
                if ($response->getStatusCode() === 200) {
                    $dest = storage_path() . '/POD/processed/';
                    exec("mv $file $dest");
                    $ret[$file] = true;
                } else {
                    $ret[$file] = false;
                }
            } catch (\Exception $ex) {
                $ret[$file] = false;
            }
        }

        return Response::prettyjson($ret);
    }

    public function postProofOfDelivery($division_id, $ordernumber)
    {
        $comments = Input::get('comments');
        $customer_email = Input::get('customerEmail');
        $division_id = intval($division_id);
        $employee_id = Input::get('employeeId');
        $items = Input::get('items');
        $location = Input::get('location');
        $photos = Input::get('photos');
        $receiver_name = Input::get('receiverName');
        $signature = Input::get('signature');

        App::finish(function (
            $request,
            $response
        ) use (
            $comments,
            $customer_email,
            $division_id,
            $employee_id,
            $items,
            $location,
            $ordernumber,
            $photos,
            $receiver_name,
            $signature
        ) {
            $header = $this->fetchProductionHeader($division_id, $ordernumber);
            $picklist = $this->fetchProductionPicklist($division_id, $items[0]['picklistid']);

            for ($i = 0; $i < count($items); ++$i) {
                $lastupdateExists = array_key_exists('lastupdate', $items[$i]);
                $scantimeExists = array_key_exists('scantime', $items[$i]);

                if ($lastupdateExists && !$scantimeExists) {
                    $items[$i]['scantime'] = $items[$i]['lastupdate'];
                } elseif (!$lastupdateExists && !$scantimeExists) {
                    $items[$i]['scantime'] = date('Y-m-d H:i:s');
                }
            }

            $metadata = [
                'branch' => $picklist->branch ?: 'LAN',
                'comments' => $comments,
                'customer_number' => $header->customercode,
                'customer_name' => $header->customername,
                'customer_address' => $header->deliveryaddress,
                'customer_city' => property_exists($header, 'deliverycity') ? $header->deliverycity : '',
                'delivery_date' => date('Y-m-d', strtotime($items[0]['scantime'])),
                'employee_id' => $employee_id,
                'items' => $items,
                'location' => $location,
                'order_number' => $ordernumber,
                'photos' => $photos,
                'receiver_name' => $receiver_name,
                'signature' => $signature,
                'total_items' => $header->totalitems
            ];

            $html = View::make('shipping.delivery_slip', $metadata)->render();
            if ($html) {
                // write html input to temp file
                $now = date('YmdHis');
                $html_path = storage_path() . "/POD/POD{$ordernumber}_{$now}.html";
                $pdf_path = storage_path() . "/POD/POD{$ordernumber}_{$now}.pdf";
    			file_put_contents($html_path, $html);

                // convert to pdf
                $cmd = self::RASTERIZE_PROGRAM . "$html_path $pdf_path" . ' 11in*8.5in';
                $response = exec($cmd);

                // upload to document control
                if ($this->uploadProofOfDeliveryToEzcm($pdf_path, $metadata)) {
                    $dest = storage_path() . '/POD/processed/';
                    exec("mv $pdf_path $dest");
                    exec("mv $html_path {$dest}html/");
                }

                // mail to customer if they provided email address
                $customer_email = filter_var($customer_email, FILTER_SANITIZE_EMAIL);
                if (filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
                    switch ($metadata['branch']) {
                        case 'ISL':
                            $email_from = 'victoriashipping@starlinewindows.com';
                            break;
                        case 'ISLP':
                            $email_from = 'nanaimoshipping@starlinewindows.com';
                            break;
                        case 'KEL':
                            $email_from = 'kelownashipping@starlinewindows.com';
                            break;
                        default:
                            $email_from = 'mainlandshipping@starlinewindows.com';
                            break;
                    }

                    // save images for embedding
                    foreach ($metadata['photos'] as $idx => $photo) {
                        $data = explode(',', $photo);
                        if (strpos($data[0], 'jpeg') !== false) {
                            $ext = 'jpg';
                        } elseif (strpos($data[0], 'png') !== false) {
                            $ext = 'png';
                        }
                        $path = storage_path() . "/POD/POD{$metadata['order_number']}_{$now}_photo_{$idx}.{$ext}";
                        file_put_contents($path, base64_decode($data[1]));
                        $metadata['photos'][$idx] = $path;
                    }

                    if ($metadata['signature']) {
                        $signature = base64_decode(explode(',', $metadata['signature'])[1]);
                        $path = storage_path() . "/POD/POD{$metadata['order_number']}_{$now}_signature.jpg";
                        file_put_contents($path, $signature);
                        $metadata['signature'] = $path;
                    }

                    Mail::send('shipping.delivery_slip_email', $metadata, function ($message) use ($email_from, $customer_email) {
                        $message->from($email_from);
                        $message->to($customer_email)->subject('Delivery Slip');
                    });

                    // cache last customer email for customer number
                    if ($header->customercode !== 'CASH0') {
                        $this->setCustomerEmail($header->customercode, $customer_email);
                    }
                }
            }
        });

        return Response::prettyjson(true);
    }

    private function fetchCustomerEmail($customer_code)
    {
        if ($customer_code === 'CASH0') {
            return '';
        }

        $sql = 'SELECT customer_email FROM production.pod_email WHERE customer_code = ?';
        $data = DB::connection('production-vinyl')->select($sql, [$customer_code]);
        return count($data) > 0 ? $data[0]->customer_email : '';
    }

    private function fetchProductionHeader($division_id, $ordernumber)
    {
        $fields = ['customername', 'totalitems', 'deliveryaddress'];

        if (intval($division_id) === 1) {
            $fields[] = 'customercode';
            $fields[] = 'deliverycity';
            $data = VinylProductionHeader::where('ordernumber', $ordernumber)->get($fields);
        } else {
            $data = ProductionHeader::where('ordernumber', $ordernumber)->get($fields);
        }

        return (count($data) > 0) ? $data[0] : null;
    }

    private function fetchProductionPicklist($division_id, $picklistid)
    {
        $data = (intval($division_id) === 1)
            ? VinylProductionPicklist::where('id', $picklistid)->get(['branch'])
            : ProductionPicklist::where('id', $picklistid)->get(['branch']);

        return (count($data) > 0) ? $data[0] : null;
    }

    private function setCustomerEmail($customer_code, $customer_email)
    {
        $sql = 'REPLACE INTO production.pod_email (customer_code, customer_email) VALUES (?, ?)';
        DB::connection('production-vinyl')->insert($sql, [$customer_code, $customer_email]);
    }

    private function uploadProofOfDeliveryToEzcm($path, $meta)
    {
        $now = date('YmdHis');
        $filename = "POD{$meta['order_number']}_{$now}.pdf";
        $uri = 'https://ezcm.starlinewindows.com/ezcm/ezrest/'.self::EZCM_DOMAIN.'/docs/';
        $customer_number = array_key_exists('customer_number', $meta) ? $meta['customer_number'] : null;
        $customer_name = str_replace('&', '&amp;', $meta['customer_name']);
        $xmlFileName = str_replace('.pdf', '', $filename);

        $xml = "
            <?xml version=\"1.0\" encoding=\"utf-8\"?>
            <doc xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" fileName=\"{$xmlFileName}\" convertPdf=\"none\">
                <doctypes>
                    <doctype name=\"PD Shipping Documents\">
                        <field name=\"Order Number\" value=\"{$meta['order_number']}\" />
                        <field name=\"Document Date\" value=\"{$meta['delivery_date']}\" />
                        <field name=\"Customer Number\" value=\"{$customer_number}\" />
                        <field name=\"Customer Name\" value=\"{$customer_name}\" />
                    </doctype>
                </doctypes>
            </doc>
        ";
        $xml = trim($xml);

        $client = new \GuzzleHttp\Client();

        $i = 0;
        while ($i < 100) {
            if (file_exists($path)) {
                $pdf_data = fopen($path, 'r');
                break;
            }
            sleep(2);
            ++$i;
        }

        if (!isset($pdf_data)) {
            file_put_contents(storage_path() . "/POD/POD{$meta['order_number']}.err", 'failed to read pdf');
            return false;
        }

        $post_data = array(
            'verify' => false, // disable ssl check
            'auth' => ['awalmsley', 'Starline1'],
            'synchronous' => true,
            'multipart' => [
                [
                    'name' => 'properties',
                    'contents' => $xml
                ],
                [
                    'name' => 'file',
                    'filename' => "{$xmlFileName}.pdf",
                    'contents' => $pdf_data
                ]
            ]
        );

        $response = $client->request('POST', $uri, $post_data);
        if ($response->getStatusCode() === 200) {
            DB::connection('production-vinyl')->table('production.headers')->where('ordernumber', $meta['order_number'])->update(['ezcm' => 1]);
            return true;
        }
        return false;
    }
}
