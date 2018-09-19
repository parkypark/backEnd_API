<?php namespace SignOffs\Model;

class Technicians extends \Eloquent {

    protected $connection = 'archdb';
    protected $primaryKey = 'Id';
    protected $table = 'installation_floorsignoffs.technicians';
    protected $guarded = ['Id', 'Password'];
    public $timestamps = false;

}
