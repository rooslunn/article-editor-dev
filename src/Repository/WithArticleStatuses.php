<?php


namespace Dreamscape\Repository;


trait WithArticleStatuses
{
    private $statuses = [];

    public function articleStatusAll()
    {
        if (! empty($this->statuses)) {
            return $this->statuses;
        }
        $this->statuses =  $this->db()
            ->query('
                select lcase(status_name), status_id
                from generic_status')
            ->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);

        return $this->statuses;
    }

    public function articleStatusId($name)
    {
        $name = strtolower($name);
        return array_key_exists($name, $this->statuses)
            ? $this->statuses[$name]
            : null;
    }
}