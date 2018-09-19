<?php namespace QualityWeb;

class PQPProductivityDepartment extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_Productivity_Departments';
    public    $timestamps = false;

}
