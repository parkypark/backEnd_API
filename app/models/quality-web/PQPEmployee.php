<?php namespace QualityWeb;

class PQPEmployee extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_Employees';
    protected $fillable   = ['LocationId', 'In', 'Out'];
    protected $touches    = ['report'];
    public    $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }

    public function location()
    {
        return $this->hasOne('QualityWeb\PQPEmployeeLocation', 'Id', 'LocationId');
    }

}
