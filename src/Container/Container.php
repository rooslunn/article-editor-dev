<?php


namespace Dreamscape\Container;

use Dreamscape\Container\Exceptions\AbstractNotInstantiated;
use Dreamscape\Contracts\Container\Container as ContainerContract;

class Container implements ContainerContract
{
    /* @var static */
    protected static $instance;

    protected $instances = [];

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public static function setInstance(ContainerContract $container = null)
    {
        return static::$instance = $container;
    }

    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
    }

    public function make($abstract)
    {
        return $this->resolve($abstract);
    }

    protected function resolve($abstract)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        throw new AbstractNotInstantiated("Container abstract '{$abstract}' not instantiated");
    }
}