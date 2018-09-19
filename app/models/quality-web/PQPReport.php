<?php namespace QualityWeb;

class PQPReport extends \Eloquent {

    protected $connection = 'archdb-admin';
    protected $table      = 'pqp_reports.PQP_Reports';
    protected $primaryKey = 'Id';
    protected $fillable   = ['Date', 'Locked'];
    protected $dates      = ['created_at', 'updated_at'];
    public    $timestamps = true;

    public function aluminumRecycling()
    {
        return $this->hasOne('QualityWeb\PQPAluminumRecycling', 'ReportId', 'Id');
    }

    public function boothTests()
    {
        return $this->hasMany('QualityWeb\PQPBoothTest', 'ReportId', 'Id');
    }

    public function employees()
    {
        return $this->hasMany('QualityWeb\PQPEmployee', 'ReportId', 'Id');
    }

    public function extrusionQuality()
    {
        return $this->hasMany('QualityWeb\PQPExtrusionQuality', 'ReportId', 'Id');
    }

    public function fabrication()
    {
        return $this->hasMany('QualityWeb\PQPFabrication', 'ReportId', 'Id');
    }

    public function fieldWaterFrameTests()
    {
        return $this->hasMany('QualityWeb\PQPFieldWaterFrameTest', 'ReportId', 'Id');
    }

    public function fieldWaterOpeningTests()
    {
        return $this->hasOne('QualityWeb\PQPFieldWaterOpeningTest', 'ReportId', 'Id');
    }

    public function forwardLoad()
    {
        return $this->hasMany('QualityWeb\PQPForwardLoad', 'ReportId', 'Id');
    }

    public function inventory()
    {
        return $this->hasMany('QualityWeb\PQPInventory', 'ReportId', 'Id');
    }

    public function materialHandling()
    {
        return $this->hasMany('QualityWeb\PQPMaterialHandling', 'ReportId', 'Id');
    }

    public function nonConformance()
    {
        return $this->hasMany('QualityWeb\PQPNonConformance', 'ReportId', 'Id');
    }

    public function productionQuality()
    {
        return $this->hasOne('QualityWeb\PQPProductionQuality', 'ReportId', 'Id');
    }

    public function productivity()
    {
        return $this->hasMany('QualityWeb\PQPProductivity', 'ReportId', 'Id');
    }

    public function sealedUnits()
    {
        return $this->hasMany('QualityWeb\PQPSealedUnit', 'ReportId', 'Id');
    }

}
