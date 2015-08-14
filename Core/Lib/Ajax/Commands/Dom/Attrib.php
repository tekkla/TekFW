<?php
namespace Core\Lib\Ajax\Commands\Dom;

use Core\Lib\Ajax\AjaxCommand;

/**
 * Attrib.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Attrib extends AjaxCommand
{

    protected $type = 'dom';

    protected $fn = 'attrib';

    /**
     * Change value of a DOM attribute.
     *
     * @param string $selector
     * @param string $attribute
     * @param string $value
     */
    public function attrib($selector, $attribute, $value)
    {
        $this->selector = $selector;
        $this->args = [
            $attribute,
            $value
        ];
    }
}
