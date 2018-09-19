<?php namespace ProjectsWeb;

use Jenssegers\Mongodb\Eloquent\SoftDeletingTrait;
use Jenssegers\Mongodb\Model;
use JWTAuth;

class ChangeOrder extends Model {

    use SoftDeletingTrait;

    protected $connection = 'pw2';
    protected $table      = 'change_orders';

    protected $dates = [
        'created_at', 'deleted_at', 'updated_at',
        'date_submitted', 'date_approved', 'date_rejected'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function($model)
        {
            $user = JWTAuth::parseToken()->toUser();
            $model->created_by = $user->full_name;
        });

        static::deleting(function($model)
        {
            $user = JWTAuth::parseToken()->toUser();
            $model->deleted_by = $user->full_name;
            $model->save();
        });

        static::updating(function($model)
        {
            $user = JWTAuth::parseToken()->toUser();
            $model->updated_by = $user->full_name;
        });
    }
}
