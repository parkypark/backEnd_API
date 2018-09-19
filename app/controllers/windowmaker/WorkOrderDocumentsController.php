<?php

class WorkOrderDocumentsController extends BaseController
{

    public function getList()
    {
        $list_number = Input::get('list_number');

        $project_name = Input::get('project_name');
        $project_name = str_replace('"', '-', $project_name);

        $order_type = Input::get('order_type');
        $order_date = Input::get('order_date');

        $patterns = [
            '/mnt/Workorders/{path}/{list_number}*.pdf',
            '/mnt/Workorders/{path}/{list_number}*.PDF',
            '/mnt/Workorders/{path}/{plist_number}*.pdf',
            '/mnt/Workorders/{path}/{plist_number}*.PDF',

            '/mnt/Workorders/{path}/*_{list_number}_*.pdf',
            '/mnt/Workorders/{path}/*_{list_number}_*.PDF',
            '/mnt/Workorders/{path}/*_{plist_number}_*.pdf',
            '/mnt/Workorders/{path}/*_{plist_number}_*.PDF'

            /*'/mnt/Workorders/{path}/{list_number}\ -\ *.pdf',
            '/mnt/Workorders/{path}/{list_number}\ -\ *.PDF',
            '/mnt/Workorders/{path}/{plist_number}-*.pdf',
            '/mnt/Workorders/{path}/{plist_number}-*.PDF',
            '/mnt/Workorders/{path}/{plist_number}\ -\ *.pdf',
            '/mnt/Workorders/{path}/{plist_number}\ -\ *.PDF'*/
        ];

        if (! $order_date) {
            return Response::prettyjson(['error' => 'invalid order date'], 400);
        }
        $order_date = strtotime($order_date);


        if ($order_type === 'WM_PROC_SVC') {
            $path = join('/', [date('Y', $order_date), 'RUSH', date('M d', $order_date)]);
        } else {
            $path = join('/', [date('Y/M', $order_date), $project_name]);
        }

        $get_data = function () use ($patterns, $path, $list_number) {
            $result = [];
            foreach ($patterns as $pattern) {
                $pattern = str_replace('{path}', $path, $pattern);
                $pattern = str_replace('{list_number}', $list_number, $pattern);
                $pattern = str_replace('{plist_number}', sprintf('%03d', $list_number), $pattern);

                foreach (glob($pattern, GLOB_NOSORT) as $file) {
                    if (strstr($file, 'CasementReport.pdf') !== false) {
                        if (filesize($file) === 11100) {
                            continue;
                        };
                    } elseif (strstr($file, 'SliderReport.pdf') !== false) {
                        if (filesize($file) < 11000) {
                            continue;
                        };
                    } elseif (strstr($file, 'SpacerReport.pdf') !== false) {
                        if (filesize($file) < 9000) {
                            continue;
                        }
                    } elseif (strstr($file, 'SpandrelAdapter.pdf') !== false) {
                        if (filesize($file) < 8900) {
                            continue;
                        }
                    }

                    $result[] = $file;
                }
            }
            $result = array_unique($result);
            sort($result);

            return array_map(function ($file) {
                return [
                    'name' => basename($file),
                    'url'  => rawurlencode(basename($file))
                ];
            }, $result);
        };

        // Convert tiffs to pdf
        $this->tiff2Pdf($path, $list_number);
        $documents = $get_data();

        return Response::prettyjson([
            'path'      => "/$path",
            'documents' => $documents
        ]);
    }

    public function getGlassFileList()
    {
        $glass_date = Input::get('glass_date');
        if ($glass_date) {
            $glass_date = date('Y-m-d', strtotime($glass_date));
        } else {
            $glass_date = date('Y-m-d');
        }
        return Response::prettyjson($this->fetchGlassFiles($glass_date));
    }

    public function postGlassImportFile()
    {
        $file = Input::file('file');
        if (! $file->isValid()) {
            return Response::prettyjson(['error' => 'invalid file'], 500);
        }

        $excel = \PHPExcel_IOFactory::load($file->getRealPath());
        $sheet = $excel->getActiveSheet();
        $headings = $sheet->rangeToArray('A2:E2')[0];
        $data_rows = $sheet->rangeToArray("A3:E{$sheet->getHighestRow()}");
        $data = [];
        $glass_date = date('Y-m-d');

        foreach ($data_rows as $row) {
            if ($row[1] && $row[1] > 0) {
                $key = (int)$row[1];

                if (array_key_exists($key, $data)) {
                    $data[$key][2] .= $row[2];
                } else {
                    $data[$key] = $row;
                }

                $glass_date = date('Y-m-d', strtotime($row[0].'-'.date('Y')));
            }
        }

        return Response::prettyjson([
            'headings' => $headings,
            'glass_date' => $glass_date,
            'glass_files' => $this->fetchGlassFiles($glass_date),
            'data' => $data
        ]);
    }

    public function postCoordinatorFab()
    {
        $document = Input::get('document');
        $data = $document['data']['file'];

        if (strlen($data) > 28) {
            $decoded = base64_decode(substr($data, 28));
            $path = str_replace(['"', '&amp;'], ['-', '&'], $document['path']);
            $filename = "/mnt/Workorders{$path}";

            $result = file_put_contents($filename, $decoded);
            $status = $result === false ? 500 : 200;

            return Response::json($result, $status);
        }

        return Response::json('Bad request', 400);
    }

    private function fetchGlassFiles($glass_date)
    {
        $ret = [];
        $glass_files = json_decode(ProcessingWeb\GlassFiles::all());

        $result = Order::select('orders.processreference')
            ->join('workorders.sealedunits', 'sealedunits.ordernumber', '=', 'orders.ordernumber')
            ->where('orders.prefix', 'P')
            ->whereBetween('orders.dateprocessed', [$glass_date, "$glass_date 23:59:59"])
            ->groupBy('orders.processreference')
            ->get();

        $list_numbers = [];
        foreach ($result as $row) {
            $listnum = $row->processreference;
            if (property_exists($glass_files, $listnum)) {
                $ret[$listnum] = array_keys(get_object_vars($glass_files->$listnum));
            }
        }

        return $ret;
    }

    private function tiff2Pdf($path, $list_number)
    {
        $patterns = [
            '/mnt/Workorders/{path}/{list_number}_*.TIF',
            '/mnt/Workorders/{path}/{plist_number}_*.TIF'
        ];

        foreach ($patterns as $pattern) {
            $pattern = str_replace('{path}', $path, $pattern);
            $pattern = str_replace('{list_number}', $list_number, $pattern);
            $pattern = str_replace('{plist_number}', sprintf('%03d', $list_number), $pattern);

            foreach (glob($pattern, GLOB_NOSORT) as $file) {
                $out = str_replace('.TIF', '.pdf', $file);

                if ((! file_exists($out)) || (filemtime($file) > filemtime($out))) {
                    $cmd = '/usr/bin/tiff2pdf -p letter -j -q 90 -f -o ' . escapeshellarg($out) . ' ' . escapeshellarg($file);
                    exec($cmd);
                }
            }
        }
    }
}
