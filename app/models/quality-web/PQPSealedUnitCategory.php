<?php namespace QualityWeb;

class PQPSealedUnitCategory extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_SealedUnits_Categories';
    public    $timestamps = false;

}
