<?php
namespace Core\Logger\Streams;

/**
 * InterpolateTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
trait InterpolateTrait {

    /**
     * Interpolates context values into the message placeholders
     */
    function interpolate($message, array $context = [])
    {
        // build a replacement array with braces around the context keys
        $replace = array();

        foreach ($context as $key => $val) {

            if (($key == 'exception' || $key == 'throwable') && $val instanceof \Throwable) {
                $val = $val->getTraceAsString();
            }

            if (is_object($val)) {
                $val = 'Objects can not be interpolated.';
            }

            $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}
