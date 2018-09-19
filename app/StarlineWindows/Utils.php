<?php namespace StarlineWindows;

class Utils {
    public static function Round16($value)
    {
        return (int)($value * 16) / 16;
    }
}