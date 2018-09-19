<?php

class FabSketchController extends \BaseController {
    const BOTTOM = 2;
    const RIGHT = 1;
    const TOP = 0;
    const LEFT = 3;

    /**
     * Get fab sketch data for frame from work order
     *
     * @return Response
     */
    public function getFrameFabSketch()
    {
        $order_number = Input::get('order_number');
        $frame_number = Input::get('frame_number');

        if (! ($order_number))
        {
            return Response::json(false, 400);
        }

        // Fetch data from the work order
        $order = $this->_getWorkOrder($order_number);
        if ($order === false) {
            return Response::prettyjson(array(
                'success' => false,
                'message' => 'Work order does not exist or contains no frames'
            ));
        }
        $project_id = $order->project_id;

        $frames = $this->_getFramesFromWorkOrder($order_number, $frame_number);
        if ($frames === false) {
            return Response::prettyjson(array(
                'success' => false,
                'message' => 'No frames'
            ));
        }

        $ret = [];
        foreach ($frames as $frame)
        {
            $frame_index = $frame->frameindex;
            $frame_tag = substr(strstr($frame->specialinstruction, '  ', true), 4);

            // Fetch frame data from win_library
            $frame_data = $this->_getFrameData(
                $project_id, $frame_tag, $frame_index,
                $frame->framenumberfrom, $frame->framenumberto);

            if ($frame_data === null)
            {
                // Frame does not exist in win_library
                continue;
            }

            // Fetch bar data from win_library
            $this->_getExtrusionData($frame_data);

            // Flip y-axis and fix negative start point if required
            $minX = $minY = 999999;
            $maxX = $maxY = 0;
            $radius = 0;

            foreach ($frame_data->bars as $bar)
            {
                if ($bar->shape === 1 && $bar->path['cp'] !== false) {
                    $radius = sqrt( pow($bar->path['cp']['x'] - $bar->path['b']['x'], 2) + pow($bar->path['cp']['y'] - $bar->path['b']['y'], 2) );
                }

                $x1 = $bar->rect['x'];
                $y1 = $bar->rect['y'];
                $x2 = $bar->rect['x'] + $bar->rect['width'];
                $y2 = $bar->rect['y'] + $bar->rect['height'];

                $minX = min($minX, $x1);
                $minY = min($minY, $y1);
                $maxX = max($maxX, $x2);
                $maxY = max($maxY, $y2);
            }
            $frame_data->rect['y'] -= $radius * pi();
            $frame_data->rect['width'] = max($frame_data->rect['width'], $maxX - $minX) + 5;

            $frame_data->rect['height'] = max($frame_data->rect['height'], $maxY - $minY) + 5;
            foreach ($frame_data->bars as $bar) {
                $bar->computeBounds($minX, $minY, $maxY);
            }

            // Fetch component data from win_library
            $this->_getComponentData($frame_data);

            // Create draw points
            $frame_data->createDrawPoints();

            // Add to return array
            $ret[] = $frame_data;
        }

        // All done: return result
        return Response::prettyjson(array(
            'success' => true,
            'project_id' => $order->project_id,
            'frames' => $ret
        ));
    }


    /**
     * Get data from work order
     *
     * @return mixed
     */
    private function _getWorkOrder($order_number)
    {
        $sql = 'SELECT * FROM workorders.orders WHERE ordernumber = ? AND operator NOT IN ("WM_PROC_MISC", "WM_PROC_OE")';
        $order = DB::connection('archdb')->select($sql, [$order_number]);
        return $order ? $order[0] : false;
    }

    /**
     * @param $order_number
     * @param $frame_number
     * @return stdClass
     */
    private function _getFramesFromWorkOrder($order_number, $frame_number = null)
    {
        $sql = 'SELECT * FROM workorders.frameproducts WHERE ordernumber = ?';
        $q_params = [$order_number];

        if ($frame_number)
        {
            $sql .= ' AND ? BETWEEN framenumberfrom AND framenumberto';
            $q_params[] = $frame_number;
        }

        $ret = DB::connection('archdb')->select($sql, $q_params);
        return $ret && count($ret) ? $ret : false;
    }

    /**
     * @param $project_number
     * @param $window_tag
     * @param $frame_tag
     * @param $frame_index
     * @return \StarlineWindows\Frame
     */
    private function _getFrameData($project_number, $frame_tag, $frame_index, $frame_number_from, $frame_number_to)
    {
        $window_tag = strrev(substr(strstr(strrev($frame_tag), '-'), 1));
        $sql = 'SELECT Param FROM projects.win_library
                WHERE ProjectNumber = ? AND WinNumber = ? AND LEFT(WinItemType, 1) = 2 AND WinItemNumber = ?';
        $params = DB::connection('archdb')->select($sql, [$project_number, $window_tag, $frame_index]);

        if (! count($params))
        {
            return null;
        }

        $params = explode(',', $params[0]->Param);

        return new StarlineWindows\Frame(
            $project_number, $frame_tag, $frame_index,
            $frame_number_from, $frame_number_to, $params
        );
    }

    /**
     * @param $project_id
     * @param $window_tag
     * @param $frame_index
     * @param \StarlineWindows\Frame $frame
     */
    private function _getComponentData(&$frame)
    {
        $sql = 'SELECT Param FROM projects.win_library WHERE ProjectNumber = ? AND WinNumber = ? AND LEFT(WinItemType, 1) = 4 AND WinItemNumber = ?';
        $component_params = DB::connection('archdb')->select($sql, [$frame->project_id, $frame->window_tag, $frame->index]);

        $frame->components = [];
        for ($i = 0; $i < count($component_params); ++$i)
        {
            $params = explode(',', $component_params[$i]->Param);
            $component = new StarlineWindows\Component($frame, $params);

            if ($component->is_vent)
            {
                // Determine opening direction and draw appropriate overlay
                $sql = 'SELECT * FROM mfg.comp_designs WHERE syscode = ? AND designcode = ?';
                $design = DB::connection('archdb')->select($sql, [$component->getParent()->frame_series, $component->component_type]);

                switch ($design[0]->opening_dir)
                {
                    case 1:
                        $component->direction = 'left';
                        break;

                    case 2:
                        $component->direction = 'right';
                        break;

                    case 3:
                        $component->direction = 'top';
                        break;

                    default:
                        $component->direction = 'fixed';
                        break;
                }

                $component->createOverlay();
            }

            $frame->components[] = $component;
        }
    }

    /**
     * @param \StarlineWindows\Frame $frame
     * @param int $frame_index
     * @param bool $item_index
     */
    private function _getExtrusionData(&$frame)
    {
        // Get extrusions
        $sql = 'SELECT Param
                  FROM projects.win_library
                 WHERE ProjectNumber = ?
                   AND WinNumber = ?
                   AND LEFT(WinItemType, 1) = 3
                   AND WinItemNumber = ?';

        $params = [$frame->project_id, $frame->window_tag, $frame->index];
        $extrusion_params = DB::connection('archdb')->select($sql, $params);

        $frame->bars = [];
        for ($i = 0; $i < count($extrusion_params); ++$i) {
            $params = explode(',', $extrusion_params[$i]->Param);
            $frame->bars[] = new StarlineWindows\Bar($frame, $params);
        }
    }
}