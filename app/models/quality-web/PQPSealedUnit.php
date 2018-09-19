<?php namespace QualityWeb;

class PQPSealedUnit extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_SealedUnits';
    protected $fillable   = ['CategoryId', 'Total'];
    protected $touches    = ['report'];
    public    $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }

    public function category()
    {
        return $this->hasOne('QualityWeb\PQPSealedUnitCategory', 'Id', 'CategoryId');
    }

}
