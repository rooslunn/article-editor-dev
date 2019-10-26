<?php


namespace Dreamscape\Repository;


final class ArticleStatusRepository extends Repository
{
    private function queryAll()
    {
        $query = 'select lcase(status_name) as status_name, status_id, status_color from generic_status';
        return $query;
    }

    public function getAll()
    {
        return $this->fetchAll($this->queryAll());
    }
}