<?php

use Jenssegers\Mongodb\Model as Eloquent;

class NcmrInternal extends Eloquent {

	protected $connection = 'quality';
	protected $collection = 'ncmr_internal';
	public $timestamps = false;

}