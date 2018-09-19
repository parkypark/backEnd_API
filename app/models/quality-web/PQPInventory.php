<?php namespace QualityWeb;

class PQPInventory extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_Inventory';
    protected $fillable   = ['CategoryId', 'TypeId', 'Total'];
    protected $touches    = ['report'];
    public    $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }

    public function category()
    {
        return $this->hasOne('QualityWeb\PQPInventoryCategory', 'Id', 'CategoryId');
    }

    public function type()
    {
        return $this->hasOne('QualityWeb\PQPInventoryType', 'Id', 'TypeId');
    }

}
