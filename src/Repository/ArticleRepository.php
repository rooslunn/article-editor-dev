<?php


namespace Dreamscape\Repository;


use Dreamscape\Model\Article;
use Dreamscape\Repository\Filters\ActiveArticleFilter;
use Dreamscape\Repository\Filters\ArticleIdArticleFilter;
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

    private function queryShort()
    {
        $query = '
            SELECT article.article_id, article.article_url, article.article_title, 
                   article.date_scanned, article.date_published,
                   article.date_updated, article.status_id, 
                   generic_status.status_name as status,
                   generic_status.status_color
            FROM article article
                LEFT JOIN generic_status generic_status ON article.status_id = generic_status.status_id
                LEFT JOIN article_sections article_sections USING (section_id)
        ';
        return $query;
    }
    
    private function queryFull()
    {
        return '
            SELECT article.article_id, article.file_id, article.article_url, article.article_title, article.article_description,
                   article.article_tags, article.section_id, article.article_content, article.weight, article.status_id, 
                   article.date_scanned, gs.status_name,
                   article.date_published, article.date_updated, article.doc_type, 
                   asec.section_title
            FROM article article
                INNER JOIN generic_status gs USING(status_id)
                LEFT JOIN article_sections asec USING(section_id)
        ';
    }

    public function recenltyInserted($limit = 0)
    {
        return $this->fetchAll($this->queryShort(), [
            new RecentlyInsertedArticleFilter(),
        ], $limit);
    }

    public function recenltyUpdated($limit = 0)
    {
        return $this->fetchAll($this->queryShort(), [
            new RecentlyUpdatedArticleFilter(),
        ], $limit);
    }

    public function articleId($article_id)
    {
        return  $this->fetch($this->queryFull(), [
            new ArticleIdArticleFilter($article_id)
        ]);
    }

    public function findOrNew($article_id)
    {
        $data = [];
        
        if ((int) $article_id !== 0) {
            $data = $this->articleId($article_id);
        }

        return Article::create($data);
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

        return $this->fetchAll($this->queryShort(), $query_filters);
    }

}