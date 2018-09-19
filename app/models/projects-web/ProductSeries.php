<?php namespace ProjectsWeb;

use Jenssegers\Mongodb\Eloquent\SoftDeletingTrait;
use Jenssegers\Mongodb\Model;

class ProductSeries extends Model {

    use SoftDeletingTrait;

    protected   $connection = 'pw2';
    protected   $table      = 'product_series';
    protected   $dates      = ['created_at', 'deleted_at', 'updated_at'];
    public      $timestamps = true;

}