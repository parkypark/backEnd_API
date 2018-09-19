<?php namespace QualityWeb;

class PQPExtrusionQuality extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_ExtrusionQuality';
    protected $fillable   = ['CategoryId', 'Received', 'Processed', 'Failed'];
    protected $touches    = ['report'];
    public    $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }

    public function category()
    {
        return $this->hasOne('QualityWeb\PQPExtrusionQualityCategory', 'Id', 'CategoryId');
    }

}
