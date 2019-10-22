<?php
namespace Dreamscape\Repository;

use Dreamscape\Contracts\Database\Database as DatabaseContract;

abstract class Repository
{
    /* @var DatabaseContract */
    private $db;

    public function __construct(DatabaseContract $db)
    {
        $this->db = $db;
    }

    public function db()
    {
        return $this->db;
    }
}