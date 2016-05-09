<?php
namespace Core\Html\Controls\Editbox;

use Core\Html\Form\Form;
use Core\Html\FormDesigner\FormDesigner;
use Core\Html\Bootstrap\Panel\Panel;
use Core\Html\Elements\A;
use Core\Html\HtmlBuildableInterface;
use Core\Html\HtmlException;

/**
 * Editbox.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Editbox implements HtmlBuildableInterface
{

    /**
     * Caption of panel / editbox
     *
     * @var string
     */
    private $caption = 'Editbox';

    /**
     * Description text
     *
     * @var string
     */
    private $description = '';

    /**
     *
     * @var unknown
     */
    private $form;

    /**
     * Ajax flag
     *
     * @var boolean
     */
    private $is_ajax = false;

    /**
     * Actions array stack
     *
     * @var array
     */
    private $actions = [];

    /**
     * String to use as text on context button
     *
     * @var string
     */
    private $context_text = '';

    /**
     * Bootstrap Panel component of editbox
     *
     * @var \Core\Html\Bootstrap\Panel\Panel
     */
    public $panel;

    public function __construct()
    {
        $this->panel = new Panel();
    }

    /**
     * Sets the DOMID of form to save.
     *
     * @param string $form
     *            DomID of form
     *
     * @return \Core\Html\Controls\Editbox
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Sets text for actions button.
     *
     * @param string $text
     *            Text to show on button
     *
     * @return \Core\Html\Controls\Editbox
     */
    public function setContextText($text)
    {
        $this->actions_text = $text;

        return $this;
    }

    /**
     * Sets ajax flag when parameter $ajax is set.
     * Otherwise will returns current ajax flag status.
     *
     * @param string $ajax
     *
     * @return boolean
     */
    public function isAjax($ajax = null)
    {
        if (isset($ajax)) {
            $this->is_ajax = (bool) $ajax;
        }
        else {
            return $this->is_ajax;
        }
    }

    /**
     * Set caption text.
     *
     * @param string $caption
     *
     * @return \Core\Html\Controls\Editbox
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Set description text.
     *
     * @param string $caption
     *
     * @return \Core\Html\Controls\Editbox
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Returns current caption text.
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Adds an action link to actions list
     *
     * @param Action $action
     */
    public function addAction(Action $action)
    {
        if ($action->getType() == Action::CONTEXT) {

            if (empty($this->actions['context'])) {
                $this->actions['context'] = [];
            }

            $this->actions['context'][] = $action;
        }
        else {
            $this->actions[$action->getType()] = $action;
        }
    }

    /**
     * Creates an action object, adds it to the actionslist and returns a reference to it
     *
     * @param string $type
     * @param string $text
     * @param string $href
     * @param string $ajax
     * @param string $confirm
     *
     * @return Action
     */
    public function &createAction($type = Action::CONTEXT, $text, $href = '', $ajax = false, $confirm = '')
    {
        $action = new Action();

        $action->setText($text);
        $action->setHref($href);
        $action->setAjax((bool) $ajax);
        $action->setConfirm('confirm');

        $this->addAction($action);

        return $action;
    }

    /**
     * Generate actions from an array of action definitions
     *
     * @param array $actions
     */
    public function generateActions(array $actions)
    {
        foreach ($actions as $action) {

            $action_object = new Action();

            if (empty($action['type'])) {
                $action['type'] = 'context';
            }

            $action_object->setType($action['type']);

            if (! empty($action['text'])) {
                $action_object->setText($action['text']);
            }

            if (! empty($action['href'])) {
                $action_object->setHref($action['href']);
            }

            if (isset($action['ajax'])) {
                $action_object->setAjax($action['ajax']);
            }

            if (! empty($action['icon'])) {
                $action_object->setIcon($action['icon']);
            }

            if (! empty($action['confirm'])) {
                $action_object->setConfirm($action['confirm']);
            }

            $this->addAction($action_object);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Html\Bootstrap\Panel\Panel::build()
     *
     * @throws HtmlException
     */
    public function build()
    {
        if (empty($this->form)) {
            Throw new HtmlException('Editbox control needs a set FormDesigner or Form object.');
        }

        // Editbox CSS class needed
        $this->panel->html->addCss('editbox');

        // Create panel heading with title
        $heading = $this->panel->createHeading();

        $title = $heading->createTitle();
        $title->setTitle($this->caption);

        if (! empty($this->description)) {
            $title->setDescription($this->description, true);
        }

        $heading->addContent($title);

        // Create panel body

        if ($this->form instanceof FormDesigner) {
            $form_id = $this->form->html->getId();
            $form_action = $this->form->html->getAttribute('action');

            // Ajax form?
            $this->is_ajax = $this->form->getSendMode() == 'ajax' ? true : false;
        }
        else {
            $form_id = $this->form->getId();
            $form_action = $this->form->getAttribute('action');
        }

        // Build form here because it's possible that form id will be autogenerated
        // and we need this id in the next step.
        $body = $this->panel->createBody();
        $body->addContent($this->form);

        // Footer with toolbar

        /* @var $toolbar \Core\Html\Bootstrap\Buttongroups\ButtonToolbar */
        $toolbar = $this->factory->create('Bootstrap\Buttongroups\ButtonToolbar');

        $create_icon = function ($icon_name) {

            /* @var $icon \Core\Html\Elements\Icon */
            $icon = $this->factory->create('Elements\Icon');
            $icon->setIcon($icon_name);

            return $icon->build();
        };

        $create_action = function (Action $action, $with_link = false) use ($create_icon) {

            $text = $action->getText();
            $icon = $action->getIcon();

            if (! empty($icon)) {
                $text = $create_icon($icon) . ' ' . $text;
            }

            if ($with_link) {

                $href = $action->getHref();

                if (! empty($href)) {

                    /* @var $a \Core\Html\Elements\A */
                    $a = $this->factory->create('Elements\A');

                    $a->setHref($href);

                    if ($action->getAjax()) {
                        $a->addData('ajax');
                    }

                    if ($action->getConfirm()) {
                        $a->addData('confirm', $action->getConfirm());
                    }

                    $a->setInner($text);

                    $text = $a->build();
                }
            }
            return $text;
        };

        foreach ($this->actions as $type => $action) {

            /* @var $group \Core\Html\Bootstrap\Buttongroups\ButtonGroup */
            $group = $toolbar->createButtongroup();
            $group->addCss('btn-group-sm');

            switch ($type) {
                case 'save':
                case 'cancel':

                    switch ($type) {
                        case 'save':
                            $button = $group->createButton();
                            $button->setFormId($form_id);
                            $button->setFormAction($form_action);
                            $button->isSuccess();

                            if ($this->is_ajax) {
                                $button->addData('ajax');
                            }
                            else {
                                $button->setType('submit');
                            }

                            break;

                        case 'cancel':
                            $group->addCss('pull-right');

                            /* @var $button \Core\Html\Controls\UiButton */
                            $button = $group->createButton('Controls\UiButton');
                            $button->setHref($action->getHref());
                            $button->addCss('btn-danger');

                            if ($this->is_ajax) {
                                $button->addData('ajax');
                            }
                            break;
                    }

                    $button->setInner($create_action($this->actions[$type]));

                    break;

                case 'context':
                default:

                    /* @var $group \Core\Html\Bootstrap\Buttongroups\ButtonGroup */
                    $group = $toolbar->createButtongroup();
                    $group->addCss('btn-group-sm pull-right dropup');

                    /* @var $context_menu \Core\Html\Form\Button */
                    $context_menu = $this->factory->create('Form\Button');
                    $context_menu->addCss('dropdown-toggle');
                    $context_menu->addAria([
                        'haspopup' => "true",
                        'expanded' => "false"
                    ]);
                    $context_menu->addData('toggle', 'dropdown');

                    // Do we have a text to show on actions button?
                    if (! empty($this->context_text)) {
                        $context_menu->addInner($this->context_text . ' ');
                    }

                    $context_menu->addInner('<span class="caret"></span>');

                    $group->setInner($context_menu->build());

                    // Create the actions dropdown menu
                    $dropdown = '<ul class="dropdown-menu">';

                    foreach ($this->actions[$type] as $action) {
                        $dropdown .= '<li>' . $create_action($action, true) . '</li>';
                    }

                    $dropdown .= '</ul>';

                    $group->addInner($dropdown);

                    break;
            }
        }

        $footer = $this->panel->createFooter();
        $footer->addContent($toolbar);

        return $this->panel->build();
    }
}
