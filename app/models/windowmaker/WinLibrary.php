<?php

class WinLibrary extends Eloquent {

    protected $connection = 'archdb';

    protected $table = 'projects.win_library';

    public $timestamps = false;

}