<?php


namespace Dreamscape\Repository\ArticleCategory;


class RecentlyInsertedCategory extends ArticleCategory implements ArticleCategoryContract
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