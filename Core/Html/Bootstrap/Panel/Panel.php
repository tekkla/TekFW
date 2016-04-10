<?php
namespace Core\Html\Bootstrap\Panel;

use Core\Html\Elements\Div;
use Core\Html\HtmlBuildableInterface;

/**
 * Panel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Panel extends PanelElementAbstract implements HtmlBuildableInterface
{

    private $context = 'default';

    public function __construct()
    {
        // Panels are divs
        $this->html = new Div();
        $this->html->addCss([
            'panel',
            'panel-default'
        ]);
    }

    public function &createHeading()
    {
        $heading = new PanelHeading();

        $this->content[] = $heading;

        return $heading;


    }

    public function &createBody()
    {
        $body = new PanelBody();

        $this->content[] = $body;

        return $body;
    }

    public function &createFooter()
    {
        $footer = new PanelFooter();

        $this->content[] = $footer;

        return $footer;
    }

    /**
     * Sets own panel context.
     *
     * @param unknown $context
     *
     * @return \Core\Html\Controls\Panel
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
     * @return \Core\Html\Controls\Panel
     */
    public function setContextPrimary()
    {
        $this->context = 'primary';

        return $this;
    }

    /**
     * Sets panel context to success.
     *
     * @return \Core\Html\Controls\Panel
     */
    public function setContextSuccess()
    {
        $this->context = 'success';

        return $this;
    }

    /**
     * Sets panel context to info.
     *
     * @return \Core\Html\Controls\Panel
     */
    public function setContextInfo()
    {
        $this->context = 'info';

        return $this;
    }

    /**
     * Sets panel context to warning.
     *
     * @return \Core\Html\Controls\Panel
     */
    public function setContextWarning()
    {
        $this->context = 'warning';

        return $this;
    }

    /**
     * Sets panel context to danger.
     *
     * @return \Core\Html\Controls\Panel
     */
    public function setContextDanger()
    {
        $this->context = 'danger';

        return $this;
    }
}

