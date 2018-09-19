<?php namespace QualityWeb;

class PQPFabrication extends \Eloquent {

    protected   $connection = 'archdb-admin';
    protected   $table      = 'pqp_reports.PQP_Fabrication';
    protected   $fillable   = ['TypeId', 'Processed', 'Failed'];
    protected   $touches    = ['report'];
    public      $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }

    public function type()
    {
        return $this->hasOne('QualityWeb\PQPFabricationType', 'Id', 'TypeId');
    }

}
