<?php


namespace Dreamscape\Repository\Filters;


use Dreamscape\Contracts\Database\QueryFilter as QueryFilterContract;

final class ArticleIdArticleFilter extends ArticleFilter implements QueryFilterContract
{
    protected $article_id;

    public function __construct($article_id)
    {
        $this->article_id = $article_id;
    }

    public function where()
    {
        return $this->where_clause($this->aliased('article_id'), '=', (int) $this->article_id);
    }

    public function orderBy()
    {
        return null;
    }
}