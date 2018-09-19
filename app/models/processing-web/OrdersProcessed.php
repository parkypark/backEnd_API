<?php namespace ProcessingWeb;

use Jenssegers\Mongodb\Model;

class OrdersProcessed extends Model {

    protected   $connection = 'cache';
    protected   $table      = 'processedOrders';
    //protected   $dates      = ['dateentered', 'dateupdated', 'dateprocessed', 'deliverydate', 'onsite_deliverydate', 'timeprocessed'];
    public      $timestamps = false;

}
