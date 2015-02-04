<?php
namespace Core\Lib\Content\Html\FormDesigner;

use Core\Lib\Content\Html\FormAbstract;
use Core\Lib\Content\Html\HtmlAbstract;


/**
 * Wrapper Class for FormDesigner elements.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2014
 *
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
     * @return Ambigous <\Core\Lib\Content\Html\FormAbstract, \Core\Lib\Content\Html\HtmlAbstract, \Core\Lib\Content\Html\FormDesigner\FormGroup>
     */
    public function &setContent($content)
    {
        // Set element type by analyzing the element
        if ($content instanceof FormAbstract) {
            $this->type = 'control';
        }
        elseif ($content instanceof HtmlAbstract) {
            $this->type = 'factory';
        }
        elseif ($content instanceof FormGroup) {
            $this->type = 'group';
        } else {
            $this->type = 'html';
        }

        $this->content = $content;

        return $content;
    }

    /**
     * Returns the set element.
     *
     * @return Ambigous <string, \Core\Lib\Content\Html\FormAbstract, \Core\Lib\Content\Html\HtmlAbstract>
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the elements type.
     *
     * @param unknown $type
     *
     * @throws \InvalidArgumentException
     *
     * @return \Core\Lib\Content\Html\FormDesigner\FormElement
     */
    public function setType($type)
    {
        $types = [
            'control',
            'factory',
            'html',
            'group'
        ];

        if (! in_array($type, $types)) {
            Throw new \InvalidArgumentException('The element type "' . $type . '" is not supported. Select from "' . implode('", ', $types) . '"');
        }

        $this->type = $type;

        return $this;
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
