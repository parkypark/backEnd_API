<?php namespace QualityWeb;

class PQPProductivity extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_Productivity';
    protected $fillable   = ['DepartmentId', 'Total'];
    protected $touches    = ['report'];
    public    $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }

    public function department()
    {
        return $this->hasOne('QualityWeb\PQPProductivityDepartment', 'Id', 'DepartmentId');
    }

}
