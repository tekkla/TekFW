<?php
namespace Core\Lib\Html\Controls;

use Core\Lib\Html\Form\Form;
use Core\Lib\Html\FormDesigner\FormDesigner;
use Core\Lib\Html\Bootstrap\Panel\Panel;
use Core\Lib\Errors\Exceptions\UnexpectedValueException;
use Core\Lib\Html\Elements\A;

/**
 * Editbox.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Editbox extends Panel
{

    private $caption = 'Editbox';

    private $description = '';

    private $form;

    private $is_ajax = false;

    private $save_text = '';

    private $cancel_text = '';

    private $cancel_action = '';

    private $center_buttons = [];

    private $actions_text = '';

    private $actions = [];

    /**
     * Sets the DOMID of form to save.
     *
     * @param string $form
     *            DomID of form
     *
     * @return \Core\Lib\Html\Controls\Editbox
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Sets action for cancel button.
     *
     * @param string $action
     *
     * @return \Core\Lib\Html\Controls\Editbox
     */
    public function setCancelAction($action)
    {
        $this->cancel_action = $action;

        return $this;
    }

    /**
     * Sets text for cancel button.
     *
     * @param string $text
     *            Text to show on button
     *
     * @return \Core\Lib\Html\Controls\Editbox
     */
    public function setCancelText($text)
    {
        $this->cancel_text = $text;

        return $this;
    }

    /**
     * Sets text for save button.
     *
     * @param string $text
     *            Text to show on button
     *
     * @return \Core\Lib\Html\Controls\Editbox
     */
    public function setSaveText($text)
    {
        $this->save_text = $text;

        return $this;
    }

    /**
     * Sets text for actions button.
     *
     * @param string $text
     *            Text to show on button
     *
     * @return \Core\Lib\Html\Controls\Editbox
     */
    public function setActionsText($text)
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
        } else {
            return $this->is_ajax;
        }
    }

    /**
     * Set caption text.
     *
     * @param string $caption
     *
     * @return \Core\Lib\Html\Controls\Editbox
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
     * @return \Core\Lib\Html\Controls\Editbox
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
     * Adds an action link to actions list.
     *
     * @param A $link
     *
     * @return A
     */
    public function &addAction(A $link)
    {
        $id = uniqid('editbox_action_');

        $this->actions[$id] = $link;

        return $this->actions[$id];
    }

    /**
     * Creates an action link, adds it to the actionslist and returns a reference to it.
     *
     * @param string $text
     * @param string $href
     * @param bool $ajax
     *
     * @return A
     */
    public function &createAction($text = '', $href = '', $ajax = false, $confirm = '')
    {
        $action = $this->addAction($this->factory->create('Elements\A'));

        if ($text) {
            $action->setInner($text);
        }

        if ($href) {
            $action->setHref($href);
        }

        if ((bool) $ajax) {
            $action->addData('ajax');
        }

        if ($confirm) {
            $action->addData('confirm', $confirm);
        }

        return $action;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Html\Bootstrap\Panel\Panel::build()
     *
     * @throws UnexpectedValueException
     */
    public function build()
    {
        if (empty($this->form)) {
            Throw new UnexpectedValueException('Editbox control needs a set FormDesigner or Form object.');
        }

        // Editbox CSS class needed
        $this->css[] = 'editbox';

        // No form designer buttons
        if ($this->form instanceof FormDesigner) {
            $form_id = $this->form->html->getId();
            $form_action = $this->form->html->getAttribute('action');

            // Ajax form?
            $this->is_ajax = $this->form->getSendMode() == 'ajax' ? true :false;
        }
        else
        {
            $form_id = $this->form->getId();
            $form_action = $this->form->getAttribute('action');
        }

        // Build form here because it's possible that form id will be autogenerated
        // and we need this id in the next step.
        $this->setBody($this->form->build());

        // Create heading content
        $heading = '<h3 class="panel-title">' . $this->caption . '</h3>';

        // Add description text?
        if ($this->description) {
            $heading .= $this->description;
        }

        $this->setHeading($heading);

        /* @var $btn_toolbar \Core\Lib\Html\Bootstrap\Buttongroups\ButtonToolbar */
        $btn_toolbar = $this->factory->create('Bootstrap\Buttongroups\ButtonToolbar');

        // Create save button

        /* @var $btn_group \Core\Lib\Html\Bootstrap\Buttongroups\ButtonGroup */
        $btn_group = $btn_toolbar->createButtongroup();
        $btn_group->addCss('btn-group-sm');

        $button = $btn_group->createButton()->setFormId($form_id)->setFormAction($form_action)->isSuccess();

        if ($this->is_ajax) {
            $button->addData('ajax');
        }
        else {
            $button->setType('submit');
        }

        /* @var $icon \Core\Lib\Html\Elements\Icon */
        $icon = $this->factory->create('Elements\Icon');
        $icon->setIcon('check');

        $button->addInner($icon->build());

        if ($this->save_text) {
            $button->addInner(' ' . $this->save_text);
        }

        if (! empty($this->cancel_action)) {

            /* @var $btn_group \Core\Lib\Html\Bootstrap\Buttongroups\ButtonGroup */
            $btn_group = $btn_toolbar->createButtongroup();
            $btn_group->addCss('btn-group-sm pull-right');

            /* @var $button \Core\Lib\Html\Controls\UiButton */
            $button = $btn_group->createButton('Controls\UiButton')->setHref($this->cancel_action)->addCss('btn-danger');

            if ($this->is_ajax) {
                $button->addData('ajax');
            }

            /* @var $icon \Core\Lib\Html\Elements\Icon */
            $icon = $this->factory->create('Elements\Icon');
            $icon->setIcon('times');

            $button->setInner($icon->build());

            if ($this->cancel_text) {
                $button->addInner(' ' . $this->cancel_text);
            }
        }

        // Action button to create?
        if ($this->actions) {

            /* @var $btn_group \Core\Lib\Html\Bootstrap\Buttongroups\ButtonGroup */
            $btn_group = $btn_toolbar->createButtongroup();
            $btn_group->addCss('btn-group-sm pull-right dropup');

            /* @var $actions \Core\Lib\Html\Form\Button */
            $actions = $this->factory->create('Form\Button');
            $actions->addCss('dropdown-toggle');
            $actions->addAria([
                'haspopup' => "true",
                'expanded' => "false"
            ]);
            $actions->addData('toggle', 'dropdown');

            // Do we have a text to show on actions button?
            if ($this->actions_text) {
                $actions->addInner($this->actions_text . ' ');
            }

            $actions->addInner('<span class="caret"></span>');

            // Create the actions dropdown menu
            $dropdown = '<ul class="dropdown-menu">';

            foreach ($this->actions as $action) {
                $dropdown .= '<li>' . $action->build() . '</li>';
            }

            $dropdown .= '</ul>';

            $btn_group->setInner($actions->build() . $dropdown);
        }

        $footer = $btn_toolbar->build();

        $this->setFooter($footer);

        return parent::build();
    }
}
