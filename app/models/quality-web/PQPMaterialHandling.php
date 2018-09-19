<?php namespace QualityWeb;

class PQPMaterialHandling extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_MaterialHandling';
    protected $fillable   = ['CategoryId', 'Processed', 'Failed'];
    protected $touches    = ['report'];
    public    $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }

    public function category()
    {
        return $this->hasOne('QualityWeb\PQPMaterialHandlingCategory', 'Id', 'CategoryId');
    }

}
