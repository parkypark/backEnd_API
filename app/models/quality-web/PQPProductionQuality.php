<?php namespace QualityWeb;

class PQPProductionQuality extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_ProductionQuality';
    protected $fillable   = ['Inspections', 'Failures'];
    protected $touches    = ['report'];
    public    $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }

}
