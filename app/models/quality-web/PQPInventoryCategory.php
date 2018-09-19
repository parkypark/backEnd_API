<?php namespace QualityWeb;

class PQPInventoryCategory extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_Inventory_Categories';
    public    $timestamps = false;

}
