<?php


namespace Dreamscape\Repository;


use PDO;

trait WithArticleStatuses
{
    private $statuses = [];

    public function articleStatusAll()
    {
        if (! empty($this->statuses)) {
            return $this->statuses;
        }
        $statuses =  $this->db()->query('
                select lcase(status_name), status_id
                from generic_status')
            ->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);

        $this->statuses = array_map('reset', $statuses);

        return $this->statuses;
    }

    public function articleStatusId($name)
    {
        $this->articleStatusAll();
        $name = strtolower($name);
        return array_key_exists($name, $this->statuses)
            ? $this->statuses[$name]
            : null;
    }
}