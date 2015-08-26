<?php
namespace Core\Lib\Content\Html\Bootstrap\Panel;

use Core\Lib\Content\Html\Elements\Div;

/**
 * Panel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 * @deprecated On Bootstrap 4.0 (!!!)
 */
class Panel extends Div
{

    protected $css = [
        'panel'
    ];

    private $heading = '';

    private $body = '';

    private $footer = '';

    private $context = 'default';

    private $use_title;

    /**
     * Sets own panel context.
     *
     * @param unknown $context
     *
     * @return \Core\Lib\Content\Html\Controls\Panel
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /*
     * +
     * Returns panel context.
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets panel context to primary.
     *
     * @return \Core\Lib\Content\Html\Controls\Panel
     */
    public function setContextPrimary()
    {
        $this->context = 'primary';

        return $this;
    }

    /**
     * Sets panel context to success.
     *
     * @return \Core\Lib\Content\Html\Controls\Panel
     */
    public function setContextSuccess()
    {
        $this->context = 'success';

        return $this;
    }

    /**
     * Sets panel context to info.
     *
     * @return \Core\Lib\Content\Html\Controls\Panel
     */
    public function setContextInfo()
    {
        $this->context = 'info';

        return $this;
    }

    /**
     * Sets panel context to warning.
     *
     * @return \Core\Lib\Content\Html\Controls\Panel
     */
    public function setContextWarning()
    {
        $this->context = 'warning';

        return $this;
    }

    /**
     * Sets panel context to danger.
     *
     * @return \Core\Lib\Content\Html\Controls\Panel
     */
    public function setContextDanger()
    {
        $this->context = 'danger';

        return $this;
    }

    public function setHeading($heading)
    {
        $this->heading = $heading;

        return $this;
    }

    public function setTitle($title)
    {
        $this->use_title = true;
        $this->heading = $title;

        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function setFooter($footer)
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Content\Html\HtmlAbstract::build()
     */
    public function build()
    {
        $this->css[] = 'panel-' . $this->context;

        $this->inner .= '<div class="panel-heading">';

        if ($this->use_title) {
            $this->inner .= '<h3 class="panel-title">' . $this->heading . '</h3>';
        }
        else {
            $this->inner .= $this->heading;
        }

        $this->inner .= '</div>
        <div class="panel-body">' . $this->body . '</div>';

        if ($this->footer) {
            $this->inner .= '<div class="panel-footer">' . $this->footer . '</div>';
        }

        return parent::build();
    }
}
