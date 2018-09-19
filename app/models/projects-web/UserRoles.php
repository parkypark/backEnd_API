<?php namespace ProjectsWeb;

use Jenssegers\Mongodb\Model;

class UserRoles extends Model {

    protected   $connection = 'pw2';
    protected   $table      = 'user_roles';
    protected   $dates      = ['created_at', 'updated_at'];
    protected   $fillable   = ['username', 'role'];
    public      $timestamps = true;

}