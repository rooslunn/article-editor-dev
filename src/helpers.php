<?php

use Dreamscape\Container\Container;

if (! function_exists('app')) {
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
        return array_map(static function($item) use ($key) {
            return is_object($item) ? $item->$key : $item[$key];
        }, $items);
    }
}

if (! function_exists('array_flatten')) {
    function array_flatten(array $items) {
        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } else {
                array_push($result, ...array_values($item));
            }
        }

        return $result;
    }
}

if (! function_exists('parenthesised')) {
    function parenthesised($str) {
        return '(' . $str . ')';
    }
}

if (! function_exists('view')) {
    function view($template, array $data) {
        return app('twig')->render($template.'.twig', $data);
    }
}

if (! function_exists('display')) {
    function display($template, array $data) {
        echo view($template, $data);
    }
}

if (! function_exists('string_studly')) {
    function string_studly($str) {
        $studly = ucwords(str_replace(['-', '_'], ' ', $str));
        return str_replace(' ', '', $studly);
    }
}

if (! function_exists('string_camel')) {
    function string_camel($str) {
        return lcfisrt(srting_studly($str));
    }
}

