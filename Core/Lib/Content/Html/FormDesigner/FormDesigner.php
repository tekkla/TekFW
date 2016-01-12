<?php
namespace Core\Lib\Content\Html\FormDesigner;

use Core\Lib\Data\Container;
use Core\Lib\Traits\TextTrait;
use Core\Lib\Content\Html\Form\Form;
use Core\Lib\Content\Html\Form\Checkbox;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;
use Core\Lib\Errors\Exceptions\UnexpectedValueException;
use Core\Lib\Content\Html\Form\Select;
use Core\Lib\Content\Html\Form\Button;
use Core\Lib\Traits\UrlTrait;

/**
 * FormDesigner.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class FormDesigner extends Form
{
    use TextTrait;
    use UrlTrait;

    /**
     * Forms group storage
     *
     * @var array
     */
    private $groups = [];

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
     *
     * @var string
     */
    private $control_name_prefix = '';

    /**
     *
     * @var string
     */
    private $label_prefix = '';

    /**
     *
     * @var string
     */
    private $control_id_prefix;

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
    private $buttons = [
        'submit' => 'save'
    ];

    /**
     * Icons for buttons
     *
     * @var array
     */
    private $icons = [
        'submit' => 'save',
        'reset' => 'eraser'
    ];

    /**
     * Associated data container
     *
     * @var Container
     */
    private $container;

    /**
     * Name of the route which creates the action url
     *
     * @var string
     */
    public $route;

    private $grid_label;

    private $grid_control;

    /**
     *
     * @var boolean
     */
    private $no_buttons = false;

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
     * @param string $send_mode Send mode which can be 'ajax' or 'submit'
     *
     * @throws InvalidArgumentException
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
            Throw new InvalidArgumentException(sprintf('"%s" is not an allowed form sendmode.', $send_mode), 1000);
        }

        $this->send_mode = $send_mode;

        return $this;
    }

    /**
     * Set prefix tp be used when creating label for a control.
     * Useful for categorized language strings.
     *
     * @param string $label_prefix
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function setLabelPrefix($label_prefix)
    {
        $this->label_prefix = $label_prefix;

        return $this;
    }

    /**
     * Creates a FormGroup
     *
     * @param bool $unshift Optional flag to add group at beginning of controls array.
     *
     * @return FormGroup
     */
    public function &addGroup($unshift = false)
    {
        $group = $this->di->instance(__NAMESPACE__ . '\FormGroup');

        if ($unshift == true) {
            array_unshift($this->groups, $group);
        }
        else {
            $this->groups[] = $group;
        }

        return $group;
    }

    /**
     * Sets name of app to be used on element creation.
     *
     * @param sting $app_name
     *
     * @return \Core\Lib\Content\Html\FormDesigner\FormDesigner
     */
    public function setAppName($app_name)
    {
        $this->app_name = $app_name;

        return $this;
    }

    /**
     * Sets name of controller to be used on element creation
     *
     * @param string $control_name
     *
     * @return \Core\Lib\Content\Html\FormDesigner\FormDesigner
     */
    public function setControlName($control_name)
    {
        $this->control_name = $control_name;

        return $this;
    }

    /**
     * Checks for a set container object
     *
     * @return bool
     */
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
        $this->send_mode = 'ajax';

        return $this;
    }

    /**
     * Wrapper method for setSendmode('full')
     *
     * @return \Core\Lib\Content\Html\Elements\FormDesigner
     */
    public function isSubmit()
    {
        $this->send_mode = 'submit';

        return $this;
    }

    /**
     * Returns forms send mode.
     *
     * @return string
     */
    public function getSendMode()
    {
        return $this->send_mode;
    }

    public function setActionRoute($route, $params = array())
    {
        // Store routename for later use
        $this->route = $route;

        // Compile route and set url as action url
        $this->attribute['action'] = $this->url($route, $params);
    }

    /**
     * Creates prefixes for controlnames/-ids used within the form which is created by FormDesigner.
     *
     * @throws UnexpectedValueException
     */
    private function createNames()
    {
        // manual forms need a set app and model name
        if (! isset($this->app_name)) {
            Throw new UnexpectedValueException('The FormDesigner needs a name of a related app.', 10000);
        }

        if (! isset($this->control_name)) {
            Throw new UnexpectedValueException('The FormDesigner needs a name for the controls,', 10000);
        }

        $base_form_name = 'appform';

        $this->lower_app_name = $this->uncamelizeString($this->app_name);
        $this->control_name = $this->uncamelizeString($this->control_name);

        // Create formname
        if (! $this->name) {

            // Create control id prefix based on the model name
            $this->control_id_prefix = '' . $this->lower_app_name . '_' . $this->control_name;

            // Create form name based on model name and possible extensions
            $this->name = $base_form_name . '_' . $this->lower_app_name . '_' . $this->control_name . (isset($this->name_ext) ? '_' . $this->name_ext : '');
        }
        else {
            // Create control id prefix based on the provided form name
            $this->control_id_prefix = '' . $this->lower_app_name . '_' . $this->name;

            // Create form name based on th provided form name
            $this->name = $base_form_name . $this->lower_app_name . '_' . $this->name . (isset($this->name_ext) ? '_' . $this->name_ext : '');
        }

        // Use formname as id when not set
        if (! $this->id) {
            $this->id = str_replace('_', '-', $this->name);
        }

        // Create control name prefix
        $this->control_name_prefix = $this->lower_app_name . '[' . $this->control_name . ']';
    }

    /**
     * Checks a possible used Container object for global errors "@" and adds a new FormGoup object on top of the groups stack.
     */
    private function handleGlobalContainerErrors()
    {
        // Are there global and not control related errors in attached container?
        if ($this->hasContainer() && $this->container->hasErrors('@')) {

            // Add a group on top of exiting groups
            $group = $this->addGroup(true);

            $div = $group->addElement('Elements\Div');
            $div->addCss('alert alert-danger alert-dismissable');

            if ($this->di->get('core.cfg')->get('Core', 'js_fadeout_time') > 0) {
                $div->addCss('fadeout');
            }

            $html = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . implode('<br>', $this->container->getErrors('@'));

            $div->setInner($html);
        }
    }

    /**
     * Handles display mode by setting the matching css class.
     *
     * @see http://getbootstrap.com/css/#forms
     */
    private function handleDisplayMode()
    {
        switch ($this->display_mode) {
            case 'h':
                $this->addCss('form-horizontal');
                break;
            case 'i':
                $this->addCss('form-inline');
                break;
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Content\Html\HtmlAbstract::build()
     *
     * @throws UnexpectedValueException
     */
    public function build()
    {
        if (empty($this->groups)) {
            Throw new UnexpectedValueException('Your form has no groups to show. Add groups and try again.');
        }

        // Create needed IDs and control names
        $this->createNames();

        // Handle global container errors
        $this->handleGlobalContainerErrors();

        // Handle display mode
        $this->handleDisplayMode();

        // Control html container
        $html = '';

        foreach ($this->groups as $group) {
            $html .= $this->buildGroup($group);
        }

        // Create hidden field with unique session token
        $html .= '<input type="hidden" name="token" value="' . $this->di->get('core.http.session')->get('token') . '">';

        $this->setInner($html);

        return parent::build();
    }

    /**
     * Recursivable method to build the created groups and the controls stroed as FormElement objects within each FormGroup object.
     *
     * @param FormGroup $group
     *
     * @return string
     */
    private function buildGroup(FormGroup $group)
    {
        $html = '';

        $elements = $group->getElements();

        /* @var $element \Core\Lib\Content\Html\FormDesigner\FormElement */
        foreach ($elements as $element) {

            $content = $element->getContent();

            switch ($element->getType()) {

                case 'control':

                    /* @var $builder \Core\Lib\Content\Html\FormDesigner\ControlBuilder */
                    $builder = $this->di->instance(__NAMESPACE__ . '\ControlBuilder');
                    $builder->setAppName($this->app_name);
                    $builder->setNamePrefix($this->control_name_prefix);
                    $builder->setIdPrefix($this->control_id_prefix);
                    $builder->setLabelPrefix($this->label_prefix);

                    // Any errors in container
                    if (isset($this->container)) {

                        $name = $content->getName();
                        $value = $this->container[$name];

                        if (!empty($name) && $this->container->hasErrors($name)) {
                            $builder->setErrors($this->container->getErrors($name));
                        }

                        // Log notice when field does not exist in container
                        if ($value === false && $content->isBound()) {
                            Throw new FormDesignerException(sprintf('The control "%s" is unbound because no field with this name was found in container bount to FormDesigner. If you want to use a control without binding it to a container field, you have to use the "notBound()" method for this control.', $name));
                        }

                        switch (true) {
                            case ($content instanceof Checkbox):
                                if ($content->getValue() == $value) {
                                    $content->isChecked();
                                }
                                break;

                            case ($content instanceof Select):
                                if (empty($content->getValue()) && $value !== false) {
                                    $content->setValue($value);
                                }
                                break;
                            default:
                                if ($content->getValue() == false && $value !== false) {
                                    $content->setValue($value);
                                }
                                break;
                        }
                    }

                    // Handle buttons
                    if ($content instanceof Button) {

                        // Add needed ajax data attribute
                        if ($this->getSendMode() == 'ajax') {
                            $content->addData('ajax');
                        }

                        // Submit buttons need the id and action of and for the form to submit
                        if ($content->isSubmit()) {
                            $content->setFormId($this->getId());
                            $content->setFormAction($this->getAttribute('action'));
                        }
                    }

                    $builder->setControl($content);
                    $builder->setDisplayMode($this->display_mode);

                    // Build control
                    $html .= $builder->build();

                    break;

                case 'factory':
                    $html .= $content->build();
                    break;

                case 'html':
                    $html .= $content;
                    break;

                case 'group':
                    $html .= $this->buildGroup($content);
                    break;
            }
        }

        $group->setInner($group->getInner() . $html);

        return $group->build();
    }
}
