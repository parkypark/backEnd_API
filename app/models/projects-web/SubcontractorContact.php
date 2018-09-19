<?php namespace ProjectsWeb;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class SubcontractorContact extends \Eloquent {

    use SoftDeletingTrait;

    protected   $connection = 'projects-web';
    protected   $table      = 'subcontractors_contacts';
    protected   $dates      = ['deleted_at'];
    public      $timestamps = false;

}