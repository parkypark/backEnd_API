<?php namespace QualityWeb;

class PQPForwardLoad extends \Eloquent {

    protected   $connection = 'archdb-admin';
    protected   $table      = 'pqp_reports.PQP_ForwardLoad';
    protected   $fillable   = ['DateGroup', 'PatioDoors', 'SwingDoors', 'Windows', 'CurtainWall', 'Employees'];
    protected   $touches    = ['report'];
    public      $timestamps = false;

    public function report()
    {
        return $this->belongsTo('QualityWeb\PQPReport', 'ReportId', 'Id');
    }

}
