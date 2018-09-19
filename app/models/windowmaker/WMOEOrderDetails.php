<?php

class WMOEOrderDetails extends Eloquent {

    protected $connection = 'oe';

    protected $table = 'order_details';

    public $timestamps = false;

}