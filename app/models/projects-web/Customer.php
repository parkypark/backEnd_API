<?php namespace ProjectsWeb;

use Jenssegers\Mongodb\Eloquent\SoftDeletingTrait;
use Jenssegers\Mongodb\Model;

class Customer extends Model {

    use SoftDeletingTrait;

    protected   $connection = 'pw2';
    protected   $table      = 'customers';
    protected   $dates      = ['created_at', 'deleted_at', 'updated_at'];
    public      $timestamps = true;

    /*public function contacts()
    {
        return $this->hasMany('ProjectsWeb\CustomerContact', 'customer_id', '_id');
    }*/

}
