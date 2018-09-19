<?php namespace QualityWeb;

class PQPAluminumRecycling extends \Eloquent {

    protected   $connection = 'archdb-admin';
    protected   $table      = 'pqp_reports.PQP_AluminumRecycling';
    protected   $fillable   = ['ReportId', 'Processed', 'Recycled', 'SAPACostPerLb'];
    protected   $touches    = ['report'];
    public      $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }
}
