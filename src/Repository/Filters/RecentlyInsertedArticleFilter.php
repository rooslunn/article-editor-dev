<?php


namespace Dreamscape\Repository\Filters;


use Dreamscape\Contracts\Database\QueryFilter as QueryFilterContract;
use Dreamscape\Database\QueryFilter;

class RecentlyInsertedArticleFilter extends QueryFilter implements QueryFilterContract
{

    public function where()
    {
        return $this->where_clause($this->aliased('date_scanned'), '>=',  '(CURRENT_DATE - INTERVAL 1 MONTH)');
    }

    public function orderBy()
    {
        return "{$this->aliased('date_scanned')} desc";
    }
}