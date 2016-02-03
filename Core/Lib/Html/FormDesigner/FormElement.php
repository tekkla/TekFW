<?php
namespace Core\Lib\Html\FormDesigner;

use Core\Lib\Html\FormAbstract;
use Core\Lib\Html\HtmlAbstract;

/**
 * FormElement.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class FormElement
{

    /**
     * Elements type
     *
     * @var string
     */
    private $type = 'control';

    /**
     * Elements content
     *
     * @var string|FormAbstract|HtmlAbstract
     */
    private $content = '';

    /**
     * Sets the element.
     *
     * @param sting|FormAbstract|HtmlAbstract $element
     *
     * @return Ambigous <\Core\Lib\Html\FormAbstract, \Core\Lib\Html\HtmlAbstract, \Core\Lib\Html\FormDesigner\FormGroup>
     */
    public function &setContent($content)
    {
        // Set element type by analyzing the element
        if ($content instanceof FormGroup) {
            $this->type = 'group';
        }
        elseif ($content instanceof FormAbstract) {
            $this->type = 'control';
        }
        elseif ($content instanceof HtmlAbstract) {
            $this->type = 'factory';
        }
        else {
            $this->type = 'html';
        }

        $this->content = $content;

        return $content;
    }

    /**
     * Returns the set element.
     *
     * @return Ambigous <string, \Core\Lib\Html\FormAbstract, \Core\Lib\Html\HtmlAbstract>
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Returns elements type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
