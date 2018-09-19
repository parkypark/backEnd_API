<?php namespace ProjectsWeb;

use BaseController, DB, Input, Response, JWTAuth;

class HistoryController extends BaseController {

    public function __construct()
    {
        $what = 'history';
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.read", ['on' => 'get']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.create", ['on' => 'post']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.update", ['on' => 'put']);
        $this->beforeFilter("jwt-auth.hasAccess:projects.{$what}.delete", ['on' => 'delete']);
    }

    public function index()
    {
        $tableState = json_decode(Input::get('tableState', null));
        $user = JWTAuth::parseToken()->toUser();

        $is_admin = false;
        if (! is_null($user->member_of))
        {
            $groups = explode(',', $user->member_of);
            $is_admin = in_array('Managers', $groups);
        }

        if ($tableState !== null)
        {
            $start = isset($tableState->pagination->start)
                ? $tableState->pagination->start
                : 0;

            $number = isset($tableState->pagination->number)
                ? $tableState->pagination->number
                : 10;

            if (isset($tableState->sort->predicate)) {
                $order_by = $tableState->sort->predicate;
                $order_dir = $tableState->sort->reverse ? 'desc' : 'asc';
            } else {
                $order_by = 'updated_at';
                $order_dir = 'desc';
            }

            $where = function($query) use ($tableState, $user, $is_admin)
            {
                if (! $is_admin)
                {
                    $query->where('updated_by', $user->full_name);
                }

                if (! (isset($tableState->search) && isset($tableState->search->predicateObject)))
                {
                    return;
                }

                foreach ($tableState->search->predicateObject as $key => $value)
                {
                    $query->where($key, 'like', "%{$value}%");
                }
            };
        }
        else
        {
            $order_by = 'updated_at';
            $order_dir = 'desc';
            $start = 0;
            $number = 100;

            $where = function($query) use ($user, $is_admin)
            {
                if (! $is_admin)
                {
                    $query->where('updated_by', $user->full_name);
                }
            };
        }

        $history = Project::select('updated_at', 'updated_by', 'project_id', 'project_name')->where($where);
        $total = $history->count();
        $data = $history
            ->orderBy($order_by, $order_dir)
            ->skip($start)
            ->take($number)
            ->get();

        return Response::prettyjson([
            'total' => $total,
            'data'  => $data,
            'user' => $user
        ]);
    }

}
