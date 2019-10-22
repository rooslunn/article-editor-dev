<?php


namespace Dreamscape\Foundation;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

final class Kernel
{
    private static $instance;

    public static function getInstance(RouteCollection $routes)
    {
        if (is_null(static::$instance)) {
            static::$instance = static::createInstance($routes);
        }

        return static::$instance;
    }

    private static function createInstance(RouteCollection $routes)
    {
        $matcher = new UrlMatcher($routes, new RequestContext());
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));
        $kernel = new HttpKernel(
            $dispatcher,
            new ControllerResolver(),
            new RequestStack(),
            new ArgumentResolver()
        );
        return $kernel;
    }
}