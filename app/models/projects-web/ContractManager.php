<?php namespace ProjectsWeb;

use Jenssegers\Mongodb\Eloquent\SoftDeletingTrait;
use Jenssegers\Mongodb\Model;

class ContractManager extends Model {

    use SoftDeletingTrait;

    protected   $connection = 'pw2';
    protected   $table      = 'contract_managers';
    protected   $dates      = ['created_at', 'deleted_at', 'updated_at'];
    public      $timestamps = true;

    public function projects()
    {
        return $this->hasMany('ProjectsWeb\Project', 'contract_manager_id', '_id');
    }

}