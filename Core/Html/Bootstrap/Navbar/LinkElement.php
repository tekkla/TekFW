<?php
namespace Core\Html\Bootstrap\Navbar;

/**
 * Link element for Bootstrap Navbar
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */
class LinkElement extends AbstractNavbarElement
{

    /**
     *
     * @var string
     */
    protected $type = 'link';

    /**
     *
     * @var string
     */
    private $url;

    /**
     *
     * @var string
     */
    private $text;

    /**
     *
     * @var boolean
     */
    private $current = false;

    /**
     *
     * @var boolean
     */
    private $ajax = false;

    /**
     * Sets linkelements href url
     *
     * @param string $url
     *
     * @return \Core\Html\Bootstrap\Navbar\LinkElement
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set links inner text.
     *
     * @param string $text
     *
     * @return \Core\Html\Bootstrap\Navbar\LinkElement
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Sets use currentx flag when parameter $ajax is set. Otherwise will returns use current flag status.
     *
     * @param boolean $current
     *
     * @return <boolean>, <\Core\Html\Bootstrap\Navbar\LinkElement>
     */
    public function useCurrent($current=null)
    {
        if (isset($current)) {
            $this->current = (bool) $current;
            return $this;
        }
        else {
            return $this->current;
        }
    }

    /**
     * Sets or gets ajax flag.
     *
     * @param string $ajax
     *
     * @return <boolean>, <\Core\Html\Bootstrap\Navbar\LinkElement>
     */
    public function isAjax($ajax=null)
    {
        if (isset($ajax)) {
            $this->ajax = (bool) $ajax;
            return $this;
        }
        else {
            return $this->ajax;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Core\Html\Bootstrap\Navbar\NavbarElementAbstract::build()
     */
    public function build()
    {
        $html = '<a href="' . $this->url . '"';

        if ($this->ajax) {
            $html .= ' data-ajax';
        }

        $html .= '>' . $this->text;

        if ($this->current) {
            $html .= ' <span class="sr-only">(current)</span>';
        }

        $html .= '</a>';

        return $html;
    }
}

