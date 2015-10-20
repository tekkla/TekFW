<?php
namespace Core\Lib\Content\Html\Form;

use Core\Lib\Content\Html\FormAbstract;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;
use Core\Lib\Content\Html\Form\Traits\ValueTrait;

/**
 * Button.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Button extends FormAbstract
{

    use ValueTrait;

    /**
     * Name of icon to use
     *
     * @var
     *
     */
    protected $button_icon;

    /**
     * Type of button
     *
     * @var string
     */
    protected $type = 'button';

    /**
     * Type
     *
     * @var string
     */
    protected $button_type = 'default';

    /**
     * Size
     *
     * @var string
     */
    protected $button_size;

    // Element type
    protected $element = 'button';

    // Basic css classes
    protected $css = [
        'btn'
    ];

    // Basic data attributes
    protected $data = [
        'control' => 'button'
    ];

    /**
     * Sets name of the fontawesome icon to use with the button.
     *
     * @param string $$button_icon Name of the icon without the leading "fa-"
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function useIcon($button_icon)
    {
        $this->button_icon = $button_icon;

        return $this;
    }

    /**
     * Sets buttontype to: default.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function isDefault()
    {
        $this->button_type = 'default';

        return $this;
    }

    /**
     * Sets buttontype to: primary.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function isPrimary()
    {
        $this->button_type = 'primary';

        return $this;
    }

    /**
     * Sets buttontype to: danger.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function isDanger()
    {
        $this->button_type = 'danger';

        return $this;
    }

    /**
     * Sets buttontype to: info.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function isInfo()
    {
        $this->button_type = 'info';

        return $this;
    }

    /**
     * Sets buttontype to: warning.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function isWarning()
    {
        $this->button_type = 'warning';

        return $this;
    }

    /**
     * Sets buttontype to: success.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function isSuccess()
    {
        $this->button_type = 'success';
        return $this;
    }

    /**
     * Sets buttontype to: link.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function isLink()
    {
        $this->button_type = 'link';

        return $this;
    }

    /**
     * Set button size to: xs.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function sizeXs()
    {
        $this->button_size = 'xs';

        return $this;
    }

    /**
     * Set button size to: sm.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function sizeSm()
    {
        $this->button_size = 'sm';

        return $this;
    }

    /**
     * Set button size to: md.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function sizeMd()
    {
        $this->button_size = 'md';

        return $this;
    }

    /**
     * Set button size to: lg.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function sizeLg()
    {
        $this->button_size = 'lg';

        return $this;
    }

    /**
     * Sets element type to: button (default).
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function isButton()
    {
        $this->type = 'button';

        return $this;
    }

    /**
     * Sets element type to: submit.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function isSubmit()
    {
        $this->type = 'submit';

        return $this;
    }

    /**
     * Sets element type to: reset.
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function isReset()
    {
        $this->type = 'reset';

        return $this;
    }

    /**
     * Sets element type.
     *
     * @param string $type Type of element (submit, reset or button)
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function setType($type)
    {
        $types = [
            'submit',
            'reset',
            'button'
        ];

        if (! in_array($type, $types)) {
            Throw new InvalidArgumentException('Wrong button type set.', 1000);
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Set the id of the form this button belongs to.
     *
     * @param string $form_id
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function setFormId($form_id)
    {
        $this->attribute['form'] = $form_id;

        return $this;
    }

    /**
     * Sets the url where to send form data on submit (only on buttontype "submit").
     *
     * @param string|Url $url Url string or object used as form action
     *
     * @return \Core\Lib\Content\Html\Form\Button
     */
    public function setFormAction($url)
    {
        $this->attribute['formaction'] = $url;

        return $this;
    }

    /**
     * Set the method of form the button belongs to.
     * Use 'post' or 'get'.
     * Form elements are using post by default.
     *
     * @param string $method Value for the method attribute of from
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Content\Html\Elements\Form
     */
    public function setFormMethod($method)
    {
        $methods = [
            'post',
            'get'
        ];

        // Safety first. Only allow 'post' or 'get' here.
        if (! in_array($method, $methods)) {
            Throw new InvalidArgumentException('Wrong method set.', 1000);
        }

        $this->attribute['formmethod'] = $method;
        return $this;
    }

    /**
     * Set the form method attribute.
     * Use 'post' or 'get'.
     * Form elements are using post by default.
     *
     * @param string $method Value for the method attribute of from
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Content\Html\Elements\Form
     */
    public function setFormEnctype($enctype)
    {
        $enctypes = [
            'application/x-www-form-urlencoded',
            'multipart/form-data',
            'text/plain'
        ];

        // Safety first. Only allow 'post' or 'get' here.
        if (! in_array($enctype, $enctypes)) {
            Throw new InvalidArgumentException('Wrong method set.', 1000);
        }

        $this->attribute['formenctype'] = $enctype;

        return $this;
    }

    /**
     * Set target of form the button belongs to
     *
     * @param string $target
     *
     * @return \Core\Lib\Content\Html\Elements\Form
     */
    public function setFormTarget($target)
    {
        $this->attribute['formtarget'] = $target;

        return $this;
    }

    /**
     * Deactivates form validation of form the button belongs to by setting "novalidate" attribute
     *
     * @return \Core\Lib\Content\Html\Elements\Form
     */
    public function setFormNoValidate()
    {
        $this->attribute['formnovalidate'] = false;

        return $this;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Abstracts\HtmlAbstract::build()
     */
    public function build()
    {
        $this->attribute['type'] = $this->type;

        // Has this button an icon top add?
        if (isset($this->button_icon)) {
            $this->inner = '<i class="fa fa-' . $this->button_icon . '"></i> ' . $this->inner;
        }

        // Add button type css
        $this->css[] = 'btn-' . $this->button_type;

        // Do we have to add cs for a specific button size?
        if (isset($this->button_size)) {
            $this->css[] = 'btn-' . $this->button_size;
        }

        return parent::build();
    }
}
