<?php


namespace Dreamscape\Repository\Filters;


use Dreamscape\Contracts\Database\QueryFilter as QueryFilterContract;

final class StatusIdArticleFilter extends ArticleFilter implements QueryFilterContract
{

    private $status_id;

    public function __construct($status_id)
    {
        $this->status_id = (int) $status_id;
    }

    public function where()
    {
        return $this->where_clause($this->aliased('status_id'), '=', $this->status_id);
    }

    public function orderBy()
    {
        return "IF({$this->aliased('date_updated')} != '0000-00-00 00:00:00', 
            {$this->aliased('date_updated')}, {$this->aliased('date_scanned')}) desc";
    }
}