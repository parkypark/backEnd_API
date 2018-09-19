<?php namespace QualityWeb;

class PQPInventoryType extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_Inventory_Types';
    public    $timestamps = false;

}
