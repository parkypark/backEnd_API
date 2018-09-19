<?php namespace SignOffs\Model;

class ProjectsHeld extends \Eloquent {

    protected $connection = 'floor-signoffs';
    protected $primaryKey = 'project_id';
    protected $table = 'installation_floorsignoffs.projects_held';
    public $timestamps = false;

}
