<?php


namespace Dreamscape\Repository;


use Dreamscape\Repository\Filters\ActiveArticleFilter;
use Dreamscape\Repository\Filters\RecentlyInsertedArticleFilter;
use Dreamscape\Repository\Filters\RecentlyUpdatedArticleFilter;

final class ArticleRepository extends Repository
{
    use WithArticleStatuses;

    const FILTERS = ['section_id', 'article_id', 'status_id'];

    protected function globalFilters()
    {
        return [
            new ActiveArticleFilter($this->articleStatusId('delete')),
        ];
    }

    private function queryAll()
    {
        $query = '
            SELECT article.article_id, article.article_url, article.article_title, 
                   article.date_scanned, article.date_published,
                   article.date_updated, article.status_id, 
                   generic_status.status_name as status
            FROM article article
                LEFT JOIN generic_status generic_status ON article.status_id = generic_status.status_id
                LEFT JOIN article_sections article_sections USING (section_id)
        ';
        return $query;
    }

    public function recenltyInserted($limit = 0)
    {
        return $this->fetchAll($this->queryAll(), [
            new RecentlyInsertedArticleFilter(),
        ], $limit);
    }

    public function recenltyUpdated($limit = 0)
    {
        return $this->fetchAll($this->queryAll(), [
            new RecentlyUpdatedArticleFilter(),
        ], $limit);
    }

    public function filterBy(array $filters)
    {
        $query_filters = [];

        foreach (array_keys($filters) as $filter_name) {
            $filter_class = $this->toFilterClass($filter_name, 'ArticleFilter');
            if (class_exists($filter_class)) {
                $filter_param = $filters[$filter_name];
                $query_filters[] = (new $filter_class($filter_param));
            }
        }

        return [];
//        return $this->fetchAll($this->queryAll(), $query_filters);
    }
}