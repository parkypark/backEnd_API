<?php namespace QualityWeb;

class PQPEmployeeLocation extends \Eloquent {

    protected $connection = 'archdb-admin';

    protected $table = 'pqp_reports.PQP_Employees_Locations';

    public $timestamps = false;

}
