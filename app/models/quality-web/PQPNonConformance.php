<?php namespace QualityWeb;

class PQPNonConformance extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_NonConformance';
    public    $timestamps = false;

    public function department()
    {
        return $this->hasOne('QualityWeb\PQPNonConformanceDepartment', 'Id', 'DepartmentId');
    }

}
