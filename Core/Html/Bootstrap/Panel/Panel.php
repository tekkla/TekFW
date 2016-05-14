<?php
namespace Core\Html\Bootstrap\Panel;

use Core\Html\Elements\Div;
use Core\Html\Bootstrap\BootstrapContextInterface;

/**
 * Panel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Panel extends AbstractPanelElement implements BootstrapContextInterface
{

    /**
     *
     * @var string
     */
    private $context = 'default';

    public function __construct()
    {
        // Panels are divs
        $this->html = new Div();
        $this->html->addCss([
            'panel'
        ]);
    }

    /**
     * Creates a PanelHeading object, adds it by default to the content stack and returns a reference on it
     *
     * @param string $title
     *            Optional text to use as title (Default: '')
     * @param string $autoadd
     *            Optional flag to dis-/enable autoadd of created object to the content stack
     *            
     * @return PanelHeading
     */
    public function &createHeading($title = '', $autoadd = true)
    {
        $heading = new PanelHeading();
        
        if ($autoadd) {
            $this->content[] = $heading;
        }
        
        return $heading;
    }

    /**
     * Creates a PanelBody object, adds it by default to the content stack and returns a reference on it
     *
     * @param string $autoadd
     *            Optional flag to dis-/enable autoadd of created object to the content stack
     */
    public function &createBody($autoadd = true)
    {
        $body = new PanelBody();
        
        if ($autoadd) {
            $this->content[] = $body;
        }
        return $body;
    }

    /**
     * Creates a PanelHeading object, adds it by default to the content stack and returns a reference on it
     *
     * @param string $autoadd
     *            Optional flag to dis-/enable autoadd of created object to the content stack
     */
    public function &createFooter($autoadd = true)
    {
        $footer = new PanelFooter();
        if ($autoadd) {
            $this->content[] = $footer;
        }
        return $footer;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Html\Bootstrap\BootstrapContextInterface::getContext()
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Html\Bootstrap\BootstrapContextInterface::setContext()
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Html\Bootstrap\Panel\AbstractPanelElement::build()
     */
    public function build()
    {
        $this->html->addCss('panel-' . $this->context);
        
        return parent::build();
    }
}

