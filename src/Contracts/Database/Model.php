<?php


namespace Dreamscape\Contracts\Database;


interface Model
{
    const MODEL_SAVE_ACTION = 'save';
    const MODEL_UPDATE_ACTION = 'update';

    const MYSQL_ZERO_DATE = '0000-00-00 00:00:00';
    const DS_ZERO_DATE = '-';

    public static function create(array $data);
}