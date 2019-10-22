<?php
namespace Dreamscape\Repository;

use Dreamscape\Contracts\Database\Database as DatabaseContract;

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
}