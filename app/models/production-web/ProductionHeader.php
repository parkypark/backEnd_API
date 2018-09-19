<?php namespace ProductionWeb;

class ProductionHeader extends \Eloquent {

    protected $connection = 'production';
    protected $table = 'headers';
    public $timestamps = false;

}
