@extends('layouts/default'))

{{-- Web site Title --}}
@section('title')
Log In
@stop

{{-- Content --}}
@section('content')
<div class="row">
    <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">

        @if ($message = Session::get('message'))
            <div class="row">
                <div class="col-xs-12">
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>Login Failed:</strong> {{ $message }}
                    </div>
                </div>
            </div>
        @endif

        <div class="well well-lg">

            <div class="row visible-xs">
                <div class="col-xs-12 text-center">
                    <img alt="Starline Architectural"
                         class="logo"
                         src="http://projects.starlinewindows.com/apps/static/api/img/staralum_logo.svg">
                </div>

                <div class="col-xs-12">
                    {{ Form::open(array('action' => 'LoginController@postLogin')) }}
                        {{ Form::hidden('redirect_uri', $redirectUri) }}

                        <h2 class="form-signin-heading">
                            <i class="glyphicon glyphicon-log-in"></i> Log In
                        </h2>

                        <div class="form-group {{ ($errors->has('username')) ? 'has-error' : '' }}">
                            {{ Form::text('username', null, array('class' => 'form-control', 'placeholder' => 'Username', 'autofocus')) }}
                            {{ ($errors->has('username') ? $errors->first('username') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                            {{ Form::password('password', array('class' => 'form-control', 'placeholder' => 'Password'))}}
                            {{ ($errors->has('password') ?  $errors->first('password') : '') }}
                        </div>

                        <label class="checkbox">
                            {{ Form::checkbox('rememberMe', 'rememberMe') }} Remember me
                        </label>

                        {{ Form::submit('Log In', array('class' => 'btn btn-primary'))}}
                    {{ Form::close() }}
                </div>
            </div>

            <table class="login hidden-xs">

                <tbody>
                <tr>
                    <td class="col-xs-4 text-center">
                        <img alt="Starline Architectural"
                             class="logo"
                             src="http://projects.starlinewindows.com/apps/static/api/img/staralum_logo.svg">
                    </td>

                    <td class="col-xs-8">
                        {{ Form::open(array('action' => 'LoginController@postLogin')) }}
                            {{ Form::hidden('redirect_uri', $redirectUri) }}

                            <h2 class="form-signin-heading">
                                <i class="glyphicon glyphicon-log-in"></i> Log In
                            </h2>

                            <div class="form-group {{ ($errors->has('username')) ? 'has-error' : '' }}">
                                {{ Form::text('username', null, array('class' => 'form-control', 'placeholder' => 'Username', 'autofocus')) }}
                                {{ ($errors->has('username') ? $errors->first('username') : '') }}
                            </div>

                            <div class="form-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                                {{ Form::password('password', array('class' => 'form-control', 'placeholder' => 'Password'))}}
                                {{ ($errors->has('password') ?  $errors->first('password') : '') }}
                            </div>

                            <label class="checkbox">
                                {{ Form::checkbox('rememberMe', 'rememberMe') }} Remember me
                            </label>

                            {{ Form::submit('Log In', array('class' => 'btn btn-primary'))}}
                        {{ Form::close() }}
                    </td>
                </tr>
                </tbody>

            </table>
        </div>

    </div>
</div>

@stop