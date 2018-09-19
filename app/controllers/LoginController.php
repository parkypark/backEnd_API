<?php

use StarlineWindows\StarlineLdap;

class LoginController extends \BaseController {

    protected $rules = array(
        'username' => 'required|min:4|max:50',
        'password' => 'required|min:4|max:25'
    );

    public function test()
    {
      $user_info = StarlineLdap::authenticate('webtest', 'Starline3#');
      $user_info['username'] = 'webtest';
      $user_info['password'] = 'Starline3#';
      $this->fetchAndUpdateUserModel($user_info);
      $user = Sentry::findUserByLogin('webtest');
      $permissions = $user->getMergedPermissions();
      return Response::prettyjson([
        'info' => $user_info,
        'user' => $user,
        'permissions' => $permissions
      ]);
    }

    public function showLogin()
    {
        $redirect_uri = Input::get('redirect_uri', '/apps');

        if (Sentry::check())
        {
            return Redirect::to($redirect_uri)
                ->withToken(JWTAuth::fromUser(Sentry::getUser()));
        }

        return View::make('sessions/login', [
            'redirectUri' => Input::get('redirect_uri')
        ]);
    }

    public function postAccessToken()
    {
        // Validate inputs
        $validator = Validator::make(Input::all(), $this->rules);
        if ($validator->fails())
        {
            return \Response::json([
                'success'  => false,
                'messages' => $validator->messages()
            ], 400);
        }

        $username = Input::get('username');
        $password = Input::get('password');

        // Attempt authentication via LDAP
        $user_info = StarlineLdap::authenticate($username, $password);
        if ($user_info === false)
        {
            return \Response::json([
                'success' => false,
                'messages' => [
                    [
                        'type'    => 'danger',
                        'message' => 'Invalid credentials'
                    ]
                ]
            ], 400);
        }

        // Add credentials to info
        $user_info['username'] = $username;
        $user_info['password'] = $password;

        // Get sentry user model and update with user info from LDAP
        $user = $this->fetchAndUpdateUserModel($user_info);

        // Generate and return jwt token
        $token = JWTAuth::fromUser($user);
        return Response::json(compact('token'));
    }

    public function postRefreshAccessToken()
    {
        try
        {
            $token = JWTAuth::parseToken()->refresh();
            return Response::json(compact('token'));
        }
        catch (Exception $e)
        {
            $error = $e->getMessage();
            return Response::json(compact('error'), 400);
        }
    }

    public function postLogin()
    {
        // Validate inputs
        $validator = Validator::make(Input::all(), $this->rules);
        if ($validator->fails())
        {
            return Redirect::route('login.show')
                ->withErrors($validator)
                ->withInput(Input::except('password'));
        }

        $username = Input::get('username');
        $password = Input::get('password');
        $redirect_uri = Input::get('redirect_uri', '/apps');

        // Attempt authentication via LDAP
        $user_info = StarlineLdap::authenticate($username, $password);
        if ($user_info === false)
        {
            return Redirect::route('login.show')
                ->withMessage('Invalid credentials')
                ->withInput(Input::except('password'));
        }

        // Add credentials to info
        $user_info['username'] = $username;
        $user_info['password'] = $password;

        // Get sentry user model and update with user info from LDAP
        $user = $this->fetchAndUpdateUserModel($user_info);

        // Log user into sentry
        if (Input::get('rememberMe'))
        {
            Sentry::loginAndRemember($user);
        }
        else
        {
            Sentry::login($user);
        }

        // Redirect to intended page
        return Redirect::to($redirect_uri);
    }

    public function logout()
    {
        $redirect_uri = Input::get('redirect_uri');
        Sentry::logout();
        Session::flush();
        return Redirect::route('login.show', ['redirect_uri' => $redirect_uri]);
    }

    public function showUser()
    {
        $user = JWTAuth::parseToken()->toUser();
        $user->permissions = $user->getMergedPermissions();
        return Response::prettyjson($user);
    }

    public function showAuthorizations()
    {
        $user = JWTAuth::parseToken()->toUser();
        return Response::prettyjson($user->getMergedPermissions());
    }

    public function showGroupMembership()
    {
        $user = JWTAuth::parseToken()->toUser();
        return Response::prettyjson($user->member_of);
    }

    private function fetchAndUpdateUserModel($user_info)
    {
        // Create user if required otherwise update everything but the username
        try
        {
            $user = Sentry::createUser(array(
                'username'  => $user_info['username'],
                'password'  => $user_info['password'],
                'full_name' => $user_info['full_name'],
                'email'     => $user_info['email'],
                'member_of' => implode(',', $user_info['member_of']),
                'activated' => true
            ));
        }
        catch (Cartalyst\Sentry\Users\UserExistsException $e)
        {
            $user = Sentry::findUserByLogin($user_info['username']);
            $user->password = $user_info['password'];
            $user->full_name = $user_info['full_name'];
            $user->member_of = implode(',', $user_info['member_of']);
            $user->email = $user_info['email'];
        }

        // Add/update groups and membership
        foreach($user_info['member_of'] as $group_name)
        {
            try
            {
                $group = Sentry::findGroupByName($group_name);
            }
            catch (Cartalyst\Sentry\Groups\GroupNotFoundException $e)
            {
                $group = Sentry::createGroup([
                    'name'              => $group_name,
                    'permissions'       => [],
                    'is_domain_group'   => 1
                ]);
            }

            $user->addGroup($group);
        }

        // Update last login
        $user->last_login = date('Y-m-d H:i:s');

        if (! $user->save())
        {
            Log::error('Failed to save/update user: ' . $user_info['username']);
        }

        return $user;
    }

}
