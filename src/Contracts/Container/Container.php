<?php

namespace Dreamscape\Contracts\Container;


interface Container
{
    public function instance($abstract, $instance);
}
