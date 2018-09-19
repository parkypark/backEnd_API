<?php namespace StarlineWindows;

class Component {
    const BOTTOM            = 0;
    const RIGHT             = 1;
    const TOP               = 2;
    const LEFT              = 3;

    private $_parent        = null;
    protected $params       = null;
    public $component_type  = 'NA';
    public $index           = -1;
    public $is_vent         = false;
    public $parent_type     = 'F';
    public $glass_width     = 0;
    public $glass_height    = 0;
    public $direction       = '';
    public $rect            = array();
    public $overlay         = array();

    public function __construct(Frame &$frame, $params)
    {
        $this->_parent          = $frame;
        $this->params           = new Params($params);
        $this->component_type   = $this->params->getValue('CompType', 'NA');
        $this->index            = (int) $this->params->getValue('Index', -1);
        $this->parent_type      = $this->params->getValue('ParentType');
        $this->glass_width      = Utils::Round16($this->params->getValue('GlassWidth'));
        $this->glass_height     = Utils::Round16($this->params->getValue('GlassHeight'));
        $this->restrictor       = $this->params->getValue('REST');
        
        if ($this->params->getValue('s1', -1) !== -1) {
            $this->is_vent = true;

            $this->bars_outside = array(
                (int) $this->params->getValue('s1', -1),
                (int) $this->params->getValue('s2', -1),
                (int) $this->params->getValue('s3', -1),
                (int) $this->params->getValue('s4', -1)
            );

            $this->bars_inside = array(
                (int) $this->params->getValue('l1', -1),
                (int) $this->params->getValue('l3', -1),
                (int) $this->params->getValue('l5', -1),
                (int) $this->params->getValue('l7', -1)
            );
        } else {
            $this->bars_outside = array(
                (int) $this->params->getValue('l1', -1),
                (int) $this->params->getValue('l3', -1),
                (int) $this->params->getValue('l5', -1),
                (int) $this->params->getValue('l7', -1)
            );
        }

        if ($frame) {
            // Get bounds and shift bars
            if ($this->is_vent) {
                $bottom_bar = $frame->findBarByIndex($this->bars_inside[self::BOTTOM]);
                $right_bar = $frame->findBarByIndex($this->bars_inside[self::RIGHT]);

                // Shift
                $bottom_bar->rect['y'] -= $bottom_bar->dim_b;
                $right_bar->rect['x'] -= $bottom_bar->dim_b;
            }

            $bottom_bar = $frame->findBarByIndex($this->bars_outside[self::BOTTOM]);
            $right_bar = $frame->findBarByIndex($this->bars_outside[self::RIGHT]);
            $top_bar = $frame->findBarByIndex($this->bars_outside[self::TOP]);
            $left_bar = $frame->findBarByIndex($this->bars_outside[self::LEFT]);

            $this->rect = array(
                'x' => $left_bar->rect['x'] + $left_bar->rect['width'],
                'y' => $top_bar->rect['y'] + $top_bar->rect['height'],
                'width' => $right_bar->rect['x'] - $left_bar->rect['x'] - $right_bar->dim_b,
                'height' => $bottom_bar->rect['y'] - $top_bar->rect['y'] - $top_bar->dim_b
            );
        }
    }

    public function getParent()
    {
        return $this->_parent;
    }

    public function createOverlay()
    {
        $this->overlay = array('direction' => $this->direction);

        if (strpos($this->_parent->frame_series, '450') !== false || strpos($this->_parent->frame_series, 'HR450') !== false) {
            $cx = $this->rect['x'] + $this->rect['width'] / 2;
            $cy = $this->rect['y'] + $this->rect['height'] / 2;

            // Different overlay for swing doors: glyph
            if ($this->direction === 'left') {
                // Draw <|-
                $this->overlay['points'] = array(
                    [ 'x' => $cx + 9, 'y' => $cy     ],
                    [ 'x' => $cx - 6, 'y' => $cy     ],
                    [ 'x' => $cx,     'y' => $cy - 3 ],
                    [ 'x' => $cx,     'y' => $cy + 3 ],
                    [ 'x' => $cx - 6, 'y' => $cy     ]
                );
            } else {
                // Draw -|>
                $this->overlay['points'] = array(
                    [ 'x' => $cx - 9, 'y' => $cy     ],
                    [ 'x' => $cx + 6, 'y' => $cy     ],
                    [ 'x' => $cx,     'y' => $cy - 3 ],
                    [ 'x' => $cx,     'y' => $cy + 3 ],
                    [ 'x' => $cx + 6, 'y' => $cy     ]
                );
            }
        } else {
            if($this->direction === 'left') {
                // Draw <
                $this->overlay['points'] = array(
                    [ 'x' => $this->rect['x'] + $this->rect['width'], 'y' => $this->rect['y'] ],
                    [ 'x' => $this->rect['x'], 'y' => $this->rect['y'] + $this->rect['height'] / 2 ],
                    [ 'x' => $this->rect['x'] + $this->rect['width'], 'y' => $this->rect['y'] + $this->rect['height'] ]
                );
            } else if ($this->direction === 'right') {
                // Draw >
                $this->overlay['points'] = array(
                    [ 'x' => $this->rect['x'], 'y' => $this->rect['y'] ],
                    [ 'x' => $this->rect['x'] + $this->rect['width'], 'y' => $this->rect['y'] + $this->rect['height'] / 2 ],
                    [ 'x' => $this->rect['x'], 'y' => $this->rect['y'] + $this->rect['height'] ]
                );
            } else if ($this->direction === 'top') {
                // Draw /\
                $this->overlay['points'] = array(
                    [ 'x' => $this->rect['x'], 'y' => $this->rect['y'] + $this->rect['height'] ],
                    [ 'x' => $this->rect['x'] + $this->rect['width'] / 2, 'y' => $this->rect['y'] ],
                    [ 'x' => $this->rect['x'] + $this->rect['width'], 'y' => $this->rect['y'] + $this->rect['height'] ]
                );
            }
        }
    }
}