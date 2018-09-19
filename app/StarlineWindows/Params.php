<?php namespace StarlineWindows;

class Params {
    private $_kvpStore = array();

    function __construct($params)
    {
        for ($i = 0; $i < count($params); ++$i)
        {
            $parts = explode('=', $params[$i]);
            if (count($parts) < 2) continue;
            $this->_kvpStore[$parts[0]] = $parts[1];
        }
    }

    function getValue($key, $default = false)
    {
        if (! isset($this->_kvpStore[$key])) {
            return $default;
        }
        return $this->_kvpStore[$key];
    }
}