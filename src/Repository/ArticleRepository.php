<?php


namespace Dreamscape\Repository;


use Dreamscape\Repository\Filters\ActiveArticleFilter;
use Dreamscape\Repository\Filters\RecentlyInsertedArticleFilter;
use Dreamscape\Repository\Filters\RecentlyUpdatedArticleFilter;

final class ArticleRepository extends Repository
{
    use WithArticleStatuses;
    
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
        return $this->get($this->queryAll(), [
            new ActiveArticleFilter($this->articleStatusId('delete')),
            new RecentlyInsertedArticleFilter(),
        ], $limit);
    }

    public function recenltyUpdated($limit = 0)
    {
        return $this->get($this->queryAll(), [
            new ActiveArticleFilter($this->articleStatusId('delete')),
            new RecentlyUpdatedArticleFilter(),
        ], $limit);
    }
}