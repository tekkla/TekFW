<?php
namespace Core\Lib\Content\Html\Controls;

use Core\AppsSec\Core\Exception\HtmlException;
use Core\Lib\Content\Html\Form\Form;
use Core\Lib\Content\Html\FormDesigner\FormDesigner;
use Core\Lib\Content\Html\Bootstrap\Panel;
/**
 *
 * @author Michael
 *
 */
class Editbox extends Panel
{

    private $caption = 'Editbox';

    private $form;

    private $cancel_action = '';

    private $is_ajax = false;

    /**
     * Sets the DOMID of form to save.
     *
     * @param string $form DomID of form
     *
     * @return \Core\Lib\Content\Html\Controls\Editbox
     */
    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Sets action for cancel button.
     *
     * @param unknown $save_action
     *
     * @return \Core\Lib\Content\Html\Controls\Editbox
     */
    public function setCancelAction($cancel_action)
    {
        $this->cancel_action = $cancel_action;

        return $this;
    }

    /**
     * Sets ajax flag when parameter $ajax is set. Otherwise will returns current ajax flag status.
     *
     * @param string $ajax
     *
     * @return boolean
     */
    public function isAjax($ajax=null)
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
     * @return \Core\Lib\Content\Html\Controls\Editbox
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

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
     * (non-PHPdoc)
     * @see \Core\Lib\Content\Html\Bootstrap\Panel::build()
     */
    public function build()
    {
        if (empty($this->form)) {
            Throw new HtmlException('Editbox control needs a form object.');
        }

        // Editbox CSS class needed
        $this->css[] = 'editbox';

        // No form designer buttons
        if ($this->form instanceof FormDesigner) {

            $this->form->noButtons();

            if ($this->form->isAjax()) {
                $this->is_ajax = true;
            }
        }

        // Build form here because it's possible that form id will be autogenerated
        // and we need this id in the next step.
        $content = $this->form->build();

        // Create heading content
        $heading = '
        <ul class="list-inline">
            <li>
                <button type="submit" form="' . $this->form->getId() . '" class="btn btn-sm btn-' . $this->getContext() . '"' . ($this->is_ajax ? ' data-ajax' : '') . ' data-form="' . $this->form->getId() . '">
                    <i class="fa fa-check"></i>
                </button>
            </li>
            <li>
                <strong>' . $this->caption . '</strong>
            </li>';

            if (!empty($this->cancel_action)) {

            $heading .= '
            <li class="pull-right">
                <a class="btn btn-sm btn-' . $this->getContext() . '" href="' . $this->cancel_action . '"' . ($this->is_ajax ? ' data-ajax' : '') . '>
                    <i class="fa fa-times"></i>
                </a>
            </li>';

            }

            $heading .= '
        </ul>';

        $this->setHeading($heading);
        $this->setBody($content);

        return parent::build();
    }
}
