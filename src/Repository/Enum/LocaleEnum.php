<?php


namespace Dreamscape\Repository\Enum;


final class LocaleEnum
{
    const VALUES = ['ae', 'au', 'in', 'nz', 'uk', 'us', 'hk', 'id', 'my', 'ph', 'sg'];

    public static function all()
    {
        return self::VALUES;
    }
}