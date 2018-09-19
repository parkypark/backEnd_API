<?php namespace ProductionWeb;

class VinylProductionPicklist extends \Eloquent {

    protected $connection = 'production-vinyl';
    protected $table = 'picklist';
    public $timestamps = false;

}
