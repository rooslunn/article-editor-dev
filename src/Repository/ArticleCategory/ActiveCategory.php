<?php


namespace Dreamscape\Repository\ArticleCategory;


class ActiveCategory extends ArticleCategory implements ArticleCategoryContract
{
    protected $status_deleted;

    public function __construct($status_deleted)
    {
        $this->status_deleted = $status_deleted;
    }

    public function where()
    {
        return $this->where_clause($this->aliased('status_id'), '!=', $this->status_deleted);
    }

    public function orderBy()
    {
        return null;
    }
}