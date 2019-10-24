<?php


namespace Dreamscape\Contracts\Database;


interface QueryFilter
{
    public function where();
    public function orderBy();
}