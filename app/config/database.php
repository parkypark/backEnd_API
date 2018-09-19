<?php

const ARCH_DB = 'archdb.starlinewindows.com';
const ARCH_DB_2 = '192.168.102.9';
const TEST_DB = '192.168.102.8';
const LOCALHOST = '192.168.102.250';
return array(

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_CLASS,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => 'auth',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => array(

        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => __DIR__.'/../database/production.sqlite',
            'prefix'   => '',
        ],

        'archdb' => [
            'driver'    => 'mysql',
            'host'      => ARCH_DB,
            'database'  => '',
            'username'  => 'wm2017',
            'password'  => '3699DEC5-504d-4b30-a95b-52a12074d2b8',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'archdb-wm' => [
            'driver'    => 'mysql',
            'host'      => ARCH_DB,
            'database'  => '',
            'username'  => 'wm2017',
            'password'  => '3699DEC5-504d-4b30-a95b-52a12074d2b8',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'archdb-admin' => [
            'driver'    => 'mysql',
            'host'      => ARCH_DB,
            'database'  => '',
            'username'  => 'wm2017',
            'password'  => '3699DEC5-504d-4b30-a95b-52a12074d2b8',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'auth' => [
            'driver'    => 'mysql',
            'host'      => ARCH_DB,
            'database'  => 'auth',
            'username'  => 'wm2017',
            'password'  => '3699DEC5-504d-4b30-a95b-52a12074d2b8',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'floor-signoffs' => [
            'driver'    => 'mysql',
            'host'      => ARCH_DB,
            'database'  => 'installation_floorsignoffs',
            'username'  => 'floorsignoffs',
            'password'  => 'bw8dnak+9',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'labour-stats' => [
            'driver'	=> 'mysql',
            'host'		=> ARCH_DB,
            'database'  => 'labour_stats',
            'username'  => 'reader',
            'password'  => 'reader',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => ''
        ],

        'oe' => [
            'driver'    => 'mysql',
            'host'      => ARCH_DB,
            'database'  => 'oe',
            'username'  => 'wm2017',
            'password'  => '3699DEC5-504d-4b30-a95b-52a12074d2b8',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'production' => [
            'driver'    => 'mysql',
            'host'      => ARCH_DB,
            'database'  => 'production',
            'username'  => 'wm2017',
            'password'  => '3699DEC5-504d-4b30-a95b-52a12074d2b8',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'production-test' => [
            'driver'    => 'mysql',
            'host'      => TEST_DB,
            'database'  => 'production',
            'username'  => 'wm2017',
            'password'  => '3699DEC5-504d-4b30-a95b-52a12074d2b8',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'production-vinyl' => [
            'driver'    => 'mysql',
            'host'      => 'mysql.starlinewindows.com',
            'database'  => 'production',
            'username'  => 'barcodescanner',
            'password'  => 'scanB@rcode',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'projects' => [
            'driver'    => 'mysql',
            'host'      => ARCH_DB,
            'database'  => 'projects',
            'username'  => 'wm2017',
            'password'  => '3699DEC5-504d-4b30-a95b-52a12074d2b8',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'projects-web' => [
            'driver'    => 'mysql',
            'host'      => ARCH_DB,
            'database'  => 'projects_web_v2',
            'username'  => 'projects-web',
            'password'  => 'correct horse battery staple',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'pw2' => [
            'driver'    => 'mongodb',
            'host'      => LOCALHOST,
            'port'      => 27017,
            'username'  => 'projects',
            'password'  => 'correct horse battery staple',
            'database'  => 'projects'
        ],

        'cache' => [
            'driver'    => 'mongodb',
            'host'      => LOCALHOST,
            'port'      => 27017,
            'username'  => 'reader',
            'password'  => 'reader',
            'database'  => 'cache'
        ],

        'quality' => [
            'driver'	=> 'mongodb',
            'host'		=> LOCALHOST,
            'port'		=> 27017,
            'username'	=> 'qa',
            'password'	=> 'barrel of monkeys',
            'database'	=> 'quality'
        ],

        'quality-web' => [
            'driver'    => 'mysql',
            'host'      => ARCH_DB,
            'database'  => 'quality_web',
            'username'  => 'quality-web',
            'password'  => '3x2ATC!O1X962g--6!l[88k:0)3$^KgD',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        'shipping' => [
            'driver'    => 'mysql',
            'host'      => TEST_DB,
            'database'  => 'production',
            'username'  => 'wm2017',
            'password'  => '3699DEC5-504d-4b30-a95b-52a12074d2b8',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => ''
        ],

        'work-orders' => [
            'driver'    => 'mysql',
            'host'      => ARCH_DB,
            'database'  => 'workorders',
            'username'  => 'wm2017',
            'password'  => '3699DEC5-504d-4b30-a95b-52a12074d2b8',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ]

    ),

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => array(

        'cluster' => false,

        'default' => array(
            'host'     => LOCALHOST,
            'port'     => 6379,
            'database' => 0,
        ),

    ),

);
