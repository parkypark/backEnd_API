<?php namespace QualityWeb;

class PQPNonConformanceDepartment extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_NonConformance_Departments';
    public    $timestamps = false;

}
