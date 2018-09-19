<?php namespace ProjectsWeb;

use BaseController, Exception, Input, Response, Sentry, User, Validator;

class AuthorizationsController extends BaseController {

    private $columns = ['member_of', 'permissions'];

    private $rules = [
        'member_of' => 'required',
        'permissions' => 'required'
    ];

    public function index()
    {
        return Response::prettyjson(Sentry::findAllUsers());
    }

    public function show($id)
    {
        $user = Sentry::findUserById($id);
        $user->merged_permissions = $user->getMergedPermissions();
        return Response::prettyjson($user);
    }

    // NOTE: Used for create and update
    public function store()
    {
        try
        {
            $data = Input::only(['username', 'role', 'permissions']);

            $permissions = [];
            foreach ($data['permissions'] as $page => $actions)
            {
                foreach ($actions as $action => $value)
                {
                    $permissions[join('.', ['projects', $page, $action])] = $value ? 1 : -1;
                }
            }

            $user = User::where('username', $data['username'])->get()->first();
            if ($user)
            {
                $user->permissions = $permissions;
                $user->save();
            }
            else
            {
                $user = Sentry::createUser([
                    'username'      => $data['username'],
                    'password'      => base64_encode(openssl_random_pseudo_bytes(30)),
                    'activated'     => true,
                    'permissions'   => $permissions
                ]);
            }

            $role = UserRoles::where('username', $data['username'])->get()->first();
            if ($role)
            {
                $role->role = $data['role'];
                $role->save();
            }
            else
            {
                $role = UserRoles::create([
                    'username'      => $data['username'],
                    'role'          => $data['role']
                ]);
            }

            $user->role = $role;
            return Response::prettyjson($user);
        }
        catch (Exception $ex)
        {
            return Response::prettyjson(['error' => $ex->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try
        {
            $user = Sentry::findUserById($id);
            if (! $user)
            {
                return Response::prettyjson(['error' => 'User does not exist'], 400);
            }

            $role = UserRoles::where('username', $user->username)->get()->first();
            if ($role)
            {
                $role->delete();
            }

            $user->delete();
            return Response::json(true);
        }
        catch (Exception $e)
        {
            return Response::prettyjson(['error' => $e->getMessage()], 500);
        }
    }

    public function getRole()
    {
        $username = Input::get('username');
        $result = UserRoles::where('username', $username)->get(['role'])->first();

        return Response::prettyjson([
            'role' => $result ? $result->role : null
        ]);
    }

}