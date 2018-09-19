<?php namespace SignOffs\Model;

class Projects extends \Eloquent {

    protected $connection = 'archdb';

    protected $primaryKey = 'ProjectNumber';

    protected $table = 'installation_floorsignoffs.activeProjects';

    public $timestamps = false;

}