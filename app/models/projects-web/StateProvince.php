<?php namespace ProjectsWeb;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class StateProvince extends \Eloquent {

    use SoftDeletingTrait;

    protected   $connection = 'projects-web';
    protected   $table      = 'province_state';
    protected   $dates      = ['deleted_at'];
    public      $timestamps = false;

}