<?php namespace LabourStats;

use DB, Input, Response;

class LabourStatsController extends \BaseController {

    public function getCost()
    {
        $first_of_month = date('Y-m-01');
        $today          = date('Y-m-d');
        $date_start     = Input::get('date_start', $first_of_month);
        $date_end       = Input::get('date_end', $today);

        $data = LabourCost
            ::select([
                DB::raw('DATE_FORMAT(date_glazed, "%Y-%m") AS `year_month`'),
                'frame_type',
                DB::raw('SUM(total_minutes) AS total_minutes')
            ])
            ->where('date_glazed', '>=', $date_start)
            ->where('date_glazed', '<=', $date_end)
            ->groupBy('year_month', 'frame_type')
            ->get();

        return Response::prettyjson($data);
    }

    public function getDistribution()
    {
        $first_of_month = date('Y-m-01');
        $today          = date('Y-m-d');
        $date_start     = Input::get('date_start', $first_of_month);
        $date_end       = Input::get('date_end', $today);

        $data = LabourDistribution
            ::select([
                DB::raw('DATE_FORMAT(date_glazed, "%Y-%m") AS `year_month`'),
                'production_group',
                'series_applicable',
                'series_not_applicable',
                DB::raw('SUM(total_minutes) AS total_minutes')
            ])
            ->where('date_glazed', '>=', $date_start)
            ->where('date_glazed', '<=', $date_end)
            ->groupBy('year_month', 'production_group')
            ->get();

        return Response::prettyjson($data);
    }

    public function getGlazingTotals()
    {
        $report_year    = Input::get('year', date('Y'));
        $report_month   = Input::get('month', date('m'));

        $data = GlazingTotals
            ::select([
                DB::raw('DATE_FORMAT(date_glazed, "%Y-%m") AS `year_month`'),
                'frame_series',
                DB::raw('SUM(total_frames) AS total_frames'),
                DB::raw('SUM(total_sqft) AS total_sqft')
            ])
            ->where(DB::raw('YEAR(date_glazed)'), '=', $report_year)
            ->where(DB::raw('MONTH(date_glazed)'), '=', $report_month)
            ->groupBy('year_month', 'frame_series')
            ->get();

        return Response::prettyjson($data);
    }



    public function getMinutesPerFrameGlazed()
    {
        $report_year    = Input::get('year', date('Y'));
        $report_month   = Input::get('month', date('m'));

        $sql = '
            SELECT
                `year_month`,
                production_group,
                total_minutes,
                (
                    SELECT
                        SUM(G.total_frames)
                    FROM
                        glazing_totals AS G
                    WHERE
                        (DATE_FORMAT(G.date_glazed, "%Y-%m") = LD.`year_month`) AND
                        (LD.series_applicable IS NULL OR FIND_IN_SET(G.frame_series, LD.series_applicable) > 0) AND
                        (LD.series_not_applicable IS NULL OR FIND_IN_SET(G.frame_series, LD.series_not_applicable) = 0)
                ) AS total_frames
            FROM
                (
                    SELECT
                        DATE_FORMAT(date_glazed, "%Y-%m") AS `year_month`,
                        production_group,
                        series_applicable,
                        series_not_applicable,
                        ROUND(SUM(total_minutes), 2) AS total_minutes
                    FROM
                        labour_distribution
                    WHERE
                        YEAR(date_glazed) = :report_year AND MONTH(date_glazed) = :report_month
                    GROUP BY
                        `year_month`,
                        production_group
                ) AS LD
        ';
        
        $data = DB
            ::connection('labour-stats')
            ->select(DB::raw($sql), ['report_year' => $report_year, 'report_month' => $report_month]);

        return Response::prettyjson($data);
    }

    public function getCostingVsLabour()
    {
        $report_year    = Input::get('year', date('Y'));
        $report_month   = Input::get('month', date('m'));

        $sql = '
            SELECT
                `year_month`,
                SUM(total_frames) AS total_frames,
                ROUND(SUM(total_minutes) / SUM(total_frames), 2) AS labour_minutes_per_frame,
                ROUND(SUM(total_cost) / SUM(total_frames), 2) AS cost_minutes_per_frame
            FROM (SELECT DATE_FORMAT(G.date_glazed, "%Y-%m") as `year_month`,
                         SUM(G.total_frames) AS total_frames,
                         0 AS total_minutes,
                         0 AS total_cost
                    FROM glazing_totals AS G
                   WHERE YEAR(G.date_glazed) = ? AND MONTH(G.date_glazed) = ?
                GROUP BY `year_month`

                UNION

                  SELECT DATE_FORMAT(LD.date_glazed, "%Y-%m") as `year_month`,
                         0 AS total_frames,
                         SUM(LD.total_minutes) AS total_minutes,
                         0 AS total_cost
                    FROM labour_distribution AS LD
                   WHERE YEAR(LD.date_glazed) = ? AND MONTH(LD.date_glazed) = ?
                GROUP BY `year_month`

                UNION

                  SELECT DATE_FORMAT(C.date_glazed, "%Y-%m") AS `year_month`,
                         0 AS total_frames,
                         0 AS total_minutes,
                         SUM(C.total_minutes) AS total_cost
                    FROM labour_cost AS C
                   WHERE YEAR(C.date_glazed) = ? AND MONTH(C.date_glazed) = ?
                GROUP BY `year_month`) AS costing_vs_labour
            GROUP BY
              `year_month`
        ';

        $parameters = [];
        for ($i = 0; $i < 3; ++$i) {
            $parameters[] = $report_year;
            $parameters[] = $report_month;
        }

        $data = DB
            ::connection('labour-stats')
            ->select(DB::raw($sql), $parameters);

        return Response::prettyjson($data);
    }
}