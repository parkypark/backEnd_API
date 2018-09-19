<?php namespace ProjectsWeb;

class ProjectLogs extends \Eloquent {

    protected   $connection = 'projects-web';
    protected   $table      = 'project_logs';
    public      $timestamps = false;

    public function project()
    {
        return $this->hasOne('ProjectsWeb\Project', 'projectid', 'dataedited');
    }


    public function taskDescription()
    {
        return $this->hasOne('ProjectsWeb\TaskDescription', 'id', 'taskid');
    }
}