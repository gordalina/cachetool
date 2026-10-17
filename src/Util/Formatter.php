<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Util;

class Formatter
{
    /**
     * @param  integer  $bytes
     * @param  integer $precision
     * @return string
     */
    public static function bytes($bytes, $precision = 2)
    {
        $units = ['b', 'KiB', 'MiB', 'GiB', 'TiB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * @param  mixed  $date
     * @param  string $format
     * @return string
     */
    public static function date($date, $format = null)
    {
        if (false === $date instanceof \DateTime) {
            if ($format !== null) {
                $date = \DateTime::createFromFormat($format, $date);
            } else {
                $date = new \DateTime($date);
            }
            $date->setTimezone(new \DateTimeZone(self::timezone()));
        }

        return $date->format(\DateTime::RFC2822);
    }

    /**
     * PHP ignores the system timezone and uses UTC when date.timezone is not set
     *
     * @return string
     */
    public static function timezone()
    {
        $default = date_default_timezone_get();

        if (get_cfg_var('date.timezone') || $default !== 'UTC') {
            return $default;
        }

        $tz = ltrim((string) getenv('TZ'), ':') ?: (string) @readlink('/etc/localtime');
        if (false !== $pos = strpos($tz, 'zoneinfo/')) {
            $tz = substr($tz, $pos + strlen('zoneinfo/'));
        }

        return in_array($tz, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC), true) ? $tz : $default;
    }
}
