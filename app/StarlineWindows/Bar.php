<?php namespace StarlineWindows;

class Bar {
    private $_parent        = null;
    protected $params       = null;

    public $dim_b           = 0;
    public $index           = -1;
    public $profile_code    = 'NA';
    public $profile_type    = 'NA';
    public $start_index     = -1;
    public $end_index       = -1;
    public $shape           = 0;
    public $mitre           = false;
    public $rf              = false;
    public $rf_dir          = null;
    public $rect            = array();
    public $line            = array();
    public $points          = array();
    public $path            = array();

    public function __construct($parent, $params)
    {
        // Class variables
        $this->_parent      = $parent;
        $this->params       = new Params($params);

        // Required properties
        $this->dim_b        = Utils::Round16($this->params->getValue('dim_b', 0));
        $this->index        = (int) $this->params->getValue('Index', -1);
        $this->profile_code = $this->params->getValue('ProfCode');
        $this->profile_type = $this->params->getValue('ProfType');
        $this->start_index  = (int) $this->params->getValue('STARTITEM', -1);
        $this->end_index    = (int) $this->params->getValue('ENDITEM', -1);
        $this->shape        = (int) $this->params->getValue('Shape', 0);

        // Shape
        if ($this->shape === 1) {
            $this->_getPath();
        }

        // Ripping info
        if ($this->params->getValue('RF') !== false && $this->params->getValue('RFDir') !== false) {
            $this->rf       = (float) $this->params->getValue('RF');
            $this->rf_dir   = (float) $this->params->getValue('RFDir');
        }

        // Mitred or not
        $cut_angle          = (int) $this->params->getValue('cutangle', 90);
        $start_angle        = (int) $this->params->getValue('StartAngle', 90);
        $end_angle          = (int) $this->params->getValue('EndAngle', 90);
        $this->mitre        = ($cut_angle !== 0 && $cut_angle !== 90) || ($start_angle !== 0 && $start_angle !== 90)|| ($end_angle !== 0 && $end_angle !== 90);

        // Calculate space occupied by this bar
        $this->computeBounds();
    }

    public function getParent()
    {
        return $this->_parent;
    }

    public function computeBounds($minX = 0, $minY = 0, $maxY = -1)
    {
        $x1 = Utils::Round16($this->params->getValue('StartX', 0));
        if ($maxY !== -1) {
            $y1 = Utils::Round16($maxY - $this->params->getValue('StartY', 0));
        } else {
            $y1 = Utils::Round16($this->params->getValue('StartY', 0));
        }

        $x2 = Utils::Round16($this->params->getValue('EndX', 0));
        if ($maxY !== -1) {
            $y2 = Utils::Round16($maxY - $this->params->getValue('EndY', 0));
        } else {
            $y2 = Utils::Round16($this->params->getValue('EndY', 0));
        }

        $width = max($this->dim_b, abs($x2 - $x1));
        $height = max($this->dim_b, abs($y2 - $y1));

        if ($this->index === 1) {
            $y1 -= $this->dim_b;
            $y2 -= $this->dim_b;
        } elseif ($this->index === 2) {
            $x1 -= $this->dim_b;
            $x2 -= $this->dim_b;
        } elseif ($this->profile_type === 'T') {
            if ($width > $height) {
                $y1 -= $this->dim_b / 2;
                $y2 -= $this->dim_b / 2;
            } else {
                $x1 -= $this->dim_b;
                $x2 -= $this->dim_b;
            }
        }

        // Correct negative origin to (0, 0)
        if ($minX < 0) {
            $x1 += abs($minX);
            $x2 += abs($minX);
        }

        if ($minY < 0) {
            $y1 += abs($minY);
            $y2 += abs($minY);
        } else {
            $y1 -= $minY;
            $y2 -= $minY;
        }

        $x1 += 0.5;
        $x2 += 0.5;

        $this->line = array(
            ['x' => $x1, 'y' => $y1],
            ['x' => $x2, 'y' => $y2]
        );

        $this->rect = array(
            'x' => min($x1, $x2),
            'y' => min($y1, $y2),
            'width' => $width,
            'height' => $height
        );

        if ($this->shape !== 0) $this->_getPath($maxY);
    }

    private function _getPath($maxY = false)
    {
        $parse = function($param) use ($maxY) {
            $this->path[$param] = false;
            $key = strtoupper($param);

            if ($this->params->getValue($key . '_X') !== false) {
                $this->path[$param] = array(
                    'x' => (float) $this->params->getValue("{$key}_X") + $this->dim_b / 2,
                    'y' => (float) $this->params->getValue("{$key}_Y")
                );

                $this->path[$param]['x'] += $this->dim_b / 4;
                $this->path[$param]['y'] -= $this->dim_b;

                if ($maxY !== false) $this->path[$param]['y'] = $maxY - $this->path[$param]['y'];
            }
        };

        $parse('a');
        $parse('b');
        $parse('c');
        $parse('d');
        $parse('e');
        $parse('f');
        $parse('cp');
    }
}