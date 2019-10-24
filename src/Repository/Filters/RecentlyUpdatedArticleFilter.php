<?php


namespace Dreamscape\Repository\Filters;


class RecentlyUpdatedArticleFilter extends QueryFilter implements QueryFilter
{

    public function where()
    {
        return $this->where_clause($this->aliased('date_updated'), '>=',  '(CURRENT_DATE - INTERVAL 1 MONTH)');
    }

    public function orderBy()
    {
        return "{$this->aliased('date_updated')} desc";
    }
}