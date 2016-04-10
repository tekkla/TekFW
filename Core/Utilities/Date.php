<?php
namespace Core\Utilities;

use Core\Language\TextTrait;

/**
 * Date.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Date
{

    use TextTrait;

    public static function timeAgo($date)
    {
        $time = strtotime($date);
        $now = time();
        $ago = $now - $time;

        switch (true) {
            case ($ago < 60):
                $when = round($ago);
                $timestring = ($when == 1) ? "second" : "seconds";
                break;
            case ($ago < 3600):
                $when = round($ago / 60);
                $timestring = ($when == 1) ? "minute" : "minutes";
                break;
            case ($ago == 3600 && $ago < 86400):
                $when = round($ago / 60 / 60);
                $timestring = ($when == 1) ? "hour" : "hours";
                break;
            case ($ago == 86400 && $ago < 2629743.83):
                $when = round($ago / 60 / 60 / 24);
                $timestring = ($when == 1) ? "day" : "days";
                break;
            case ($ago == 2629743.83 && $ago < 31556926):
                $when = round($ago / 60 / 60 / 24 / 30.4375);
                $timestring = ($when == 1) ? 'month' : 'months';

                break;
            default:
                $when = round($ago / 60 / 60 / 24 / 365);
                $timestring = ($when == 1) ? 'year' : 'years';
                break;
        }

        $timestring = $this->text('time.strings', 'Core')[$timestring];

        return sprintf($this->text('time.text.ago', 'Core'), $when, $timestring);
    }
}

