<?php
namespace Core\Lib\Ajax\Commands\Dom;

use Core\Lib\Ajax\AjaxCommandAbstract;

/**
 * Css.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Css extends AjaxCommandAbstract
{

    protected $type = 'dom';

    protected $fn = 'css';

    /**
     * Changes css property of dom element.
     *
     * @param string $selector
     * @param string $property
     * @param string $value
     */
    public function css($selector, $property, $value)
    {
        $this->selector = $selector;
        $this->args = [
            $property,
            $value
        ];
    }
}
