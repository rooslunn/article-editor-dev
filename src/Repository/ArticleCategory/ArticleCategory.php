<?php


namespace Dreamscape\Repository\ArticleCategory;


abstract class ArticleCategory
{
    protected $alias = 'article';

    protected function aliased($field_name)
    {
        return "{$this->alias}.$field_name";
    }

    protected function where_clause()
    {
        if (func_num_args() > 0) {
            return implode(' ', func_get_args());
        }
        return '';
    }
}