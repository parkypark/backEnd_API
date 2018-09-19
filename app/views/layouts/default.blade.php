<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<title> 
			@section('title') 
			@show 
		</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<?php $static_host = 'http://projects.starlinewindows.com/apps/static/api'  ?>
		<link rel="stylesheet" href="{{ $static_host }}/bower_components/bootstrap/dist/css/bootstrap.min.css">
		<link rel="stylesheet" href="{{ $static_host }}/bower_components/bootstrap/dist/css/bootstrap-theme.min.css">

		<style>
			@section('styles')
				body {
					padding: 70px 0 0 0;
					background-color: #025;
				}
				table.login {
					width: 100%
				}
			@show
		</style>

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

	
	</head>

	<body>
		

		<!-- Navbar -->
		<div class="navbar navbar-default navbar-fixed-top">
	      <div class="container">
	        <div class="navbar-header">
	          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	          </button>
	          <a class="navbar-brand" href="/apps">Apps</a>
	        </div>
	        <div class="collapse navbar-collapse">

	          <ul class="nav navbar-nav navbar-right">
	            @if (Sentry::check())
				<li {{ (Request::is('users/show/' . Session::get('userId')) ? 'class="active"' : '') }}><a href="{{ URL::route('users.show', Session::get('userId')) }}">{{ \Sentry::getUser()->full_name }}</a></li>
				<li><a href="{{ URL::route('logout') }}">Logout</a></li>
				@else
				<li {{ (Request::is('login.show') ? 'class="active"' : '') }}><a href="{{ URL::route('login.show') }}">Login</a></li>
				@endif
	          </ul>
	        </div><!--/.nav-collapse -->
	      </div>
	    </div>
		<!-- ./ navbar -->

		<!-- Container -->
		<div class="container">
			<!-- Notifications -->
			@include('layouts/notifications')
			<!-- ./ notifications -->

			<!-- Content -->
			@yield('content')
			<!-- ./ content -->
		</div>

		<!-- ./ container -->

		<!-- Javascripts
		================================================== -->
		<script src="{{ $static_host }}/bower_components/jquery/dist/jquery.min.js"></script>
		<script src="{{ $static_host }}//bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
	</body>
</html>
