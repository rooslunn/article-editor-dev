<?php


namespace Dreamscape\Repository\Filters;


use Dreamscape\Contracts\Database\QueryFilter as QueryFilterContract;

final class SectionIdArticleFilter extends ArticleFilter implements QueryFilterContract
{

    private $section_id;

    public function __construct($section_id)
    {
        $this->section_id = (int) $section_id;
    }

    public function where()
    {
        return $this->where_clause($this->aliased('section_id'), '=', $this->section_id);
    }

    public function orderBy()
    {
        return null;
    }
}