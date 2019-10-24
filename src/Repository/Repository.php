<?php
namespace Dreamscape\Repository;

use Dreamscape\Contracts\Database\Database as DatabaseContract;
use Dreamscape\Contracts\Database\QueryFilter as QueryFilterContract;

abstract class Repository
{
    /* @var DatabaseContract */
    private $db;

    const DEFAULT_QUERY_LIMIT = 5;

    public function __construct(DatabaseContract $db)
    {
        $this->db = $db;
    }

    public function db()
    {
        return $this->db;
    }

    protected function applyFilters($query, array $filters)
    {
        $where = [];
        $order_by = [];

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
            $query = "{$query} where {$where_clause}";
        }
        if (! empty($order_by_clause)) {
            $query = "{$query} order by {$order_by_clause}";
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

    protected function get($query, array $filters = [], $limit = 0)
    {
        $query = trim($query);

        $query = $this->applyFilters($query, $filters);
        $query = $this->applyLimit($query, $limit);

        return $this->db()->query($query)->fetchAll();
    }
}