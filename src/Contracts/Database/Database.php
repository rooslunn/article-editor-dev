<?php


namespace Dreamscape\Contracts\Database;


interface Database
{
    public function query($query);
    public function fetchAll($fetch_style = 0);
    public function fetch();
}