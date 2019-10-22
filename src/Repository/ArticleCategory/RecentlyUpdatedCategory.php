<?php


namespace Dreamscape\Repository\ArticleCategory;


class RecentlyUpdatedCategory extends ArticleCategory implements ArticleCategoryContract
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