<?php
namespace Core\Lib\Ajax\Commands\Dom;

use Core\Lib\Ajax\AjaxCommand;

/**
 * Genric.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Genric extends AjaxCommand
{

    protected $type = 'dom';

    /**
     * Generic DOM function.
     *
     * @param string $function
     * @param string $selector
     * @param array $args
     */
    public function generic($function, $selector, $args = [])
    {
        $this->fn = $function;
        $this->selector = $selector;
        $this->args = $args;
    }
}
