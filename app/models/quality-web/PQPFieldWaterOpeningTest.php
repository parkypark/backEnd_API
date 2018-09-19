<?php namespace QualityWeb;

class PQPFieldWaterOpeningTest extends \Eloquent {

    protected   $connection = 'archdb-admin';
    protected   $table      = 'pqp_reports.PQP_FieldWaterTests_Openings';
    protected   $fillable   = ['Tests', 'Failures'];
    protected   $touches    = ['report'];
    public      $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }

}
