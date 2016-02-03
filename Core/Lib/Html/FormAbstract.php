<?php
namespace Core\Lib\Html;

use Core\Lib\Html\HtmlAbstract;
use Core\Lib\Html\Form\Label;
use Core\Lib\Traits\StringTrait;
use Core\Lib\Errors\Exceptions\UnexpectedValueException;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;

/**
 * FormAbstract.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class FormAbstract extends HtmlAbstract
{
    use StringTrait;

    /**
     * Name of app the form is used in
     *
     * @var unknown
     */
    private $app;

    /**
     * Model name the element is bound to
     *
     * @var string
     */
    private $model;

    /**
     * Name of the field in model the element is related to
     *
     * @var string
     */
    private $field;

    /**
     * Description for the help block
     *
     * @var string
     */
    private $description;

    /**
     * Flag for binding element to a model field or not
     *
     * @var bool
     */
    private $bound = false;

    /**
     * Width for the element
     *
     * @var string
     */
    private $element_width;

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

    /**
     * Public html object of a form label
     *
     * @var Label
     */
    public $label;

    /**
     * Flags this element to use no label
     *
     * @return \Core\Lib\Html\Elements\FormControlAbstract
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
     * @return \Core\Lib\Html\Form\Label
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
     * Set the app name this element is from
     *
     * @param string $app            
     *
     * @return \Core\Lib\Html\Elements\FormControlAbstract
     */
    public function setApp($app_name)
    {
        $this->app_name = $this->stringUncamelize($app_name);
        
        return $this;
    }

    /**
     * Returns the name of the set app.
     *
     * @throws UnexpectedValueException
     *
     * @return string
     */
    public function getApp()
    {
        if (! isset($this->app_name)) {
            Throw new UnexpectedValueException('App name was not set for this form and cannot be returned');
        }
        
        return $this->app_name;
    }

    /**
     * Set the app this element is from
     *
     * @param string $app            
     *
     * @return \Core\Lib\Html\Elements\FormControlAbstract
     */
    public function setModelName($model_name)
    {
        $this->model_name = $this->stringUncamelize($model_name);
        
        return $this;
    }

    /**
     * Returns the mnodel name set
     *
     * @throws UnexpectedValueException
     *
     * @return string
     */
    public function getModel()
    {
        if (! isset($this->model_name)) {
            Throw new UnexpectedValueException('Model name was not set for element and cannot be returned');
        }
        
        return $this->model_name;
    }

    /**
     * Set the field this element is bound to
     *
     * @param string $app            
     *
     * @return \Core\Lib\Html\Elements\FormControlAbstract
     */
    public function setField($field_name)
    {
        $this->field_name = $this->stringUncamelize($field_name);
        
        return $this;
    }

    /**
     * Returns the fieldname the control is bound to
     *
     * @throws UnexpectedValueException
     *
     * @return string
     */
    public function getField()
    {
        if (! isset($this->field_name) && $this->getBound()) {
            Throw new UnexpectedValueException('There is no field name bount onto "' . $this->name . '" element which can be returned.');
        }
        
        return $this->field_name;
    }

    /**
     * Create the name of the form element by using names of app, $model and field
     *
     * @throws UnexpectedValueException
     *
     * @return \Core\Lib\Html\Elements\FormControlAbstract
     */
    public function createName()
    {
        if (! isset($this->app_name)) {
            throw new UnexpectedValueException('No app name set for your form control.');
        }
        
        if (! isset($this->model_name)) {
            throw new UnexpectedValueException('No model name set for your form control.');
        }
        
        if (! isset($this->field_name)) {
            throw new UnexpectedValueException('No field name set for your form control.');
        }
        
        $this->setName('web[' . $this->app_name . '][' . $this->model_name . '][' . $this->field_name . ']');
        
        return $this;
    }

    /**
     * Creates the dom id using app, model and field names
     *
     * @throws UnexpectedValueException
     *
     * @return \Core\Lib\Html\Elements\FormControlAbstract
     */
    public function createId()
    {
        if (! isset($this->app_name)) {
            throw new UnexpectedValueException('No app name set for your form control.');
        }
        
        if (! isset($this->model_name)) {
            throw new UnexpectedValueException('No model name set for your form control.');
        }
        
        if (! isset($this->field_name)) {
            throw new UnexpectedValueException('No field name set for your form control.');
        }
        
        $this->setId('appform_' . $this->app_name . '_' . $this->model_name . '_' . $this->field_name);
        
        return $this;
    }

    /**
     * Add autofocus attribute
     *
     * @return \Core\Lib\Html\Elements\FormControlAbstract
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
     * @return \Core\Lib\Html\Elements\FormControlAbstract
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
     * @return \Core\Lib\Html\Elements\FormControlAbstract
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
        return isset($this->description);
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
     * @return \Core\Lib\Abstracts\FormControlAbstract
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
     * @return \Core\Lib\Html\Elements\FormControlAbstract
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
            throw new InvalidArgumentException('Element with is no valid', 1000);
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
        return isset($this->element_width) ? $this->element_width : false;
    }

    /**
     * Checks for a set element width
     */
    public function hasElementWidth()
    {
        return isset($this->element_width);
    }

    /**
     * Sets an input mask for the elements
     *
     * @param string $mask            
     *
     * @return \Core\Lib\Html\Elements\FormControlAbstract
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
     * @return \Core\Lib\Html\Elements\FormControlAbstract
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
}