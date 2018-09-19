<?php namespace QualityWeb;

class PQPBoothTest extends \Eloquent {

    protected   $connection = 'archdb-admin';
    protected   $table      = 'pqp_reports.PQP_BoothTests';
    protected   $fillable   = ['FrameSeries', 'Tests', 'Failures'];
    protected   $touches    = ['report'];
    public      $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }

    public function frameSeries()
    {
        return $this->hasOne('QualityWeb\PQPFrameSeries', 'Id', 'FrameSeries');
    }

}
