<?php

namespace CacheTool\Util;

class Formatter
{
    public static function bytes($bytes, $precision = 2)
    {
        $units = array('b', 'KiB', 'MiB', 'GiB', 'TiB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function date($date, $format = null)
    {
        if (false === $date instanceof \DateTime) {
            if ($format !== null) {
                $date = \DateTime::createFromFormat($format, $date);
            } else {
                $date = new \DateTime($date);
            }
        }

        return $date->format(\DateTime::RFC2822);
    }
}
