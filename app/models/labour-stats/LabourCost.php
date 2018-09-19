<?php namespace LabourStats;

class LabourCost extends \Eloquent {

    protected   $connection = 'labour-stats';
    protected   $table      = 'labour_cost';
    public      $timestamps = false;

}