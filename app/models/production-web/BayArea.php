<?php namespace ProductionWeb;

use Eloquent;

class BayArea extends Eloquent {

    protected $connection  = 'production';
    protected $table       = 'bay_areas';
    protected $primaryKey  = 'rack_number';
    protected $dates       = ['updated_at'];
    public $timestamps     = true;

}
