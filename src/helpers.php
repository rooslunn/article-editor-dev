<?php

use Dreamscape\Container\Container;

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string $abstract
     * @return mixed|Dreamscape\Foundation\Application
     */
    function app($abstract = null)
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract);
    }
}

