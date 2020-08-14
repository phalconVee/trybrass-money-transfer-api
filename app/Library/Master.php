<?php

namespace App\Library;

use App\Traits\GuzzleRequestTrait;

use App\Traits\RequestTrait;
use App\Traits\ResponseTrait;

class Master
{
    use RequestTrait,
        ResponseTrait,
        GuzzleRequestTrait;

    protected static $_instance = null;

    /**
     * Prevent direct object creation
     */
    final private function  __construct() { }

    /**
     * Prevent object cloning
     */
    final private function  __clone() { }

    /**
     * Returns new or existing Singleton instance
     * @return Master
     */
    final public static function getInstance()
    {
        if (null !== static::$_instance) {
            return static::$_instance;
        }
        static::$_instance = new static();
        return static::$_instance;
    }

    public static function hasDebug()
    {
        return env('APP_DEBUG', false);
    }
}
