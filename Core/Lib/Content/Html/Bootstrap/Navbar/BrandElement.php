<?php
namespace Core\Lib\Content\Html\Bootstrap\Navbar;

/**
 * Brand element for Bootstrap Navbar
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */
class BrandElement extends NavbarElementAbstract
{

    /**
     *
     * @var string
     */
    private $brand_type = 'text';

    /**
     *
     * @var string
     */
    private $content = '';

    /**
     *
     * @var string
     */
    private $url = '';

    /**
     *
     * @var string
     */
    private $alt = 'Brand';

    /**
     *
     * @var boolean
     */
    private $ajax = false;

    /**
     * Flags brand as image and sets image src and alt attribute.
     *
     * @param string $src
     * @param string $alt
     *
     * @return \Core\Lib\Content\Html\Bootstrap\Navbar\BrandElement
     */
    public function setImage($src, $alt = '')
    {
        $this->brand_type = 'image';
        $this->content = $src;
        $this->alt = $alt;

        return $this;
    }

    /**
     * Flags brand as text and sets the text.
     *
     * @param string $text
     *
     * @return \Core\Lib\Content\Html\Bootstrap\Navbar\BrandElement
     */
    public function setText($text)
    {
        $this->brand_type = 'text';
        $this->content = $text;

        return $this;
    }

    /**
     * Returns brand type.
     *
     * @return string
     */
    public function getBrandType()
    {
        return $this->brand_type;
    }

    /**
     * Set url to use for wrapping link of brand.
     *
     * @param string $url
     *
     * @return \Core\Lib\Content\Html\Bootstrap\Navbar\BrandElement
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Sets or gets ajax flag.
     *
     * @param string $ajax
     *
     * @return <boolean>, <\Core\Lib\Content\Html\Bootstrap\Navbar\LinkElement>
     */
    public function isAjax($ajax = null)
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
     *
     * @see \Core\Lib\Content\Html\Bootstrap\Navbar\NavbarElementAbstract::build()
     *
     */
    public function build()
    {
        $html = '';

        if ($this->url) {
            $html = '<a class="navbar-brand" href="' . $this->url . '"' . ($this->ajax ? ' data-ajax' : '') . '>';
        }

        if ($this->brand_type == 'image') {
            $html .= '<img alt="' . $this->alt . '" src="' . $this->content . '">';
        }
        else
            $html .= $this->content;

        if ($this->url) {
            $html .= '</a>';
        }

        return $html;
    }
}
