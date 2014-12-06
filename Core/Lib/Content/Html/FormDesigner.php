<?php
namespace Core\Lib\Content\Html;

use Core\Lib\Amvc\Model;
use Core\Lib\Content\Html\Controls\UiButton;
use Core\Lib\Content\Html\Form\Form;
use Core\Lib\Content\Html\Form\Input;
use Core\Lib\Content\Html\Form\Button;
use Core\Lib\Content\Html\Controls\Group;
use Core\Lib\Content\Html\FormElementAbstract;
use Core\Lib\Data\Container;
use Core\Lib\Content\Html\Form\Checkbox;

/**
 * FormDesigner
 *
 * @todo Write explanation... or an app which explains the basics
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2014 by author
 */
final class FormDesigner extends Form
{
    use\Core\Lib\Traits\StringTrait;
    use\Core\Lib\Traits\TextTrait;

    /**
     * Form controls storage
     *
     * @var array
     */
    private $controls = [];

    /**
     * The mode the data will be send to server
     *
     * @var string Options: full | ajax / Default: full
     */
    private $send_mode = 'submit';

    /**
     * Form name extension
     *
     * @var string
     */
    private $name_ext;

    /**
     * Name of the related app
     *
     * @var string
     */
    public $app_name;

    /**
     * Name of the attached model
     *
     * @var string
     */
    private $control_name;

    /**
     * Displaymode of the form h = horizontal v = vertical (default) i = inline
     *
     * @see http://getbootstrap.com/css/#forms
     * @var string
     */
    private $display_mode = 'v';

    /**
     * The gridtype used in the form Select from:
     * col-xs, col-sm (default), col-md and col-lg
     *
     * @see http://getbootstrap.com/css/#grid-options
     * @var string
     */
    private $grid_type = 'col-sm';

    /**
     * Buttons to use with this form.
     * Every form has a submit button.
     *
     * @var array
     */
    private $buttons = array(
        'submit' => 'save'
    );

    /**
     * By default buttons have no value.
     *
     * @var bool
     */
    private $use_button_values = false;

    /**
     * Icons for buttons
     *
     * @var array
     */
    private $icons = array(
        'submit' => 'save',
        'reset' => 'eraser'
    );

    /**
     * Associated model
     *
     * @var Model
     */
    private $container;

    /**
     * Name of the route which creates the action url
     *
     * @var string
     */
    public $route;

    /**
     * *********************************
     */
    /* ??? */
    /**
     * *********************************
     */
    private $group_open = false;

    private $group_name;

    private $group_headline;

    private $group_header;

    private $group_description;

    private $group;

    private $grid_label;

    private $grid_control;

    /**
     * Inject model dependency
     *
     * @param Container $container
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function attachContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    public function setModelName($model_name)
    {
        $this->model_name = $this->uncamelizeString($model_name);
    }

    public function setGridLabel($size)
    {
        $this->grid_label = $size;
        return $this;
    }

    public function setGridControl($size)
    {
        $this->grid_control = $size;

        return $this;
    }

    /**
     * Extends the form name and id on creation with this extensiondata
     *
     * @param int|string $name_ext
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function extendName($name_ext)
    {
        $this->name_ext = $name_ext;

        return $this;
    }

    /**
     * Set the sendmode for the form.
     * You can use the html submit or the frameworks ajax system. Default: 'submit'
     *
     * @param string $send_mode
     *            Send mode which can be 'ajax' or 'submit'
     *
     * @throws Error
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function setSendMode($send_mode)
    {
        $modes = [
            'ajax',
            'submit'
        ];

        if (! in_array($send_mode, $modes)) {
            Throw new \InvalidArgumentException('Wrong form sendmode.', 1000);
        }

        $this->send_mode = $send_mode;

        return $this;
    }

    /**
     * Set the name of the related app.
     *
     * @param string $app_name
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function setApp($app_name)
    {
        $this->app_name = (string) $app_name;

        return $this;
    }

    /**
     * Start control group
     *
     * @param unknown $group_name
     *
     * @return Group
     */
    public function &openGroup($group_name = '')
    {
        if (! $group_name) {
            $group_name = uniqid();
        }

        // close current open group
        if (isset($this->group_name)) {
            $this->closeGroup();
        }

        $this->group_name = $group_name;

        // Create group html object
        $this->controls[$this->group_name] = $this->factory->create('Controls\Group', [
            'setName' => $group_name
        ]);

        return $this->controls[$this->group_name];
    }

    /**
     * Checks for an currently open group and closes it if found.
     */
    public function closeGroup()
    {
        if (isset($this->group_name)) {
            $this->controls[$this->group_name . '_close'] = 'close_group';
            unset($this->group_name);
        }
    }

    public function setGroupHeadline($headline)
    {
        $this->group_headline = $headline;
    }

    public function setGroupHeader($header)
    {
        $this->group_header = $header;
    }

    /**
     * Creates a formelement and adds it by it's name to the controls member.
     *
     * @param string $type
     *            The type of control to create
     * @param string $name
     *            Name of the control. Ths name is used to bind the control to a model field.
     * @throws Error
     * @return Ambigous <\Core\Lib\Content\Html\Controls\UiButton, \Core\Lib\Content\Html\Controls\Edito, Html
     */
    public function &createElement($type, $name = '', $params = [])
    {
        // Default element
        $element = [
            'class' => 'Form\Input'
        ];

        switch ($type) {
            case 'hidden':
                $element['setType'] = 'hidden';
                break;

            case 'text':
                $element['setType'] = 'text';
                break;

            case 'number':
                $element['setType'] = 'number';
                break;

            case 'mail' :
				/* @var Input $element */
				$element['setType'] = 'mail';
                break;

            case 'phone' :
				/* @var Input $element */
				$element['setType'] = 'tel';
                break;

            case 'url':
                $element['setType'] = 'url';
                break;

            case 'date':
            case 'date-iso':
                $element['class'] = 'Controls\DateTimePicker';
                $element['setFormat'] = 'YYYY-MM-DD';
                $element['setMask'] = '9999-99-99';
                $element['setMaxlenght'] = 10;
                $element['setSize'] = 10;
                break;

            case 'date-us':
                $element['class'] = 'Controls\DateTimePicker';
                $element['setFormat'] = 'mm/dd/yyyy';
                $element['setMask'] = '99/99/9999';
                $element['setMaxlenght'] = 10;
                $element['setSize'] = 10;
                break;

            case 'date-gr':
                $element['class'] = 'Controls\DateTimePicker';
                $element['setFormat'] = 'dd.mm.yyyy';
                $element['setMask'] = '99.99.9999';
                $element['setMaxlenght'] = 10;
                $element['setSize'] = 10;
                break;

            case 'time-24':
                $element['class'] = 'Controls\DateTimePicker';
                $element['setFormat'] = 'HH:mm';
                $element['setMask'] = '99:99';
                $element['setMaxlenght'] = 5;
                $element['setSize'] = 5;
                break;

            case 'time-24s':
                $element['class'] = 'Controls\DateTimePicker';
                $element['setFormat'] = 'HH:mm:ss';
                $element['setMask'] = '99:99:99';
                $element['setMaxlenght'] = 8;
                $element['setSize'] = 8;
                break;

            case 'time-12':
                $element['class'] = 'Controls\DateTimePicker';
                $element['setFormat'] = 'hh:mm A/PM';
                $element['setMask'] = '99:99';
                $element['setMaxlenght'] = 5;
                $element['setSize'] = 5;
                break;

            case 'time-12s':
                $element['class'] = 'Controls\DateTimePicker';
                $element['setFormat'] = 'hh:mm::ss A/PM';
                $element['setMask'] = '99:99:99';
                $element['setMaxlenght'] = 8;
                $element['setSize'] = 8;
                $element['showMeridian'] = true;
                break;

            case 'datetime':
            case 'datetime-iso':
                $element['class'] = 'Controls\DateTimePicker';
                $element['setFormat'] = 'YYYY-MM-DD HH:mm';
                $element['setMask'] = '9999-99-99 99:99';
                $element['setMaxlenght'] = 16;
                $element['setSize'] = 16;
                break;

            case 'password':
                $element['setType'] = 'password';
                break;

            case 'file':
                $element['setType'] = 'file';
                break;

            case 'select':
                $element['class'] = 'Form\Select';
                break;

            case 'multiselect' :
				/* @var Select $element */
				$element['class'] = 'Form\Select';
                $element['isMultiple'] = 1;
                $element['setSize'] = 10;
                break;

            case 'dataselect':
                $element['class'] = 'Controls\DataSelect';
                $element['addCss'] = 'form-select';
                break;

            case 'submit':
                $element['class'] = 'Form\Button';
                $element['setType'] = 'submit';
                $element['setInner'] = $this->txt('btn_save');
                $element['useIcon'] = 'save';
                $element['isPrimary'] = 'true';
                break;

            case 'reset':
                $element['class'] = 'Form\Button';
                $element['setType'] = 'reset';
                $element['setInner'] = $this->txt('btn_reset');
                break;

            case 'textarea':
                $element['class'] = 'Form\Textarea';
                break;

            case 'checkbox':
                $element['class'] = 'Form\Checkbox';
                break;

            case 'switch':
                $element['class'] = 'Controls\OnOffSwitch';
                break;

            case 'optiongroup':
                $element['class'] = 'Controls\OptionGroup';
                break;

            case 'button':
                $element['class'] = 'Form\Button';
                break;

            case 'ajaxbutton':
                $element['class'] = 'Controls\UiButton';
                $element['setType'] = 'button';
                $element['setMode'] = 'ajax';
                break;

            case 'ajaxicon':
                $element['class'] = 'Controls\UiButton';
                $element['setType'] = 'icon';
                $element['setMode'] = 'ajax';
                break;

            case 'editor':
                $element['class'] = 'Controls\Editor';
                break;

            case 'range':
                if (! isset($params))
                    Throw new \InvalidArgumentException('Range elements need min and max parameters to be set. None was set.', 1001);

                if (count($params) < 2)
                    Throw new \InvalidArgumentException('Range elements need min and max parameters to be set. You only set one parameter.', 1001);

                if (! is_int($params[0]) || ! is_int($params[1]))
                    Throw new \InvalidArgumentException('Range elements parameter need to be of type INT.', 1000);

                $element['setType'] = 'number';
                $element['addAttribute'] = [
                    'min' => $params[0],
                    'max' => $params[1]
                ];

                break;

            /*
             * @TODO TYPES color image month radio range search tel week
             */

            // Simple html elements for layouting
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $element['class'] = 'Elements\Heading';
                $element['setInner'] = $name;
                $element['setSize'] = substr($type, - 1, 1);
                break;

            case 'p':
                $element['class'] = 'Elements\Paragraph';
                $element['setInner'] = $name;
                break;
        }

        // create Element
        $class = $element['class'];
        unset($element['class']);

        $element = $this->factory->create($class, $element);

        if ($element instanceof FormElementAbstract) {
            $element->addCss('form-' . $type);
            $element->setName($name);
        }

        if (method_exists($element, 'setField') && isset($this->container) && isset($this->container[$name])) {
            $element->setField($name);
        } else {
            // Set element as unbound as long as it is a FormElement subclass
            if ($element instanceof FormElementAbstract) {
                $element->setUnbound();
            }
        }

        $this->controls[$name] = $element;

        return $this->controls[$name];
    }

    /**
     * Add a html form control to forms controllist
     *
     * @param object|string $control->
     *
     * @return \Core\Lib\Html\FormDesigner
     */
    public function addControl($name, $control)
    {
        $this->controls[$name] = $control;
        return $this;
    }

    public function setAppName($app_name)
    {
        $this->app_name = $app_name;

        return $this;
    }

    public function setControlName($control_name)
    {
        $this->control_name = $control_name;

        return $this;
    }

    public function build()
    {
        // manual forms need a set app and model name
        if (! isset($this->app_name)) {
            Throw new \RuntimeException('The FormDesigner needs a name of a related app.', 10000);
        }

        if (! isset($this->control_name)) {
            Throw new \RuntimeException('The FormDesigner needs a name for the controls,', 10000);
        }

        if (empty($this->controls)) {
            Throw new \RuntimeException('Your form has no controls to show. Add controls and try again.');
        }

        $base_form_name = 'appform';

        $this->app_name = $this->uncamelizeString($this->app_name);
        $this->control_name = $this->uncamelizeString($this->control_name);

        // Create formname
        if (! $this->name) {
            // Create control id prefix based on the model name
            $control_id_prefix = '' . $this->app_name . '_' . $this->control_name;

            // Create form name based on model name and possible extensions
            $this->name = $base_form_name . '_' . $this->app_name . '_' . $this->control_name . (isset($this->name_ext) ? '_' . $this->name_ext : '');
        } else {
            // Create control id prefix based on the provided form name
            $control_id_prefix = '' . $this->app_name . '_' . $this->name;

            // Create form name based on th provided form name
            $this->name = $base_form_name . $this->app_name . '_' . $this->name . (isset($this->name_ext) ? '_' . $this->name_ext : '');
        }

        // Use formname as id when not set
        if (! $this->id) {
            $this->id = str_replace('_', '-', $this->name);
        }

            // Create control name prefix
        $control_name_prefix = $this->app_name . '[' . $this->control_name . ']';

        // Create display mode
        switch ($this->display_mode) {
            case 'h':
                $this->addCss('form-horizontal');
                break;
            case 'i':
                $this->addCss('form-inline');
                break;
        }

        // Control html container
        $html_control = '';

        // are there global and not control related errors?
        if ($this->hasContainer() && $this->container->hasErrors() && isset($this->container->errors['@'])) {

            $div = $this->factory->create('Element\Div');
            $div->addCss('alert alert-danger');
            $div->setInner(implode('<br>', $this->container->errors['@']));

            $html_control .= $div->build();
        }

        // Create form buttons
        foreach ($this->buttons as $btn => $text) {

            $btn_name = 'btn_' . $btn;
            $btn_id = 'btn-' . str_replace('_', '-', $btn);

            /* @var $button \Core\Lib\Content\Html\Form\Button */
            $button = $this->createElement('button', $btn_name);
            $button->setId($btn_id);
            $button->setInner($this->txt($text));

            switch ($btn) {

                case 'submit':

                    $button->addCss('btn-primary');

                    switch ($this->send_mode) {

                        case 'submit':
                            $button->setType('submit')->addAttribute('form', $this->getId());
                            break;

                        case 'ajax':
                            $button->addData('ajax', 'form')->addData('form', $this->getId());
                            break;
                    }

                    break;

                case 'reset':
                    $button->setType('reset')->setInner($this->txt('btn_reset'));
                    break;
            }

            if (isset($this->icons[$btn])) {
                $button->useIcon($this->icons[$btn]);
            }
        }

        // Open group left? Close if it is so.
        $this->closeGroup();

        $tabindex = 0;

        foreach ($this->controls as $control_field => $control) {

            // No object and no Group? Next please!
            if (! is_object($control)) {
                if ($control != 'close_group') {
                    continue;
                }
            }

            if ($control instanceof Group) {

                $control->setId($this->id . '-group-' . $control->getId());
                $this->group = $control;
                $this->group_open = true;

                continue;
            }

            // No object but Group?
            if ($control == 'close_group') {

                $html_control .= $this->group->build();
                $this->group_open = false;

                continue;
            }

            // Is the control a ui button and the mode is ajax?
            if ($control instanceof UiButton) {

                if ($control->getMode() == 'ajax') {

                    $control->setForm($this->getId());

                    if (isset($this->route)) {
                        $control->urlsetNamedRoute($this->route);
                    } else {
                        $control->urlsetAction($this->getAttribute('action'));
                    }
                }

                $html_control .= $control->build();
            }

            // No ajax button. Normal form control.
            elseif ($control instanceof FormElementAbstract) {

                // Only visible fields get a tabindex
                if (! $control instanceof Input || ($control instanceof Input && $control->getType() !== 'hidden')) {
                    $control->addAttribute('tabindex', $tabindex);
                    $tabindex ++;
                }

                // What type of control do we have to handle?
                $type = $control->getData('control');

                // Create the control name
                $field_name = $this->uncamelizeString($control->isBound() ? $control->getField() : $control->getName());

                // Create control name app[app][model][existing name]
                if (method_exists($control, 'setName')) {

                    // Remove button name?
                    if (! $control instanceof Button || ($control instanceof Button && $this->use_button_values == true)) {
                        $name = ($type == 'input' && $control->getType() == 'file') ? 'files' : $control_name_prefix . '[' . $field_name . ']';
                        $control->setName($name);
                    } else {
                        $control->removeName();
                    }
                }

                // create control id {app}_{model}_{existing id}
                $control->setId(str_replace('_', '-', $control_id_prefix . '-' . $field_name));

                // Set BS group class
                switch ($type) {
                    case 'radio':
                        $container = '<div class="radio{state}"><label>{control}{label}{help}</label></div>';
                        break;

                    case 'checkbox':
                        $container = '<div class="checkbox{state}"><label>{control}{label}{help}</label></div>';
                        break;

                    case 'button':
                    case 'hidden':
                        $container = '{control}';
                        break;

                    default:
                        $container = '<div class="form-group{state}">{label}{control}{help}</div>';
                        $control->addCss('form-control');
                        break;
                }

                // Preset the control data if there are model fields and values
                if ($control->isBound() && isset($this->container[$field_name])) {

                    if (method_exists($control, 'setValue')) {
                        $control->setValue($this->container[$field_name]);
                    }

                    if (method_exists($control, 'setInner')) {
                        $control->setInner($this->container[$field_name]);
                    }

                    // The following controls do not like empty strings as value
                    if ($this->container[$field_name] !== '') {

                        // Seclcte control
                        if (method_exists($control, 'setSelectedValue')) {
                            $control->setSelectedValue($this->container[$field_name]);
                        }

                        // Checkbox control
                        if ($type == 'checkbox' && $this->container[$field_name] == $control->getValue()) {
                            $control->isChecked(1);
                        }

                        // Switch control
                        if (method_exists($control, 'switchOn')) {
                            $control->switchTo($this->container[$field_name]);
                        }
                    }
                }

                // Set the form id to editor controls
                if ($type == 'editor') {
                    $control->setFormId($this->getId());
                }

                // Hidden controls dont need any label or other stuff to display
                if ($type == 'hidden') {
                    $html_control .= $control->build();
                    continue;
                }

                // Set working state for fields to nothing
                $state = '';

                // Add possible validation errors css to label and control
                $field_errors = $this->container->getErrors($field_name);

                if ($field_errors) {
                    $control->addData('error', implode('<br>', $field_errors));
                    $state = ' has-error';
                    $container = str_replace('{help}', '{help}<div class="small text-danger">' . implode('<br>', $field_errors) . '</div>', $container);
                }

                // Insert gropupstate
                $container = str_replace('{state}', $state, $container);

                // Add possible label
                if ($control->hasLabel() && ! $control instanceof Checkbox) {

                    // Try to find a suitable text as label in our languagefiles
                    if (! $control->getLabel()) {
                        $control->setLabel($this->txt($this->control_name . '_' . $this->uncamelizeString($control_field), $this->app_name));
                    }

                    // Attach to control id
                    $control->label->setFor($control->getId());

                    // Make it a BS control label
                    $control->label->addCss('control-label');

                    // Horizontal forms needs grid size for label
                    if ($this->display_mode == 'h') {
                        $control->label->addCss('col-sm-4');
                    }

                    $label = $control->label->build();

                } elseif ($control instanceof Checkbox) {

                    $label = $control->getLabel();

                    // Checkboxes are wrapped by label tags, so we need only the text
                    if (! $label) {
                        $label = $control->setLabel($this->txt($this->control_name . '_' . $this->uncamelizeString($control_field), $this->app_name));
                    }

                } else {
                    $label = '';
                }

                // Insert label into controlcontainer
                $container = str_replace('{label}', $label, $container);

                // Build possible description
                $help = $control->hasDescription() ? '<span class="help-block">' . $control->getDescription() . '</span>' : '';

                // Insert description into controlcontainer
                $container = str_replace('{help}', $help, $container);

                // Insert dom id of related control for checkbox and radio labels
                if ($type == 'checkbox' || $type == 'radio') {
                    $container = str_replace('{id}', $control->getId(), $container);
                }

                // Add max file size field before file input field
                if ($control instanceof Input && $control->getType() == 'file') {

                    // Get maximum filesize for uploads
                    $max_file_size = $this->di['core.io.file']->getMaximumFileUploadSize();

                    $max_size_field = $this->factory->create('Form\Input');
                    $max_size_field->setType('hidden');
                    $max_size_field->setValue($max_file_size);

                    $container = str_replace('{control}', $max_size_field->build() . '{control}', $container);
                }

                // Add hidden field to compare posted value with previous value.
                if ($control->hasCompare()) {

                    $compare_name = str_replace($field_name, $field_name . '_compare', $control->getName());

                    $compare_control = $this->factory->create('Form\Input');
                    $compare_control->setName($compare_name);
                    $compare_control->setType('hidden');
                    $compare_control->setValue($control->getCompare());
                    $compare_control->setId($control->getId() . '_compare');

                    $container = str_replace('{control}', '{control}' . $compare_control->build(), $container);
                }

                // Build control
                $control_html = $this->display_mode == 'h' && $control->hasLabel() ? '<div class="col-sm-8">' . $control->build() . '</div>' : $control->build();

                if ($control->hasElementWidth()) {
                    $container = '<div class="' . $control->getElementWidth() . '">' . $container . '</div>';
                }

                $html = str_replace('{control}', $control_html, $container);

                if ($this->group_open) {
                    $this->group->addContent($html);
                } else {
                    $html_control .= $html;
                }
            } else {

                $html = $control->build();

                if ($this->group_open) {
                    $this->group->addContent($html);
                } else {
                    $html_control .= $html;
                }
            }
        }

        $this->setInner($html_control);

        return parent::build();
    }

    private function hasContainer()
    {
        return isset($this->container);
    }

    /**
     * Sets the forms display mode to horizontal
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function isHorizontal()
    {
        $this->display_mode = 'h';

        return $this;
    }

    /**
     * Sets the forms display mode to vertical
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function isVertical()
    {
        $this->display_mode = 'v';

        return $this;
    }

    /**
     * Sets the forms display mode to inline
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function isInline()
    {
        $this->display_mode = 'i';

        return $this;
    }

    /**
     * Wrapper method for setSendMode('ajax')
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function isAjax()
    {
        $this->setSendMode('ajax');

        return $this;
    }

    /**
     * Wrapper method for setSendmode('full')
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function isSubmit()
    {
        $this->setSendMode('submit');

        return $this;
    }

    /**
     * Set icon for a button
     *
     * @param string $button
     *            Name of the button the icon is related to
     * @param string $icon
     *            Name of the icon to use
     *
     * @throws Error
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesignerDesigner
     */
    public function setIcon($button, $icon)
    {
        if (! array_key_exists($button, $this->icons)) {
            Throw new \InvalidArgumentException('Form Tool: Button not ok.');
        }

        $this->icons[$button] = $icon;

        return $this;
    }

    /**
     * Remove one or all set button icons.
     *
     * @param string $button
     *            Optional Name of the button to remove. If not set, all icons will be removed.
     *
     * @throws Error
     */
    public function removeIcon($button = null)
    {
        if (isset($button) && ! in_array($button, $this->icons)) {
            Throw new \InvalidArgumentException('This button is not set in form buttonlist and cannot be removed');
        }

        if (! isset($button)) {
            $this->icons = null;
        } else {
            unset($this->icons[$button]);
        }
    }

    /**
     * Adds reset button to forms buttonlist
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function useResetButton()
    {
        $this->buttons['reset'] = 'reset';

        return $this;
    }

    /**
     * Access to the control objects of this form
     *
     * @param string $control_name
     *            The name of the control you want to access
     *
     * @throws Error
     *
     * @return multitype:
     */
    public function getControl($control_name)
    {
        if (! isset($this->controls[$control_name])) {
            Throw new \InvalidArgumentException('The requested control "' . $control_name . '" does not exist in this form.');
        }

        return $this->controls[$control_name];
    }

    public function setSaveButtonText($text)
    {
        $this->buttons['submit'] = $text;

        return $this;
    }

    /**
     * Disables the automatic button creation.
     * Good when you use an alternative like the Actionbar helper.
     */
    public function noButtons()
    {
        $this->buttons = [];

        return $this;
    }

    public function setActionRoute($route, $params = array())
    {
        // Store routename for later use
        $this->route = $route;

        // Compile route and set url as action url
        $this->attribute['action'] = $this->di->get('core.http.router')->url($route, $params);
    }
}
