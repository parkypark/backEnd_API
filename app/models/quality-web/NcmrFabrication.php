<?php

use Jenssegers\Mongodb\Model as Eloquent;

class NcmrFabrication extends Eloquent {

	protected $connection = 'quality';
	protected $collection = 'ncmr_fabrication';
	public $timestamps = false;

}
