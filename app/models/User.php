<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Cartalyst\Sentry\Users\Eloquent\User as SentryUser;

class User extends SentryUser implements UserInterface, RemindableInterface {

    use UserTrait, RemindableTrait;

    protected   $connection = 'auth';
    protected   $table      = 'users';
    protected   $hidden     = ['password', 'remember_token', 'activation_code', 'persist_code', 'reset_password_code'];
    protected   $dates      = ['created_at', 'updated_at', 'activated_at', 'last_login'];
    public      $timestamps = true;

}
