<?php


namespace Dreamscape\Paginator;


final class Paginator
{
    private $pageCount;

    private $dataSize;

    private $perPage;

    public function __construct(array $data, $perPage = 15)
    {
        $this->dataSize = \count($data);
        $this->perPage = $perPage;
    }

    public function paginate($currentPage = 1)
    {
        $totalPages = \ceil($this->dataSize/$this->perPage);
        return [
            'total' => $totalPages,
            'current' => $currentPage,
        ];
    }
}