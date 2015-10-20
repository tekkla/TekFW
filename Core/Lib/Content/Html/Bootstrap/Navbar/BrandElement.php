<?php
namespace Core\Lib\Content\Html\Bootstrap\Navbar;

use Core\Lib\Content\Html\Elements\Img;
use Core\Lib\Content\Html\Elements\A;

/**
 * Brand element for Bootstrap Navbar
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */
class BrandElement extends AbstractNavbarElement
{

    /**
     *
     * @var string
     */
    protected $type = 'brand';

    /**
     *
     * @var string
     */
    private $content = '';

    /**
     *
     * @var A
     */
    private $link = false;

    /**
     * Creates a brand imageobject and returns reference.
     *
     * @param string $src
     * @param string $alt Optional
     *
     * @return \Core\Lib\Content\Html\Elements\Img
     */
    public function createImage($src, $alt = '')
    {
        /* @var $img \Core\Lib\Content\Html\Elements\Img */
        $img = $this->factory->create('Elements\Img');
        $img->setSrc($src);

        if (! empty($alt)) {
            $img->setAlt($alt);
        }

        return $this->content = $img;
    }

    /**
     * Sets brand imageobject.
     *
     * @param Img $img
     * @return \Core\Lib\Content\Html\Bootstrap\Navbar\BrandElement
     */
    public function setImage(Img $img)
    {
        $this->content = $img;

        return $this;
    }

    /**
     * Sets brandtext.
     *
     * @param string $text
     *
     * @return \Core\Lib\Content\Html\Bootstrap\Navbar\BrandElement
     */
    public function setText($text)
    {
        $this->content = $text;

        return $this;
    }

    public function createLink($url='')
    {
        /* @var $a \Core\Lib\Content\Html\Elements\A */
        $a = $this->factory->create('Elements\A');

        if (!empty($url)) {
            $a->setHref($url);
        }

        return $this->link = $a;
    }

    public function setLink(A $a)
    {
        $this->link = $a;

        return $this;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Content\Html\Bootstrap\Navbar\NavbarElementAbstract::build()
     *
     */
    public function build()
    {
        // Create brand
        $html = $this->content instanceof Img ? $this->content->build() : $this->content;

        // Brand wrapped by a link
        if ($this->link instanceof A) {

            $this->link->addCss('navbar-brand');
            $this->link->setInner($html);

            $html = $this->link->build();
        }

        return $html;
    }
}
