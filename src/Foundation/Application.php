<?php


namespace Dreamscape\Foundation;


use Dreamscape\Container\Container;
use Dreamscape\Contracts\Foundation\Application as ApplicationContract;

class Application extends Container implements ApplicationContract
{

    const VERSION = '0.1';

    /* @var string */
    protected $basePath;

    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }
        $this->registerBaseBindings();
    }

    public function version()
    {
        return static::VERSION;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
        return $this;
    }

    public function basePath($path = '')
    {
        return $this->basePath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    protected function registerBaseBindings()
    {
        static::setInstance($this);
        $this->instance('app', $this);
    }

}