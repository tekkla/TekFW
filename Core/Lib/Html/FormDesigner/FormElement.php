<?php
namespace Core\Lib\Html\FormDesigner;

use Core\Lib\Html\FormAbstract;
use Core\Lib\Html\HtmlAbstract;
use Core\Lib\Html\FormDesigner\Controls\FormDesignerControlInterface;
use Core\Lib\Html\FormDesigner\Controls\ControlsCollectionInterface;

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

    private $app_name = '';

    private $container_name = '';

    public function setAppName($app_name)
    {
        $this->app_name = $app_name;

        return $this;
    }

    public function getAppName()
    {
        if (! $this->app_name) {
            Throw new FormDesignerException('No app name set.');
        }

        return $this->app_name;
    }

    public function setContainerName($container_name)
    {
        $this->container_name = $container_name;

        return $this;
    }

    public function getContainerName()
    {
        if (! $this->container_name) {
            Throw new FormDesignerException('No container name set.');
        }

        return $this->container_name;
    }

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
        switch (true) {

            case ($content instanceof FormGroup):
                $this->type = 'group';
                break;

            case ($content instanceof ControlsCollectionInterface):
                $this->type = 'collection';
                break;

            case ($content instanceof FormAbstract):
                $this->type = 'control';
                break;

            case ($content instanceof HtmlAbstract):
                $this->type = 'factory';
                break;

            default:
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
