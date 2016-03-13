<?php
namespace Core\Lib\Html\FormDesigner;

use Core\Lib\Traits\StringTrait;
use Core\Lib\Html\FormAbstract;
use Core\Lib\Html\HtmlAbstract;
use Core\Lib\Html\Elements\Div;
use Core\Lib\Html\Form\Button;

/**
 * FormGroup.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class FormGroup
{

    use StringTrait;

    private $name = '';

    /**
     * FormGroups element storage
     *
     * @var array
     */
    private $elements = [];

    /**
     *
     * @var string
     */
    private $index = '';

    /**
     *
     * @var FormDesigner
     */
    public $fd;

    /**
     *
     * @var FormGroup
     */
    private $parent;


    /**
     *
     * @var Div
     */
    public $html;

    public function __construct()
    {
        $this->html = new Div();
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Injects reference to the FormDesigner object this FormGroup is part of.
     *
     * @param FormDesigner $form_designer
     *            Reference to FormDesigner object this FormGroup belongs to
     *
     * @return \Core\Lib\Html\FormDesigner\FormGroup
     */
    public function injectFormDesigner(FormDesigner $form_designer)
    {
        $this->fd = $form_designer;

        return $this;
    }

    /**
     * Injects reference to a parent FormGroup object
     *
     * @param FormGroup $form_group
     *            Reference ot parent FormGroup object
     *
     * @return \Core\Lib\Html\FormDesigner\FormGroup
     */
    public function injectParentGroup(FormGroup $form_group)
    {
        $this->parent = $form_group;

        return $this;
    }

    /**
     * Creates a formcontrol and adds it by it's name to the controls list.
     *
     * @param string $type
     *            The type of control to create
     * @param string $name
     *            Name of the control. Ths name is used to bind the control to a model field.
     *
     * @return \Core\Lib\Html\FormAbstract
     */
    public function &addControl($control, $name='', $label = '', $value = '', $description = '', $unbound = false)
    {
        $control = $this->di->instance(__NAMESPACE__ . '\Controls\\' . $this->stringCamelize($control) . 'Control');

        // Inject html factory for controls which are creating html controls by themself
        $control->factory = $this->di->get('core.html.factory');

        // Set name
        $control->setName($name);

        // set contols name
        if ($unbound && method_exists($control, 'setUnbound')) {
            $control->setUnbound();
            $control->removename();
        }

        // Label set?
        if (! empty($label) && method_exists($control, 'setLabel')) {
            $control->setLabel($label);
        }

        if (! empty($value) && method_exists($control, 'setValue')) {
            $control->setValue($value);
        }

        if (! empty($description) && method_exists($control, 'setDescription')) {
            $control->setDescription($description);
        }

        if ($control instanceof Button) {
            $control->noLabel();
        }

        // Create element
        $element = $this->elementFactory('control', $control);

        return $control;
    }

    /**
     * Creates a new group element based on pure html string.
     *
     * @param string $html
     *            Creates a FormElement with html content
     *
     * @return \Core\Lib\Html\FormDesigner\FormGroup
     */
    public function addHtml($html)
    {
        $this->elementFactory('html', $html);

        return $this;
    }

    /**
     * Create an new html object and adds it as new FormElement
     *
     * @param string $element
     *            Name/Path of element to create
     * @param array $args
     *            Optional arguments to be passed on element creation call.
     *
     * @return \Core\Lib\Html\HtmlAbstract
     */
    public function &addElement($element, $args = [])
    {
        $element = $this->di->get('core.html.factory')->create($element, $args);

        $this->elementFactory('factory', $element);

        return $element;
    }

    /**
     * Creates a new FormGroup objects and adds it as new FormElement
     *
     * @param bool $unshift
     *            Optional flag to add group at beginning of controls array.
     *
     * @return \Core\Lib\Html\FormDesigner\FormGroup
     */
    public function &addGroup($unshift = false)
    {
        /* @var $group FormGroup */
        $group = $this->di->instance(__NAMESPACE__ . '\FormGroup');

        $this->elementFactory('group', $group, $unshift);

        $group->injectFormDesigner($this->fd);
        $group->injectParentGroup($this);

        return $group;
    }

    /**
     * Factory mehtod to create new FormElement object within this group object.
     *
     * @param string $type
     *            Elementtype to create
     * @param string|FormAbstract|HtmlAbstract|FormGroup $content
     *            Content of the element to create
     * @param bool $unshift
     *            Optional boolean flag to add element on top of elements stack of this group
     *
     * @return \Core\Lib\Html\FormDesigner\FormElement
     */
    private function elementFactory($type, $content, $unshift = false)
    {
        $element = $this->di->instance(__NAMESPACE__ . '\FormElement');
        $element->setContent($content);

        if ($unshift == true) {
            array_unshift($this->elements, $element);
        }
        else {
            $this->elements[] = $element;
        }

        return $element;
    }

    /**
     * Returns all element objects of the FormGroup
     *
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }
}
