<?php
namespace Core\Ajax\Commands\Dom;

use Core\Ajax\AjaxCommandAbstract;

/**
 * AddClass.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class AddClass extends AjaxCommandAbstract
{

    protected $fn = 'addClass';

    protected $type = 'dom';

    /**
     * Change css property of dom element.
     *
     * @param string $selector
     * @param string $class
     */
    public function addClass($selector, $class)
    {
        $this->selector = $selector;
        $this->args = $class;
    }
}
