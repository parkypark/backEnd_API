<?php namespace ProjectsWeb;

use Jenssegers\Mongodb\Eloquent\SoftDeletingTrait;
use Jenssegers\Mongodb\Model;
use JWTAuth;

class Project extends Model {

    use SoftDeletingTrait;

    private $_taskIds = ['project-assigned', 'project-first-delivery', 'project-updated-delivery'];

    protected $connection = 'pw2';
    protected $table      = 'projects';

    protected $dates = [
      'created_at', 'deleted_at', 'updated_at',
      'bid_date', 'secured_date', 'expected_start_date',
      'contracts.date_submitted', 'contracts.date_issued'
    ];

    protected $fillable = [
        // Details
        'project_id', 'project_name', 'address', 'city', 'province', 'branch', 'project_status',
        'secured_date', 'contract_manager', 'site_coordinator', 'expected_start_date',

        // Estimating
        'project_type', 'estimator', 'bid_status', 'bid_date', 'bid_price', 'discount_rate',
        'sqft_price', 'first_delivery_quarter', 'first_delivery_year',

        // Objects
        'customers', 'documents', 'products', 'tasks',

        // Stats
        'created_by', 'deleted_by', 'updated_by'
    ];

    function __construct() {
      parent::__construct();

      foreach($this->_taskIds as $taskId)
      {
        $this->dates[] = "tasks.{$taskId}.from";
        $this->dates[] = "tasks.{$taskId}.to";
      }
    }

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

    public function changeOrders()
    {
        return $this->hasMany('ProjectsWeb\ChangeOrder', 'project_id');
    }
}
