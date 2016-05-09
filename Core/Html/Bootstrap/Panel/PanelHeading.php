<?php
namespace Core\Html\Bootstrap\Panel;

use Core\Html\Elements\Div;

/**
 * PanelHeading.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class PanelHeading extends AbstractPanelElement
{

    /**
     *
     * @var \Core\Html\Bootstrap\Panel\PanelTitle
     */
    private $title;

    public function __construct()
    {
        $this->html = new Div();
        $this->html->addCss('panel-heading');
    }

    /**
     * Creates a PanelTitle object, adds it by default as title to use in the heading and returns a reference to it
     *
     * @param string $text
     *            Optional text to fill in as title content.
     * @param boolean $autoadd
     *            Optional flag to dis-/enable autoadd of created object as title object
     *
     * @return \Core\Html\Bootstrap\Panel\PanelTitle
     */
    public function &createTitle($text = '', $autoadd = true)
    {
        $title = new PanelTitle();

        if (! empty($text)) {
            $title->setTitle($text);
        }

        if ($autoadd) {
            $this->title = $title;
        }

        return $title;
    }

    /**
     *
     * @param PanelTitle $title
     */
    public function setTitle(PanelTitle $title)
    {
        $this->title = $title;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Html\Bootstrap\Panel\AbstractPanelElement::build()
     */
    public function build()
    {
        if (! empty($this->title)) {
            $this->inner = $this->title->build();
        }

        return parent::build();
    }
}
