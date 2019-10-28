<?php


namespace Dreamscape\Repository;


use PDO;

class ArticleImagesRepository extends Repository
{
    private function queryOnArticle($article_id)
    {
        $article_id = (int) $article_id;
        return "
            SELECT  ai.image_id, atai.image_id, atai.article_id, ai.image_name, ai.mime_type, ai.image
            FROM image_to_article atai
                INNER JOIN article_images ai USING (image_id)
            WHERE article_id = {$article_id}
            ORDER BY ai.image_id asc
         ";
    }
    public function belongsToArtcile($article_id)
    {
        if ((int) $article_id === 0) {
            return [];
        }
        return $this->fetchAll($this->queryOnArticle($article_id), $filters = [], $limit = 0, $offset = 0, PDO::FETCH_GROUP|PDO::FETCH_UNIQUE);
    }
}