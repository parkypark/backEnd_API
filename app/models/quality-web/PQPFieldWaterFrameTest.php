<?php namespace QualityWeb;

class PQPFieldWaterFrameTest extends \Eloquent {

    protected   $connection = 'archdb-admin';
    protected   $table      = 'pqp_reports.PQP_FieldWaterTests';
    protected   $fillable   = ['FrameSeries', 'Tests', 'FailuresMfg', 'FailuresInstall'];
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
