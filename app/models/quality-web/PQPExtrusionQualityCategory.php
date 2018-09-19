<?php namespace QualityWeb;

class PQPExtrusionQualityCategory extends \Eloquent {

    protected   $connection = 'archdb-admin';
    protected   $table      = 'pqp_reports.PQP_ExtrusionQuality_Categories';
    public      $timestamps = false;

}
