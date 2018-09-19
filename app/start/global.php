<?php

ini_set('memory_limit', '2048M');

/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/

ClassLoader::addDirectories(array(

	app_path().'/commands',
	app_path().'/controllers',
	app_path().'/models',
	app_path().'/database/seeds',
	app_path().'/StarlineWindows',
	app_path().'/StarlineWindows/Traits'
));

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a basic log file setup which creates a single file for logs.
|
*/

Log::useDailyFiles(storage_path().'/logs/laravel.log');

// Log queries!
#require_once app_path('query_logger.php');


/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/

App::error(function(Exception $exception, $code)
{
	Log::error($exception);
});

App::error(function(Tymon\JWTAuth\Exceptions\JWTException $e, $code)
{
	if ($e instanceof Tymon\JWTAuth\Exceptions\TokenExpiredException) {
		return Response::json(['token_expired'], $e->getStatusCode());
	} else if ($e instanceof Tymon\JWTAuth\Exceptions\TokenInvalidException) {
		return Response::json(['token_invalid'], $e->getStatusCode());
	}
});

/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenance mode is in effect for the application.
|
*/

App::down(function()
{
	return Response::make("Be right back!", 503);
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path().'/filters.php';

/*
|--------------------------------------------------------------------------
| Blade extensions
|--------------------------------------------------------------------------
*/

Blade::extend(function($view, $compiler)
{
  $pattern = $compiler->createMatcher('numberToWords');
	return preg_replace($pattern, '$1<?php echo (new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($2); ?>', $view);
});
