<?php
namespace Core\Ajax\Commands\Dom;

use Core\Ajax\AjaxCommandAbstract;

/**
 * Genric.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Genric extends AjaxCommandAbstract
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
