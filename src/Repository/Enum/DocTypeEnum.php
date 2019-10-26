<?php


namespace Dreamscape\Repository\Enum;


final class DocTypeEnum
{
    const VALUES = ['article', 'guide', 'tutorial'];

    public static function all()
    {
        $result = [];
        foreach (self::VALUES as $value) {
            $result[] = [
                'title' => $value,
            ];
        }
        return $result;
    }

}