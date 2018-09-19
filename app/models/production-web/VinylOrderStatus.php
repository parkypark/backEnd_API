<?php namespace ProductionWeb;

class VinylOrderStatus extends \Eloquent {

    protected $connection = 'production-vinyl';
    protected $table = 'orderstatus';
    public $timestamps = false;

}
