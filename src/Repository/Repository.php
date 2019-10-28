<?php
namespace Dreamscape\Repository;

use Dreamscape\Contracts\Database\Database as DatabaseContract;
use Dreamscape\Contracts\Database\QueryFilter as QueryFilterContract;
use PDO;

abstract class Repository
{
    /* @var DatabaseContract */
    private $db;

    const RECENTLY_QUERY_LIMIT = 5;

    const FILTERS_NAMESPACE = 'Dreamscape\Repository\Filters';

    public function __construct(DatabaseContract $db = null)
    {
        $db = $db ?: app('db');
        $this->db = $db;
    }

    public function db()
    {
        return $this->db;
    }

    protected function globalFilters()
    {
        return [];
    }

    protected function applyFilters($query, array $filters)
    {
        $where = [];
        $order_by = [];

        array_unshift($filters, $this->globalFilters());
        $filters = array_flatten($filters);

        foreach ($filters as $filter) {
            if ($filter instanceof QueryFilterContract) {
                if ($str = $filter->where()) {
                    $where[] = parenthesised($str);
                }
                if ($str = $filter->orderBy()) {
                    $order_by[] = $str;
                }
            }
        }

        $where_clause =  implode(' and ', $where);
        $order_by_clause = implode(', ', $order_by);

        if (! empty($where_clause)) {
            $query = "{$query} \n where {$where_clause}";
        }
        if (! empty($order_by_clause)) {
            $query = "{$query} \n order by {$order_by_clause}";
        }

        return $query;
    }

    protected function applyLimit($query, $limit = 0)
    {
        if ($limit > 0) {
            return "{$query} limit $limit";
        }

        return $query;
    }

    protected function applyOffset($query, $offset)
    {
        if ($offset > 0) {
            return "{$query} offset {$offset}";
        }

        return $query;
    }

    protected function queryPrepareForPage(&$query, $filters = [], $page, $per_page = 15)
    {
        $offset = ($page - 1) * $per_page;
        $limit = $per_page;

        $this->queryPrepare($query, $filters, $limit, $offset);
    }

    protected function queryPrepare(&$query, array $filters = [], $limit = 0, $offset = 0)
    {
        $query = trim($query);
        $query = $this->applyFilters($query, $filters);
        $query = $this->applyLimit($query, $limit);
        $query = $this->applyOffset($query, $offset);
    }

    protected function fetchAll($query, array $filters = [], $limit = 0, $offset = 0, $fetch_style = PDO::FETCH_ASSOC)
    {
        $this->queryPrepare($query, $filters, $limit, $offset);
        return $this->db()->query($query)->fetchAll($fetch_style);
    }

    protected function forPage($query, array $filters = [], $page = 1, $per_page = 15, $fetch_style = PDO::FETCH_ASSOC)
    {
        $this->queryPrepareForPage($query, $filters, $page, $per_page);
        return $this->db()->query($query)->fetchAll($fetch_style);
    }

    protected function fetch($query, array $filters = [])
    {
        $this->queryPrepare($query, $filters);
        return $this->db()->query($query)->fetch();
    }

    protected function toFilterClass($filter_name, $postfix = '')
    {
        $filter_class = string_studly($filter_name) . $postfix;
        return static::FILTERS_NAMESPACE . '\\' . $filter_class;
    }
}