<?php
namespace Core\Lib\Content\Html\FormDesigner;

use Core\Lib\Traits\StringTrait;
use Core\Lib\Content\Html\FormAbstract;
use Core\Lib\Content\Html\HtmlAbstract;
use Core\Lib\Content\Html\Elements\Div;

/**
 * FormGroup.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class FormGroup extends Div
{

    use StringTrait;

    /**
     * FormGroups element storage
     *
     * @var array
     */
    private $elements = [];

    private $container;

    /**
     * Creates a formcontrol and adds it by it's name to the controls list.
     *
     * @param string $type The type of control to create
     * @param string $name Name of the control. Ths name is used to bind the control to a model field.
     *
     * @return FormAbstract @TODO Check container and autobind param to be neccessary.
     */
    public function &addControl($control, $name = '', $label = '', $value = '')
    {
        $control = $this->di->instance(__NAMESPACE__ . '\Controls\\' . $this->camelizeString($control) . 'Control');

        // Inject html factory for controls which are creating html controls by themself
        $control->factory = $this->di->get('core.content.html.factory');

        // set contols name
        if (!empty($name)) {
            $control->setName($name);
        }
        else {
            $control->setUnbound();
        }

        // And optionally bind this control to a field with the same name
        if (! empty($name) && method_exists($control, 'setField')) {
            $control->setField($name);
        }

        // Label set?
        if (! empty($label) && method_exists($control, 'setLabel')) {
            $control->setLabel($label);
        }

        if (! empty($value) && method_exists($control, 'setValue')) {
            $control->setValue($value);
        }

        // Create element
        $this->elementFactory('control', $control);

        return $control;
    }

    /**
     * Creates a new group element based on pure html string.
     *
     * @param string$html
     */
    public function addHtml($html)
    {
        $this->elementFactory('html', $html);
    }

    /**
     * Create an new html object and adds it as new FormElement
     *
     * @param string $element Name/Path of element to create
     * @param array $args Optional arguments to be passed on element creation call.
     *
     * @return HtmlAbstract
     */
    public function &addElement($element, $args = [])
    {
        $element = $this->di->get('core.content.html.factory')->create($element, $args);

        $this->elementFactory('factory', $element);

        return $element;
    }

    /**
     * Creates a new FormGroup objects and adds it as new FormElement
     *
     * @param bool $unshift Optional flag to add group at beginning of controls array.
     *
     * @return FormGroup
     */
    public function &addGroup($unshift = false)
    {
        $group = $this->di->instance(__NAMESPACE__ . '\FormGroup');

        $this->elementFactory('group', $group, $unshift);

        return $group;
    }

    /**
     * Factory mehtod to create new FormElement object within this group object.
     *
     * @param string $type Elementtype to create
     * @param string|FormAbstract|HtmlAbstract|FormGroup $content
     * @param bool $unshift Optional boolean flag to add element on top of elements stack of this group
     */
    private function elementFactory($type, $content, $unshift = false)
    {
        $element = $this->di->instance(__NAMESPACE__ . '\FormElement');
        $element->setType($type);
        $element->setContent($content);

        if ($unshift == true) {
            array_unshift($this->elements, $element);
        }
        else {
            $this->elements[] = $element;
        }
    }

    /**
     * Returns all element objects of the FormGroup
     *
     * @return multitype:
     */
    public function getElements()
    {
        return $this->elements;
    }
}
