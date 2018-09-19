<?php

class Order extends Eloquent {

    protected $connection = 'work-orders';

    protected $table = 'orders';

    public $timestamps = false;

    public function frames()
    {
        return $this->hasMany('FrameProduct', 'ordernumber', 'ordernumber');
    }

}