<?php namespace LabourStats;

class LabourDistribution extends \Eloquent {

    protected   $connection = 'labour-stats';
    protected   $table      = 'labour_distribution';
    public      $timestamps = false;

}