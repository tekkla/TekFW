<?php
namespace Core\Lib\Html\FormDesigner;

use Core\Lib\Html\FormAbstract;
use Core\Lib\Html\Controls\UiButton;
use Core\Lib\Html\Form\Button;
use Core\Lib\Html\Form\Checkbox;
use Core\Lib\Html\Form\Input;
use Core\Lib\Traits\StringTrait;
use Core\Lib\Language\TextTrait;

/**
 * ControlBuilder.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class ControlBuilder
{

    use StringTrait;
    use TextTrait;

    private $app_name;

    private $control;

    private $name_prefix = '';

    private $id_prefix = '';

    private $label_prefix = '';

    private $errors = [];

    private $display_mode = 'v';

    private $label_width = 3;

    private $grid_size = 'sm';

    /**
     * Sets name of app to be used
     *
     * @param string $app_name
     *
     * @return \Core\Lib\Html\FormDesigner\ControlBuilder
     */
    public function setAppName($app_name)
    {
        $this->app_name = $app_name;

        return $this;
    }

    /**
     * Bind control
     *
     * @param FormAbstract $control
     *
     * @return \Core\Lib\Html\FormDesigner\ControlBuilder
     */
    public function setControl(FormAbstract $control)
    {
        $this->control = $control;

        return $this;
    }

    public function setNamePrefix($name_prefix)
    {
        $this->name_prefix = $name_prefix;

        return $this;
    }

    public function setIdPrefix($id_prefix)
    {
        $this->id_prefix = $id_prefix;

        return $this;
    }

    public function setLabelPrefix($label_prefix)
    {
        $this->label_prefix = $label_prefix;

        return $this;
    }

    public function setErrors(Array $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    public function setDisplayMode($display_mode, $grid_size = 'sm', $label_with = 3)
    {
        $this->display_mode = $display_mode;
        $this->grid_size = $grid_size;
        $this->label_width = $label_with;

        return $this;
    }

    public function build(FormAbstract $control = null)
    {
        if (! empty($control)) {
            $this->control = $control;
        }

        // Is the control a ui button and the mode is ajax?
        if ($this->control instanceof UiButton) {
            return $this->buildUiButton();
        }

        // No ajax button. Normal form control.
        if ($this->control instanceof FormAbstract) {
            return $this->buildFormAbstract();
        }

        return $this->control->build();
    }

    private function buildUiButton()
    {
        if ($this->control->getMode() == 'ajax') {

            $this->control->setForm($this->getId());

            if (isset($this->route)) {
                $this->control->urlsetNamedRoute($this->route);
            }
            else {
                $this->control->urlsetAction($this->getAttribute('action'));
            }
        }

        return $this->control->build();
    }

    private function buildFormAbstract()
    {
        // What type of control do we have to handle?
        $type = $this->control->getData('control');

        // Create the control name
        $field_name = $this->control->isBound() ? $this->control->getField() : $this->control->getName();

        // Create control name app[app][model][existing name]
        if (method_exists($this->control, 'setName')) {

            // Remove button name?
            if (! empty($field_name) || ! $this->control instanceof Button) {

                $field_name = $this->stringUncamelize($field_name);

                $name = ($type == 'input' && $this->control->getType() == 'file') ? 'files' : $this->name_prefix . '[' . $field_name . ']';
                $this->control->setName($name);
            }
            else {
                $this->control->removeName();
            }
        }

        // create control id {app}_{model}_{existing id}
        if (! $this->control->getId()) {
            $this->control->setId(str_replace('_', '-', $this->id_prefix . '-' . ($this->control->isBound() ? $field_name : uniqid())));
        }

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
                $container = $this->display_mode == 'h' ? '<div class="form-group{state}">{control}</div>' : '{control}';
                break;

            default:
                $container = '<div class="form-group{state}">{label}{control}' . ($this->display_mode == 'v' ? '{help}{error}' : '') . '</div>';
                $this->control->addCss('form-control');
                break;
        }

        // Hidden controls dont need any label or other stuff to display
        if ($type == 'input' && $this->control->getType() == 'hidden') {
            return $this->control->build();
        }

        // Insert groupstate
        $container = str_replace('{state}', $this->errors ? ' has-error' : '', $container);

        $label = '';

        // Add possible label
        if ($this->control->hasLabel() && ! $this->control instanceof Checkbox) {

            // Try to find a suitable text as label in our languagefiles
            if (! $this->control->getLabel()) {
                $this->control->setLabel($this->text($this->label_prefix . $field_name, $this->app_name));
            }

            // Attach to control id
            $this->control->label->setFor($this->control->getId());

            // Make it a BS control label
            $this->control->label->addCss('control-label');

            // Horizontal forms needs grid size for label
            if ($this->display_mode == 'h') {
                $this->control->label->addCss('col-' . $this->grid_size . '-' . $this->label_width);
            }

            $label = $this->control->label->build();
        }
        elseif ($this->control instanceof Checkbox) {

            $label = $this->control->getLabel();

            // Checkboxes are wrapped by label tags, so we need only the text
            if (empty($label)) {
                $label = $this->text($this->label_prefix . $field_name, $this->app_name);
            }
        }

        // Insert label into controlcontainer
        $container = str_replace('{label}', $label, $container);

        // Insert dom id of related control for checkbox and radio labels
        if ($type == 'checkbox' || $type == 'radio') {
            $container = str_replace('{id}', $this->control->getId(), $container);
        }

        // Add max file size field before file input field
        if ($this->control instanceof Input && $this->control->getType() == 'file') {

            // Get maximum filesize for uploads
            $max_file_size = $this->di->get('core.io.file')->getMaximumFileUploadSize();

            $max_size_field = $this->factory->create('Form\Input');
            $max_size_field->setType('hidden');
            $max_size_field->setValue($max_file_size);

            $container = str_replace('{control}', $max_size_field->build() . '{control}', $container);
        }

        // Add hidden field to compare posted value with previous value.
        if ($this->control->hasCompare()) {

            $compare_name = str_replace($field_name, $field_name . '_compare', $this->control->getName());

            $compare_control = $this->factory->create('Form\Input');
            $compare_control->setName($compare_name);
            $compare_control->setType('hidden');
            $compare_control->setValue($this->control->getCompare());
            $compare_control->setId($this->control->getId() . '_compare');

            $container = str_replace('{control}', '{control}' . $compare_control->build(), $container);
        }

        // Add possible validation errors css to control
        if ($this->errors) {
            $this->control->addData('error', implode('<br>', $this->errors));
        }

        // Build control
        $html = '';

        if ($this->display_mode == 'h') {
            $control = '<div class="' . (! $this->control->hasLabel() ? 'col-' . $this->grid_size . '-offset-3 ' : '') . 'col-' . $this->grid_size . '-' . (12 - $this->label_width) . '">' . $this->control->build() . '{help}{error}</div>';
        }
        else {
            $control = $this->control->build();
        }

        if ($this->control->hasElementWidth()) {
            $container = '<div class="' . $this->control->getElementWidth() . '">' . $container . '</div>';
        }

        // Inserc built control in container
        $container = str_replace('{control}', $control, $container);

        // Build possible description
        $help = $this->control->hasDescription() ? '<div class="help-block">' . $this->control->getDescription() . '</div>' : '';

        // Insert description into controlcontainer
        $container = str_replace('{help}', $help, $container);

        // Add possible validation errors css to label and control
        $error = $this->errors ? '<div class="small text-danger">' . implode('<br>', $this->errors) . '</div>' : '';

        // Insert errors into container
        $container = str_replace('{error}', $error, $container);

        return $container;
    }
}

