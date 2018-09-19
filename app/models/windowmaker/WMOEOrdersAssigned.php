<?php

class WMOEOrdersAssigned extends Eloquent {

    protected $connection = 'oe';

    protected $table = 'orders_assigned';

    public $timestamps = false;

}