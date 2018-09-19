<?php namespace ProcessingWeb;

class GlassFiles {

  public static function all()
  {
    $redis = \Redis::connection();
    return $redis->get('glass-files');
  }

}
