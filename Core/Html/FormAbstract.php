<?php
namespace Core\Html;

use Core\Html\HtmlAbstract;
use Core\Html\Form\Label;
use Core\Traits\StringTrait;

/**
 * FormAbstract.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class FormAbstract extends HtmlAbstract
{
    use StringTrait;

    /**
     * Description for the help block
     *
     * @var string
     */
    private $description = '';

    /**
     * Flag for binding element to a model field or not
     *
     * @var bool
     */
    private $bound = true;

    /**
     * Width for the element
     *
     * @var string
     */
    private $element_width = '';

    /**
     * Value for creation an hidden field for the original value
     * Good for comparing before and then values
     *
     * @var bool
     */
    private $compare_value;

    /**
     * Signals that we want a label or not
     *
     * @var bool
     */
    private $use_label = true;

    private $is_array = false;

    /**
     * Public html object of a form label
     *
     * @var Label
     */
    public $label;

    /**
     * Flags this element to use no label
     *
     * @return \Core\Html\Elements\FormControlAbstract
     */
    public function noLabel()
    {
        unset($this->label);
        $this->use_label = false;

        return $this;
    }

    /**
     * Creates a Label html object and injects it into the element
     *
     * @param string $label_text
     *            The text to show as label
     *
     * @return \Core\Html\Form\Label
     */
    public function setLabel($label_text)
    {
        $this->label = $this->factory->create('Form\Label');
        $this->label->setInner($label_text);
        $this->use_label = true;

        return $this->label;
    }

    /**
     * Returns the inner value of label or false if label is not set.
     *
     * @return Ambigous <boolean, strin
     */
    public function getLabel()
    {
        return isset($this->label) ? $this->label->getInner() : false;
    }

    /**
     * Returns the state of label using.
     *
     * @return boolean
     */
    public function hasLabel()
    {
        return $this->use_label;
    }

    /**
     * Add autofocus attribute
     *
     * @return \Core\Html\Elements\FormControlAbstract
     */
    public function setAutofocus()
    {
        $this->addAttribute('autofocus');

        return $this;
    }

    /**
     * Declare this element as unbound, so the FormDesigner does not need to
     * try to fill it with data from the Model.
     *
     * @return \Core\Html\Elements\FormControlAbstract
     */
    public function setUnbound()
    {
        $this->bound = false;

        return $this;
    }

    /**
     * Returns the current bound state
     *
     * @return boolean
     */
    public function isBound()
    {
        return $this->bound;
    }

    /**
     * Returns the type attribute but only if a setType() method exists in the childclass and the type attribute isset.
     * This method is used to determine the type of input form elements.
     *
     * @return string|null
     */
    public function getType()
    {
        return method_exists($this, 'setType') ? $this->getAttribute('type') : null;
    }

    /**
     * Set a description which will be used as a help block
     *
     * @param sting $text
     *
     * @return \Core\Html\Elements\FormControlAbstract
     */
    public function setDescription($text)
    {
        $this->description = $text;

        return $this;
    }

    /**
     * Returns set state of description
     */
    public function hasDescription()
    {
        return !empty($this->description);
    }

    /**
     * Returns the set description string
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Handles the creation state of an hidden element for comparision.
     * If $compare parameter not set, the method returns the current state.
     *
     * @param boolean $state
     *
     * @return boolean
     */
    public function hasCompare()
    {
        return isset($this->compare_value);
    }

    /**
     * Sets compare field value
     *
     * @param mixed $compare_value
     *
     * @return \Core\Abstracts\FormControlAbstract
     */
    public function setCompare($compare_value)
    {
        $this->compare_value = $compare_value;

        return $this;
    }

    /**
     * Returns compare field value
     *
     * @return boolean
     */
    public function getCompare()
    {
        return $this->compare_value;
    }

    /**
     * Assign an bootstrap element width.
     *
     * @param string $element_width
     *            BS grid sizes like "sm-3" or "lg-5". Needed "col-" will be added by the method.
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Html\Elements\FormControlAbstract
     */
    public function setElementWidth($element_width = 'sm-3')
    {
        $sizes = [
            'xs',
            'sm',
            'md',
            'lg'
        ];
        $allowed_widths = [];

        foreach ($sizes as $size) {
            for ($i = 1; $i < 13; $i ++) {
                $allowed_widths[] = $size . '-' . $i;
            }
        }

        if (! in_array($element_width, $allowed_widths)) {
            Throw new HtmlException(sprintf('Element width "%s" is not valid. Select from: %s', $element_width, implode(', ', $sizes)));
        }

        $this->element_width = 'col-' . $element_width;

        return $this;
    }

    /**
     * Returns a set element width or boolean false if not set.
     *
     * @return string|boolean
     */
    public function getElementWidth()
    {
        return $this->element_width;
    }

    /**
     * Checks for a set element width
     */
    public function hasElementWidth()
    {
        return !empty($this->element_width);
    }

    /**
     * Sets an input mask for the elements
     *
     * @param string $mask
     *
     * @return \Core\Html\Elements\FormControlAbstract
     */
    public function setMask($mask)
    {
        $this->addData('form-mask', $mask);

        return $this;
    }

    /**
     * Disabled attribute setter and checker.
     * Accepts parameter "null", "0" and "1".
     * "null" means to check for a set disabled attribute
     * "0" means to remove disabled attribute
     * "1" means to set disabled attribute
     *
     * @param int $state
     *
     * @return \Core\Html\Elements\FormControlAbstract
     */
    public function isDisabled($state = null)
    {
        $attrib = 'disabled';

        if (! isset($state)) {
            return $this->checkAttribute($attrib);
        }

        if ($state == 0) {
            $this->removeAttribute($attrib);
        }
        else {
            $this->addAttribute($attrib);
        }

        return $this;
    }

    public function isArray($state = null)
    {
        if (empty($state)) {
            return $this->is_array;
        }

        $this->is_array = (bool) $state;

        return $this;
    }
}
