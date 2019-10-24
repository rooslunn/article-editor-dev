<?php


namespace Dreamscape\Database;


abstract class QueryFilter
{
    protected $alias;

    protected function aliased($field_name)
    {
        if ($this->alias) {
            return "{$this->alias}.$field_name";
        }
        return $field_name;
    }

    protected function where_clause()
    {
        if (func_num_args() > 0) {
            return implode(' ', func_get_args());
        }
        return '';
    }
}