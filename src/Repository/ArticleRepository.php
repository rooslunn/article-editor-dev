<?php


namespace Dreamscape\Repository;


use Dreamscape\Repository\ArticleCategory\ActiveCategory;
use Dreamscape\Repository\ArticleCategory\ArticleCategoryContract;
use Dreamscape\Repository\ArticleCategory\RecentlyInsertedCategory;
use Dreamscape\Repository\ArticleCategory\RecentlyUpdatedCategory;

final class ArticleRepository extends Repository
{
    use WithArticleStatuses;
    
    private function listCategory(array $categories, $limit = 0)
    {
        $query = trim('
            SELECT article.article_id, article.article_url, article.article_title, 
                   article.date_scanned, article.date_published,
                   article.date_updated, article.status_id, 
                   generic_status.status_name as status
            FROM article article
                LEFT JOIN generic_status generic_status ON article.status_id = generic_status.status_id
                LEFT JOIN article_sections article_sections USING (section_id)
        ');

        $query = $this->apply($query, $categories);
        $query = $this->applyLimit($query, $limit);

        return $this->db()->query($query)->fetchAll();
    }

    private function apply($query, array $categories)
    {
        $where = [];
        $order_by = [];

        foreach ($categories as $category) {
            if ($category instanceof ArticleCategoryContract) {
                if ($str = $category->where()) {
                    $where[] = \parenthesised($str);
                }
                if ($str = $category->orderBy()) {
                    $order_by[] = $str;
                }
            }
        }

        $where_clause =  implode(' and ', $where);
        $order_by_clause = implode(', ', $order_by);

        if (! empty($where_clause)) {
            $query = "{$query} where {$where_clause}";
        }
        if (! empty($order_by_clause)) {
            $query = "{$query} order by {$order_by_clause}";
        }

        return $query;
    }

    private function applyLimit($query, $limit)
    {
        if ($limit > 0) {
            return "{$query} limit $limit";
        }

        return $query;
    }

    public function recenltyInserted($limit = 0)
    {
        return $this->listCategory([
            new ActiveCategory($this->articleStatusId('delete')),
            new RecentlyInsertedCategory(),
        ], $limit);
    }

    public function recenltyUpdated($limit = 0)
    {
        return $this->listCategory([
            new ActiveCategory($this->articleStatusId('delete')),
            new RecentlyUpdatedCategory(),
        ], $limit);
    }
}