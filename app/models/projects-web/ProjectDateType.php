<?php namespace ProjectsWeb;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ProjectDateType extends \Eloquent {

    use SoftDeletingTrait;

    protected   $connection = 'projects-web';
    protected   $table      = 'project_date_types';
    protected   $dates      = ['created_at', 'deleted_at', 'updated_at'];
    public      $timestamps = true;

}