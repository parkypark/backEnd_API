<?php namespace SignOffs\Controller;

use App, PDO, Response, View;

class ReportController extends \BaseController {
    const RASTERIZE_PROGRAM = '/usr/bin/xvfb-run /usr/bin/wkhtmltopdf';

    public function get($project_id, $report_id)
    {
        $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=installation_floorsignoffs';
        $dbh = new PDO($connection_string, 'floorsignoffs', 'bw8dnak+9', [PDO::ATTR_PERSISTENT => false]);

        // cache inspectionid => inspection name
        $stmt = $dbh->prepare('SELECT * FROM inspections');
        $stmt->execute();

        $inspectionMap = [];
        while ($row = $stmt->fetchObject())
        {
            $inspectionMap[$row->Id] = $row->Inspection;
        }

        // get signoff data
        $stmt = $dbh->prepare('CALL get_all_signoffs_for_project(?, ?)');
        $stmt->execute([$project_id, $report_id]);

        // group data by building -> floor -> window
        $data = [];
        while ($row = $stmt->fetchObject())
        {
            if (! array_key_exists($row->BuildingName, $data))
            {
                $data[$row->BuildingName] = [];
            }

            if (! array_key_exists($row->FloorName, $data[$row->BuildingName]))
            {
                $data[$row->BuildingName][$row->FloorName] = [];
            }

            $window = $row->Window_Name.'-'.$row->WindowIndex;
            if (! array_key_exists($window, $data[$row->BuildingName][$row->FloorName]))
            {
                $data[$row->BuildingName][$row->FloorName][$window] = [];
            }

            $data[$row->BuildingName][$row->FloorName][$window][] = $row;
        }

        if (count($data) < 1)
        {
            return 'No signoff data for selected project';
        }

        // paginate and extract inspections
        $page = 0;
        $signoffs = [];
        foreach ($data as $building_name => $building_data)
        {
            foreach ($building_data as $floor_name => $floor_data)
            {
                $page++;
                $signoffs[$page] = [
                    'building_name' => $building_name,
                    'floor_name' => $floor_name,
                    'windows' => [],
                    'inspections' => [],
                    'notes' => []
                ];

                foreach ($floor_data as $window_name => $window_data)
                {
                    if (count($signoffs[$page]['windows']) >= 20)
                    {
                        $signoffs[++$page] = [
                            'building_name' => $building_name,
                            'floor_name' => $floor_name,
                            'windows' => [],
                            'inspections' => [],
                            'notes' => []
                        ];
                    }

                    foreach ($window_data as $row)
                    {
                        // project name and field inspection technician
                        $signoffs[$page]['project_name'] = $project_name = $row->ProjectName;
                        $signoffs[$page]['technician'] = $row->Technician;

                        // notes
                        if ($row->Notes)
                        {
                            $note = $window_name . ': ' . $row->Notes;
                            if (! in_array($note, $signoffs[$page]['notes']))
                            {
                                $signoffs[$page]['notes'][] = $note;
                            }
                        }

                        // windows and inspection dates
                        $signoffs[$page]['windows'][$window_name] = $row->ReportDate;

                        // inspection data
                        if (! array_key_exists($row->Category, $signoffs[$page]['inspections']))
                        {
                            $signoffs[$page]['inspections'][$row->Category] = [];
                        }

                        $inspectionData = explode(',', $row->Inspections);
                        foreach ($inspectionData as $inspection)
                        {
                            $kvp = explode('=', $inspection);
                            $name = $inspectionMap[$kvp[0]];

                            if (! array_key_exists($name, $signoffs[$page]['inspections'][$row->Category]))
                            {
                                $signoffs[$page]['inspections'][$row->Category][$name] = [];
                            }

                            $signoffs[$page]['inspections'][$row->Category][$name][$window_name] = $kvp[1];
                        }
                    }
                }
            }
        }

        $html = View::make('signoffs.prepped_openings', ['signoffs' => $signoffs])->render();

        if ($html)
        {
            // Create temp file for html, rename it to html extension or converter will fail
            $tmp = tempnam(sys_get_temp_dir(), '_api.');
            rename($tmp, "$tmp.html");
            $htmlFile = "$tmp.html";
            $pdfFile = "$tmp.pdf";

            // Write html input
            file_put_contents($htmlFile, $html);

            // Print to pdf with phantomjs
            $cmd = self::RASTERIZE_PROGRAM . " -O landscape $htmlFile $pdfFile";
            $response = exec($cmd);

            // Make files get deleted afterwards
            App::finish(function($request, $response) use ($htmlFile, $pdfFile)
            {
                unlink($htmlFile);
                unlink($pdfFile);
            });

            // Return generated pdf
            $report_name = $report_id == 1 ? 'Prepped Openings' : 'Complete Product';

            return Response::make(file_get_contents($pdfFile), 200, [
                'Content-Type'              => 'application/pdf',
                'Content-Disposition'       => "filename={$project_name} ({$project_id}) {$report_name}.pdf",
                'Content-Transfer-Encoding' => 'binary',
                'Accept-Ranges'             => 'bytes'
            ]);
        }

        return Response::prettyjson(['error' => 'Bad request'], 400);
    }
}
