<?php namespace QualityWeb;

class PQPMaterialHandlingCategory extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_MaterialHandling_Categories';
    public    $timestamps = false;

}
