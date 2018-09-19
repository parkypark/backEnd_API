<?php

/*
 * Material data comes from quality-robot (node service running on archdb2). It is processed and stored in the local
 * redis cache as a compressed json string.
 *
 */

class Materials {

	public static function all()
	{
		$redis = Redis::connection();
		return $redis->get('quality.materials');
	}

}