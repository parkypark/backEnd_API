<?php namespace StarlineWindows;

class Frame {
    protected $params           = null;

    public $project_id          = '';
    public $window_tag          = '';
    public $index               = -1;
    public $colour              = 'NA';
    public $components          = array();
    public $frame_series        = 'NA';
    public $tag                 = 'NA';
    public $rect                = array();
    public $bars                = array();
    public $frame_number_from   = -1;
    public $frame_number_to     = -1;

    public function __construct($project_id, $frame_tag, $frame_index, $frame_number_from, $frame_number_to, $params)
    {
        $this->params               = new Params($params);
        $this->project_id           = $project_id;
        $this->colour               = $this->params->getValue('Color');
        $this->frame_series         = $this->params->getValue('Frame');
        $this->window_tag           = strrev(substr(strstr(strrev($frame_tag), '-'), 1));
        $this->tag                  = $frame_tag;
        $this->index                = $frame_index;
        $this->frame_number_from    = $frame_number_from;
        $this->frame_number_to      = $frame_number_to;

        $this->rect = array(
            'x' =>      (float) $this->params->getValue('StartX', 0),
            'y' =>      (float) $this->params->getValue('StartY', 0),
            'width' =>  (float) $this->params->getValue('HW', 0),
            'height' => (float) $this->params->getValue('HH', 0)
        );
    }

    public function createDrawPoints()
    {
        foreach ($this->bars as $bar)
        {
            $bar->points    = [];
            $add            = function($x, $y) use (&$bar) { $bar->points[] = [ 'x' => $x, 'y' => $y ]; };

            if ($bar->shape === 0)
            {
                if ($bar->mitre)
                {
                    $start_joint = $this->findBarByIndex($bar->start_index);
                    $end_joint = $this->findBarByIndex($bar->end_index);
                    $bottom = function ($b) {
                        return $b->rect['y'] + $b->rect['height'];
                    };
                    $top = function ($b) {
                        return $b->rect['y'];
                    };
                    $left = function ($b) {
                        return $b->rect['x'];
                    };
                    $right = function ($b) {
                        return $b->rect['x'] + $b->rect['width'];
                    };

                    if ($bar->rect['width'] > $bar->rect['height'])
                    {
                        $add($left($start_joint), $bottom($bar));
                        $add($right($end_joint), $bottom($bar));
                        $add($left($end_joint), $top($bar));
                        $add($right($start_joint), $top($bar));
                    }
                    else
                    {
                        $add($right($bar), $top($end_joint));
                        $add($right($bar), $bottom($start_joint));
                        $add($left($bar), $top($start_joint));
                        $add($left($bar), $bottom($end_joint));
                    }
                }
                else
                {
                    $top = $bar->rect['y'];
                    $bottom = $top + $bar->rect['height'];
                    $left = $bar->rect['x'];
                    $right = $left + $bar->rect['width'];

                    $add($left, $top);
                    $add($right, $top);
                    $add($right, $bottom);
                    $add($left, $bottom);
                }
            }
        }
    }

    /**
     * @param $index
     * @return Bar
     */
    public function findBarByIndex($index)
    {
        for ($i = 0; $i < count($this->bars); ++$i) {
            if ($this->bars[$i]->index === $index) {
                return $this->bars[$i];
            }
        }
        return false;
    }
}