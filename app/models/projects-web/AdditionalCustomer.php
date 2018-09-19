<?php namespace ProjectsWeb;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class AdditionalCustomer extends \Eloquent {

    use SoftDeletingTrait;

    protected   $connection = 'projects-web';
    protected   $table      = 'additionalcustomers';
    protected   $dates      = ['deleted_at'];
    public      $timestamps = false;

    public function project()
    {
        return $this->belongsTo('ProjectsWeb\Project', 'projectid', 'projectid');
    }

    public function customer()
    {
        return $this->hasOne('ProjectsWeb\Customer', 'id', 'customerid');
    }

    public function salesrep()
    {
        return $this->hasOne('ProjectsWeb\SalesPerson', 'id', 'salesrep');
    }
}