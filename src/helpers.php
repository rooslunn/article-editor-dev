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

if (! function_exists('array_pluck')) {
    function array_pluck($items, $key) {
        return array_map( function($item) use ($key) {
            return is_object($item) ? $item->$key : $item[$key];
        }, $items);
    }
}

if (! function_exists('parenthesised')) {
    function parenthesised($str) {
        return '(' . $str . ')';
    }
}

if (! function_exists('display')) {
    function display($template, array $data) {
        echo app('twig')->render($template.'.twig', $data);
    }
}

if (! function_exists('string_studly')) {
    function srting_studly($str) {
        return ucwords(str_replace(['-', '_'], ' ', $str));
    }
}

if (! function_exists('string_camel')) {
    function string_camel($str) {
        return lcfisrt(srting_studly($str));
    }
}

