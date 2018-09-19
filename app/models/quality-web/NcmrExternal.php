<?php

use Jenssegers\Mongodb\Model as Eloquent;

class NcmrExternal extends Eloquent {

	protected $connection = 'quality';
	protected $collection = 'ncmr_external';
	public $timestamps = false;

}