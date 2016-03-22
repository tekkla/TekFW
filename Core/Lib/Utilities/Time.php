<?php
namespace Core\Lib\Utilities;

/**
 * Time.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Time
{

    const SEC_PER_DAY = 60 * 60 * 24;
    const SEC_PER_HOUR = 60 * 60;

    /**
     * Converts a date and time string into timestamp
     *
     * @param string $date
     * @param string $time
     * @return number
     */
    public function toTimestamp($date, $time = null)
    {
        if (! isset($time)) {
            $time = '00:00:01';
        }

            // create unix_timestamp
        return strtotime($date . ' ' . $time);
    }

    /**
     * Converts a timestamp into many date infos
     *
     * @param int $timestamp
     * @param string $dateformat
     * @param string $timeformat
     * @return multitype:unknown string
     */
    public function fromTimestamp($timestamp, $dateformat = 'd.m.Y', $timeformat = 'H:i')
    {
        return [
            'stamp' => $timestamp,
            'date' => date($dateformat, $timestamp),
            'time' => date($timeformat, $timestamp),
            'week' => date('W', $timestamp),
            'day' => date('d', $timestamp),
            'month' => date('m', $timestamp),
            'hour' => date('H', $timestamp),
            'minute' => date('i', $timestamp)
        ];
    }

    /**
     * Converts a date into another format
     *
     * @param unknown $date
     * @param string $format
     * @return string
     */
    public function dateConversion($date, $format = 'd.m.Y')
    {
        return date($format, strtotime($date));
    }

    /**
     * Calculates
     *
     * @param integer $timestamp
     * @param boolean $bLeft
     * @return string
     */
    public function timeLeft($timestamp, $bLeft = true)
    {
        global $txt;

        if ($bLeft == true) {
            $diff = time() - $timestamp;
        }
        else {
            $diff = $timestamp;
        }

        $showdiff = [
            "y" => 0,
            "m" => 0,
            "w" => 0,
            "d" => 0,
            "h" => 0,
            "min" => 0
        ];

        while ($diff >= 31536000) {
            // 1 year = 31536000 seconds
            $diff -= 31536000;
            $showdiff['y'] ++;
        }
        while ($diff >= 2419200) {
            // 1 day = 2592000 seconds
            $diff -= 2419200;
            $showdiff['m'] ++;
        }
        while ($diff >= 648000) {
            // 1 week = 604800 seconds
            $diff -= 648000;
            $showdiff['w'] ++;
        }

        while ($diff >= 86400) {
            // 1 day = 86400 seconds
            $diff -= 86400;
            $showdiff['d'] ++;
        }

        while ($diff >= 3600) {
            // 1 hour = 3600 seconds
            $diff -= 3600;
            $showdiff['h'] ++;
        }

        while ($diff >= 60) {
            // 1 minute = 60 seconds
            $diff -= 60;
            $showdiff['min'] ++;
        }

        $out = '';

        if (! empty($showdiff['y'])) {
            return $showdiff['y'] . ' ' . $txt['app_web_time_year' . ($showdiff['y'] > 1 ? 's' : '')];
        }

        if (! empty($showdiff['m'])) {
            return $showdiff['m'] . ' ' . $txt['app_web_time_month' . ($showdiff['m'] > 1 ? 's' : '')];
        }

        if (! empty($showdiff['w'])) {
            return $showdiff['w'] . ' ' . $txt['app_web_time_week' . ($showdiff['w'] > 1 ? 's' : '')];
        }

        if (! empty($showdiff['d'])) {
            return $showdiff['d'] . ' ' . $txt['app_web_time_day' . ($showdiff['d'] > 1 ? 's' : '')];
        }

        if (! empty($showdiff['h'])) {
            $out .= $showdiff['h'] . ' ' . $txt['app_web_time_hour' . ($showdiff['h'] > 1 ? 's' : '')];
        }

        if (! empty($showdiff['min'])) {
            $out .= $showdiff['min'] . ' ' . $txt['app_web_time_minute' . ($showdiff['min'] > 1 ? 's' : '')];
        }

        return $out;
    }
}
