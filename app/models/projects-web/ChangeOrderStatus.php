<?php namespace ProjectsWeb;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ChangeOrderStatus extends \Eloquent {

    use SoftDeletingTrait;

    protected   $connection = 'projects-web';
    protected   $table      = 'changeorder_status';
    protected   $dates      = ['deleted_at'];
    public      $timestamps = false;

}