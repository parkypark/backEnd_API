<?php

class ScanLog extends Eloquent {

    protected $connection = 'production';

    protected $table = 'scanlogs';

    public $timestamps = false;

}