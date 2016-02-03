<?php
namespace Core\Lib\Html\FormDesigner;

use Core\Lib\Data\Container;
use Core\Lib\Language\TextTrait;
use Core\Lib\Html\Form\Form;
use Core\Lib\Html\Form\Checkbox;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;
use Core\Lib\Errors\Exceptions\UnexpectedValueException;
use Core\Lib\Html\Form\Select;
use Core\Lib\Html\Form\Button;
use Core\Lib\Router\UrlTrait;
use Core\Lib\Html\Controls\OptionGroup;

/**
 * FormDesigner.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
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
     *
     * @var string
     */
    private $controller_name;

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
     * Labels grid width when using horizontal displaymode
     *
     * @var number
     */
    private $label_width = 3;

    /**
     * The gridtype used in the form Select from:
     * col-xs, col-sm (default), col-md and col-lg
     *
     * @see http://getbootstrap.com/css/#grid-options
     * @var string
     */
    private $grid_size = 'sm';

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
     * Glue to connect strings
     *
     * @var string
     */
    private $glue = '.';

    /**
     * Name of the route which creates the action url
     *
     * @var string
     */
    public $route;

    /**
     * Injects a Container object
     *
     * @param Container $container            
     *
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function attachContainer(Container $container)
    {
        $this->container = $container;
        
        return $this;
    }

    /**
     * Sets grid size type which is used in horizontal forms
     *
     * @param string $size
     *            Bootstrap Grid size type aka "xs", "sm", "md" or "lg"
     *            
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function setGridSize($grid_size)
    {
        $sizes = [
            'xs',
            'sm',
            'md',
            'lg'
        ];
        
        if (! in_array($grid_size, $sizes)) {
            Throw new InvalidArgumentException(sprintf('FormDesigner allowed grid sizes are %s. The size "%s" is not supported.', implode(', ', $sizes), $grid_size));
        }
        
        $this->grid_size = $grid_size;
        
        return $this;
    }

    /**
     * With of label when form display mode is horizontal
     *
     * @param int $label_width
     *            Value between 1 and 12 as label width
     *            
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function setLabelWidth($label_width)
    {
        if ($label_width < 1 || $label_width > 12) {
            Throw new InvalidArgumentException('FormDesigner label width needs to be a value between 1 and 12');
        }
        
        $this->label_width = $label_width;
        
        return $this;
    }

    /**
     * Sets glue which is used to connect string while creation names and IDs
     *
     * @param string $glue
     *            The glue to connect strings
     *            
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function setGlue($glue)
    {
        $this->glue = $glue;
        return $this;
    }

    /**
     * Extends the form name and id on creation with this extensiondata
     *
     * @param int|string $name_ext            
     *
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
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
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
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
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function setLabelPrefix($label_prefix)
    {
        $this->label_prefix = $label_prefix;
        
        return $this;
    }

    /**
     * Creates a FormGroup
     *
     * @param bool $unshift
     *            Optional flag to add group at beginning of controls array.
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
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function setAppName($app_name)
    {
        $this->app_name = $app_name;
        
        return $this;
    }

    /**
     * Sets name of controller to be used on element creation
     *
     * @param string $controller_name
     *            Name of the controller this data is for
     *            
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function setControllerName($control_name)
    {
        $this->controller_name = $control_name;
        
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
     * @param string $size
     *            Optional Bootstrap gridsize type aka "xs", "sm", "md" or "lg". Default: 'sm'
     *            
     * @param int $label_width
     *            Optional label with between 1 and 12. Default: 3
     *            
     *            
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function isHorizontal($grid_size = 'sm', $label_with = '3')
    {
        $this->display_mode = 'h';
        $this->setGridSize($grid_size);
        $this->setLabelWidth($label_with);
        
        return $this;
    }

    /**
     * Sets the forms display mode to vertical
     *
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function isVertical()
    {
        $this->display_mode = 'v';
        
        return $this;
    }

    /**
     * Sets the forms display mode to inline
     *
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function isInline()
    {
        $this->display_mode = 'i';
        
        return $this;
    }

    /**
     * Wrapper method for setSendMode('ajax')
     *
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function isAjax()
    {
        $this->send_mode = 'ajax';
        
        return $this;
    }

    /**
     * Wrapper method for setSendmode('full')
     *
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
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
        
        if (! isset($this->controller_name)) {
            Throw new UnexpectedValueException('The FormDesigner needs a name for the controls,', 10000);
        }
        
        $this->lower_app_name = $this->stringUncamelize($this->app_name);
        $this->controller_name = $this->stringUncamelize($this->controller_name);
        
        // Create formname
        if (! $this->name) {
            
            // Create control id prefix based on the model name
            $this->control_id_prefix = $this->controller_name;
            
            // Create form name based on model name and possible extensions
            $this->name = $this->control_name . (isset($this->name_ext) ? $this->glue . $this->name_ext : '');
        }
        else {
            // Create control id prefix based on the provided form name
            $this->control_id_prefix = $this->name;
            
            // Create form name based on th provided form name
            $this->name = $this->name . (isset($this->name_ext) ? $this->glue . $this->name_ext : '');
        }
        
        // Use formname as id when not set
        if (! $this->id) {
            $this->id = str_replace($this->glue, '-', $this->name);
        }
        
        // Create control name prefix
        $this->control_name_prefix = $this->lower_app_name . '[' . $this->controller_name . ']';
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
            
            if ($this->di->get('core.cfg')->get('Core', 'js.style.fadeout_time') > 0) {
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
     * @see \Core\Lib\Html\HtmlAbstract::build()
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
        
        // Create hidden field with unique session token
        $html .= '<input type="hidden" name="token" value="' . $this->di->get('core.http.session')->get('token') . '">';
        
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
        
        /* @var $element \Core\Lib\Html\FormDesigner\FormElement */
        foreach ($elements as $element) {
            
            $content = $element->getContent();
            
            switch ($element->getType()) {
                
                case 'control':

                    /* @var $builder \Core\Lib\Html\FormDesigner\ControlBuilder */
                    $builder = $this->di->instance(__NAMESPACE__ . '\ControlBuilder');
                    $builder->setAppName($this->app_name);
                    $builder->setNamePrefix($this->control_name_prefix);
                    $builder->setIdPrefix($this->control_id_prefix);
                    $builder->setLabelPrefix($this->label_prefix);
                    
                    // Any errors in container
                    if (isset($this->container)) {
                        
                        $name = $content->getName();
                        $value = $this->container[$name];
                        
                        if (! empty($name) && $this->container->hasErrors($name)) {
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
                            case ($content instanceof OptionGroup):
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
                    $builder->setDisplayMode($this->display_mode, $this->grid_size, $this->label_width);
                    
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
