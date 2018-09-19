<?php namespace ProductionWeb;

class OrderStatus extends \Eloquent
{
    protected $connection = 'production';
    protected $table = 'orderstatus';
    public $timestamps = false;
}
