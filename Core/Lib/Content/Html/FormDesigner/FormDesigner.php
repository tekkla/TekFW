<?php
namespace Core\Lib\Content\Html\FormDesigner;

use Core\Lib\Data\Container;
use Core\Lib\Traits\StringTrait;
use Core\Lib\Traits\TextTrait;
use Core\Lib\Traits\AnalyzeVarTrait;
use Core\Lib\Content\Html\Form\Form;
use Core\Lib\Content\Html\Form\Option;
use Core\Lib\Content\Html\Form\Checkbox;

/**
 * FormDesigner
 *
 * @todo Write explanation... or an app which explains the basics
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */
final class FormDesigner extends Form
{
    use StringTrait;
    use TextTrait;
    use AnalyzeVarTrait;

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
    private $buttons = array(
        'submit' => 'save'
    );

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
     * @param string $button Name of the button the icon is related to
     * @param string $icon Name of the icon to use
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
     * @param string $button Optional Name of the button to remove. If not set, all icons will be removed.
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
        }
        else {
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
        $this->no_buttons = true;

        return $this;
    }

    public function setActionRoute($route, $params = array())
    {
        // Store routename for later use
        $this->route = $route;

        // Compile route and set url as action url
        $this->attribute['action'] = $this->di->get('core.http.router')->url($route, $params);
    }

    /**
     * Creates prefixes for controlnames/-ids used within the form which is created by FormDesigner.
     *
     * @throws \RuntimeException
     */
    private function createNames()
    {
        // manual forms need a set app and model name
        if (! isset($this->app_name)) {
            Throw new \RuntimeException('The FormDesigner needs a name of a related app.', 10000);
        }

        if (! isset($this->control_name)) {
            Throw new \RuntimeException('The FormDesigner needs a name for the controls,', 10000);
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
     * Handles optional buttons like 'submit' or 'reset'
     *
     * @todo Check namecreation process an find a wayto use buttons for values!
     */
    private function handleButtons()
    {
        if (! $this->no_buttons && ! empty($this->buttons)) {

            /* @var $group \Core\Lib\Content\Html\FormDesigner\FormGroup */
            $group = $this->addGroup();

            // Create form buttons
            foreach ($this->buttons as $btn => $text) {

                $btn_name = 'btn_' . $btn;
                $btn_id = 'btn-' . str_replace('_', '-', $btn);

                /* @var $button \Core\Lib\Content\Html\FormDesigner\Controls\ButtonControl */
                $button = $group->addControl('Button', '', false);
                $button->setName($btn_name);
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
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Content\Html\HtmlAbstract::build()
     */
    public function build()
    {
        if (empty($this->groups)) {
            Throw new \RuntimeException('Your form has no groups to show. Add groups and try again.');
        }

        // Create needed IDs and control names
        $this->createNames();

        // Handle global container errors
        $this->handleGlobalContainerErrors();

        // Handle display mode
        $this->handleDisplayMode();

        // Handle buttons
        $this->handleButtons();

        // Control html container
        $html = '';

        foreach ($this->groups as $group) {
            $html .= $this->buildGroup($group);
        }

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

                        if ($this->container->hasErrors($content->getName())) {
                            $builder->setErrors($this->container->getErrors($content->getName()));
                        }

                        // Is control checkable (checkbox eg option)?
                        if ($content instanceof Checkbox || $content instanceof  Option) {

                            // Set control checked when it's value = container field value
                            if ($content->getValue() == $this->container[$content->getName()]) {
                                $content->addAttribute('checked');
                            }
                        }

                        // Try to get value from container when control has no content set
                        elseif ($content->getValue() === null && $this->container[$content->getName()]) {
                           $content->setValue($this->container[$content->getName()]);
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
                    $html .= $this->buildGroups($content);
                    break;
            }
        }

        return $html;
    }
}
