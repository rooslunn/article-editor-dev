<?php


namespace Dreamscape\Repository\Enum;


final class ResellerEnum
{
    const VALUES = [
        1 => 'AustDomains',
        1344 => 'CrazyDomains',
        1023 => 'Sitebeat',
    ];

    public static function all()
    {
        return self::VALUES;
    }
}