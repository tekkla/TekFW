<?php
namespace Core\Lib\Html\FormDesigner;

use Core\Lib\Data\Container\Container;
use Core\Lib\Language\TextTrait;
use Core\Lib\Html\Form\Form;
use Core\Lib\Html\Form\Checkbox;
use Core\Lib\Html\Form\Select;
use Core\Lib\Html\Form\Button;
use Core\Lib\Router\UrlTrait;
use Core\Lib\Html\FormAbstract;

/**
 * FormDesigner.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class FormDesigner
{
    use TextTrait;
    use UrlTrait;

    /**
     *
     * @var array
     */
    private static $used_ids = [];

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
     *
     * @var array
     */
    private $data = [];

    private $errors = [];

    /**
     *
     * @var Form
     */
    public $html;

    public function __construct()
    {
        $this->html = new Form();
    }

    public function mapData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function mapErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Lib\Html\HtmlAbstract::setId()
     */
    public function setId($id)
    {
        if (in_array($id, self::$used_ids)) {
            Throw new FormDesignerException(sprintf('The form id "%s" is already in use. Please set a different/unique form id.', $id));
        }

        self::$used_ids[] = $id;

        $this->html->setId($id);

        return $this;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Lib\Html\HtmlAbstract::setId()
     */
    public function setName($name)
    {
        $this->html->setName($name);

        return $this;
    }

    /**
     * Sets grid size type which is used in horizontal forms
     *
     * @param string $size
     *            Bootstrap Grid size type aka "xs", "sm", "md" or "lg"
     *
     * @throws FormDesignerException
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
            Throw new FormDesignerException(sprintf('FormDesigner allowed grid sizes are %s. The size "%s" is not supported.', implode(', ', $sizes), $grid_size));
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
     * @throws FormDesignerException
     *
     * @return \Core\Lib\Html\FormDesigner\FormDesigner
     */
    public function setLabelWidth($label_width)
    {
        if ($label_width < 1 || $label_width > 12) {
            Throw new FormDesignerException('FormDesigner label width needs to be a value between 1 and 12');
        }

        $this->label_width = $label_width;

        return $this;
    }

    /**
     * Set the sendmode for the form.
     * You can use the html submit or the frameworks ajax system. Default: 'submit'
     *
     * @param string $send_mode
     *            Send mode which can be 'ajax' or 'submit'
     *
     * @throws FormDesignerException
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
            Throw new FormDesignerException(sprintf('"%s" is not an allowed form sendmode.', $send_mode), 1000);
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

        $group->injectFormDesigner($this);

        return $group;
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

    /**
     * Checks a possible used Container object for global errors "@" and adds a new FormGoup object on top of the groups stack.
     */
    private function handleGlobalContainerErrors()
    {
        $form_id = $this->html->getId();

        // No data of form with our id in session stored?
        if (! isset($_SESSION['formdesigner'][$form_id])) {
            return;
        }

        // Are there global and not control related errors in attached container?
        if (isset($_SESSION['formdesigner'][$form_id]['errors']['@'])) {

            // Add a group on top of exiting groups
            $group = $this->addGroup(true);

            $div = $group->addElement('Elements\Div');
            $div->addCss('alert alert-danger alert-dismissable');

            if ($this->di->get('core.cfg')->get('Core', 'js.style.fadeout_time') > 0) {
                $div->addCss('fadeout');
            }

            $html = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . implode('<br>', $_SESSION['formdesigner'][$form_id]['errors']['@']);

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
                $this->html->addCss('form-horizontal');
                break;
            case 'i':
                $this->html->addCss('form-inline');
                break;
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Html\HtmlAbstract::build()
     *
     * @throws FormDesignerException
     */
    public function build()
    {
        if (empty($this->groups)) {
            Throw new FormDesignerException('Your form has no groups to show. Add groups and try again.');
        }

        if (! $this->html->getId()) {
            Throw new FormDesignerException('There is no unique form id set. Each FormDesigner form needs it\'s unique id');
        }

        // Handle global container errors
        $this->handleGlobalContainerErrors();

        // Handle display mode
        $this->handleDisplayMode();

        // Control html container
        $html = '';

        // Create hidden field with unique session token
        $html .= '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';

        foreach ($this->groups as $group) {
            $html .= $this->buildGroup($group);
        }

        $this->html->addInner($html);

        return $this->html->build();
    }

    /**
     * Recursivable method to build the created groups and the controls stroed as FormElement objects within each FormGroup object.
     *
     * @param FormGroup $group
     *
     * @return string
     */
    private function buildGroup(FormGroup $group, array $names = [])
    {
        $html = '';

        $elements = $group->getElements();

        // Get groupname
        $group_name = $group->getName();

        // If group has a name, add it to the names array
        if (!empty($group_name)) {
            $names[] = $group_name;
        }

        /* @var $element \Core\Lib\Html\FormDesigner\FormElement */
        foreach ($elements as $element) {

            $content = $element->getContent();

            switch ($element->getType()) {

                case 'control':

                    /* @var $builder \Core\Lib\Html\FormDesigner\ControlBuilder */
                    $builder = $this->di->instance(__NAMESPACE__ . '\ControlBuilder');

                    // Control field name
                    $name = $content->getName();

                    // Create name parts based on current group names
                    $pieces = array_map(function($name) {
                        return '[' . $name . ']';
                    }, $names);

                    $pieces[] = '[' . $name . ']';

                    $content->setName('core' . implode('', $pieces));

                    // Any errors in container
                    if (isset($this->data[$name])) {

                        // Get value
                        $value = $this->data[$name];


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

                            default:
                                if (empty($content->getValue()) && $value !== false && method_exists($content, 'setValue')) {
                                    $content->setValue($value);
                                }
                                break;
;
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
                            $content->setFormId($this->html->getId());
                            $content->setFormAction($this->html->getAttribute('action'));
                            $content->setFormMethod('post');
                        }
                    }

                    $builder->setControl($content);
                    $builder->setDisplayMode($this->display_mode, $this->grid_size, $this->label_width);

                    // Build control
                    $html .= $builder->build();

                    break;

                case 'collection':

                    /* @var $builder \Core\Lib\Html\FormDesigner\ControlBuilder */
                    $builder = $this->di->instance(__NAMESPACE__ . '\ControlBuilder');

                    foreach ($content->getControls() as $control) {

                        if ($control instanceof FormAbstract) {
                            $builder->setControl($control);
                            $content->addInner($builder->build());
                        }
                        else {
                            $content->addInner($control->build());
                        }
                    }

                    $content->clearControls();

                    $html .= $content->build();

                    break;

                case 'factory':
                    $html .= $content->build();
                    break;

                case 'html':
                    $html .= $content;
                    break;

                case 'group':
                    $html .= $this->buildGroup($content, $names);
                    break;
            }
        }

        return $group->html->addInner($html)->build();
    }
}
