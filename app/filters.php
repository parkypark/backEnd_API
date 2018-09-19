<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
	//
});


App::after(function($request, $response)
{
	//
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest())
	{
		return Response::make('Unauthorized', 401);
	}
});

Route::filter('auth.basic', function()
{
	return Auth::basic('username');
});

/**
 * Sentry filter
 *
 * Checks if the user is logged in
 */
Route::filter('Sentry', function()
{
	if (! Sentry::check())
	{
		return Response::make('Unauthorized', 401);
	}
});

/**
 * hasAcces filter (permissions)
 *
 * Check if the user has permission (group/user)
 */
Route::filter('hasAccess', function($route, $request, $value)
{
	try
	{
		$user = Sentry::getUser();
		if(! $user->hasAccess($value))
		{
			return Response::make('Unauthorized', 401);
		}
	}
	catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
	{
		return Response::make('Unauthorized', 401);
	}
});

/**
 * InGroup filter
 *
 * Check if the user belongs to a group
 */
Route::filter('inGroup', function($route, $request, $value)
{
	try
	{
		$user = Sentry::getUser();
		$group = Sentry::findGroupByName($value);

		if(! $user->inGroup($group))
		{
			return Response::make('Unauthorized', 401);
		}
	}
	catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
	{
		return Response::make('Unauthorized', 401);
	}

	catch (Cartalyst\Sentry\Groups\GroupNotFoundException $e)
	{
		return Response::make('Unauthorized', 401);
	}
});

Route::filter('jwt-auth.hasAccess', function($route, $request, $scope)
{
	$user = JWTAuth::parseToken()->toUser();
	$permissions = $user->getMergedPermissions();

	// Special case for redbook report
	if ($scope === 'projects.reports.red-book')
	{
		if (! array_key_exists($scope, $permissions) || $permission[$scope] !== 1)
		{
			return Response::make('Unauthorized', 401);
		}
	}

	$app = explode('.', $scope)[0];
	$isAdmin = array_key_exists("$app.admin", $permissions) && $permissions["$app.admin"] === 1;
	if (! ($isAdmin || (array_key_exists($scope, $permissions) && $permissions[$scope] === 1)))
	{
		return Response::make('Unauthorized', 401);
	}
});


/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});
