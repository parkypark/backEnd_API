<?php namespace ProjectsWeb;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Document extends \Eloquent {

    use SoftDeletingTrait;

    protected   $connection = 'projects-web';
    protected   $table      = 'documents';
    protected   $dates      = ['created_at', 'updated_at', 'deleted_at'];

    public function project()
    {
        return $this->belongsTo('ProjectsWeb\Project', 'projectid', 'project_id');
    }

}