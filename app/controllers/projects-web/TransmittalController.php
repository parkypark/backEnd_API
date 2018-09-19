<?php namespace ProjectsWeb;

use App, BaseController, DB, Exception, Response, Input, File, View;

class TransmittalController extends BaseController {

    protected $layout = 'layouts.transmittal';

    public function generateDistributionChecklist()
    {
      $json = Input::get('data');
      if (!($json && strlen($json) > 0))
      {
        return Response::prettyjson(['error' => 'Bad input'], 400);
      }
      $data = json_decode($json);
      $data->distribution = [
        [
          'Angelo Bellabono',
          'Alex Gonzales',
          'Chris Taylor',
          'Dave Boon',
          'Jake Bell',
          'Trevor Alderson',
          'Wayne Millard',
          'Chris Grzyzwacz'
        ],
        [
          'Daryl Wilson',
          'Grant Vass',
          'Marnie Pringle',
          'Michelle Massick',
          'Murray Kennedy',
          'Shawn Wiese',
          'Tyler Lehman'
        ],
        [
          'Joseph Zhang',
          'Remo Schulz',
          'Judy Shannon',
          'Order Entry',
          'Allan Wright',
          'Coordinator'
        ]
      ];

      $html = View::make('transmittals.distribution_checklist', (array)$data)->render();
      if ($html)
      {
        // Get temp file
        $tmp = tempnam(sys_get_temp_dir(), '_api.');

        // Write html input
        file_put_contents("$tmp.html", $html);

        // Print to pdf with phantomjs
        $cmd = '/usr/bin/phantomjs /usr/share/phantomjs/examples/rasterize.js ' . "$tmp.html $tmp.pdf" . ' Letter';
        $response = exec($cmd);

        // Make files get deleted afterwards
        App::finish(function($request, $response) use ($tmp)
        {
          unlink("$tmp.html");
          unlink("$tmp.pdf");
        });

        // Return generated pdf
        return Response::make(file_get_contents("$tmp.pdf"), 200, [
          'Content-Type'              => 'application/pdf',
          'Content-Disposition'       => 'inline; filename=distribution_checklist.pdf',
          'Content-Transfer-Encoding' => 'binary',
          'Accept-Ranges'             => 'bytes'
        ]);
      }

      return Response::prettyjson(['error' => 'Failed to generate distribution checklist'], 500);
    }

    public function generateTransmittal($template)
    {
        $json = Input::get('data');
        if (!($template && strlen($template) > 0 && $json && strlen($json) > 0))
        {
          return Response::prettyjson(['error' => 'Bad input'], 400);
        }
        $data = json_decode($json);

        foreach ($data as $key => $value)
        {
            // parse date values
            if (strpos($key, 'date') !== false)
            {
                $data->$key = date('M d, Y', strtotime($value));
            }
        }

        // save values for reuse
        $this->save($template, $data);

        $html = View::make('transmittals.' . $template, (array)$data)->render();
        if ($html)
        {
            // Get temp file
            $tmp = tempnam(sys_get_temp_dir(), '_api.');

            // Write html input
            file_put_contents("$tmp.html", $html);

            // Print to pdf with phantomjs
            $cmd = '/usr/bin/xvfb-run /usr/bin/phantomjs /usr/share/doc/phantomjs/examples/rasterize.js '
                . "$tmp.html $tmp.pdf"
                . ' Letter';

            $response = exec($cmd);

            // Make files get deleted afterwards
            App::finish(function($request, $response) use ($tmp)
            {
                unlink("$tmp.html");
                unlink("$tmp.pdf");
            });

            // Return generated pdf
            return Response::make(file_get_contents("$tmp.pdf"), 200, [
                'Content-Type'              => 'application/pdf',
                'Content-Disposition'       => "inline; filename=$template.pdf",
                'Content-Transfer-Encoding' => 'binary',
                'Accept-Ranges'             => 'bytes'
            ]);
        }

        return Response::prettyjson(['error' => 'Invalid template'], 400);
    }

    public function show($project_number, $template_name)
    {
      $transmittals = Transmittal::where('project', $project_number)->where('type', $template_name)->get();
      if ($transmittals && count($transmittals) > 0)
      {
          return Response::prettyjson($transmittals[0]);
      }
      return Response::json(false);
    }

    private function save($template_name, $data)
    {
        $transmittals = Transmittal::where('project', $data->project_number)->where('type', $template_name)->get();
        if ($transmittals && count($transmittals) > 0)
        {
            $transmittals[0]->delete();
        }

        $transmittal = new Transmittal();
        $transmittal->project = $data->project_number;
        $transmittal->type = $template_name;
        $transmittal->data = $data;
        $transmittal->save();
    }
}
