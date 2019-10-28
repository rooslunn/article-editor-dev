<?php


namespace Dreamscape\Repository;


use PDO;

class ArticleToSectionRepository extends Repository
{
    private function queryOnArticle($article_id)
    {
        $article_id = (int) $article_id;
        return "
            SELECT section_id, article_id
            FROM article_to_section
            WHERE article_id = {$article_id}
         ";
    }
    public function belongsToArtcile($article_id)
    {
        if ((int) $article_id === 0) {
            return [];
        }
        return $this->fetchAll($this->queryOnArticle($article_id), $filters = [], $limit = 0, $offset = 0, PDO::FETCH_GROUP);
    }
}