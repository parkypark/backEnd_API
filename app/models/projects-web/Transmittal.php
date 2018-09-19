<?php namespace ProjectsWeb;

use Jenssegers\Mongodb\Model;

class Transmittal extends Model {

    protected   $connection = 'pw2';
    protected   $table      = 'transmittals';
    public      $timestamps = false;

}
