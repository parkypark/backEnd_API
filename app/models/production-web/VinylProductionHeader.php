<?php namespace ProductionWeb;

class VinylProductionHeader extends \Eloquent {

    protected $connection = 'production-vinyl';
    protected $table = 'headers';
    public $timestamps = false;

}
