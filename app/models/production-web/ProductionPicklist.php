<?php namespace ProductionWeb;

class ProductionPicklist extends \Eloquent {

    protected $connection = 'production';
    protected $table = 'picklist';
    public $timestamps = false;

}
