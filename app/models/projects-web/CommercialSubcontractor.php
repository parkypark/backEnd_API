<?php namespace ProjectsWeb;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class CommercialSubcontractor extends \Eloquent {

    use SoftDeletingTrait;

    protected   $connection = 'projects-web';
    protected   $table      = 'commercial_subcontractors';
    protected   $dates      = ['deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::updating(function($model)
        {
            $user = JWTAuth::parseToken()->toUser();
            $model->updated_by = $user->id;
        });

        static::deleting(function($model)
        {
            $user = JWTAuth::parseToken()->toUser();

            // Needed because soft delete = update one column and using eloquent would trigger an update event
            DB::connection('projects-web')
                ->update('UPDATE headers SET deleted_by = ? WHERE id = ?', [$user->id, $model->id]);
        });

        static::saving(function($model)
        {
            $user = JWTAuth::parseToken()->toUser();
            $model->created_by = $user->id;
        });
    }

    public function project()
    {
        return $this->belongsTo('ProjectsWeb\Project', 'projectid', 'projectid');
    }

    public function commercial()
    {
        return $this->belongsTo('ProjectsWeb\Commercial', 'projectid', 'projectid');
    }

    public function subcontractor()
    {
        return $this->hasOne('ProjectsWeb\Subcontractor', 'id', 'subcontractorid');
    }
}