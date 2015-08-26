<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Content\Html\HtmlAbstract;

/**
 * Img.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Img extends HtmlAbstract
{

    protected $element = 'img';

    /**
     * Set src attribute.
     *
     * @param string|Url $src Src value
     *
     * @return \Core\Lib\Content\Html\Elements\Img
     */
    public function setSrc($src)
    {
        $this->attribute['src'] = $src;

        return $this;
    }

    /**
     * Sets alt attribute.
     *
     * @param string $alt
     *
     * @return \Core\Lib\Content\Html\Elements\Img
     */
    public function setAlt($alt)
    {
        $this->attribute['alt'] = $alt;

        return $this;
    }

    /**
     * Sets title attribute.
     *
     * @param string $title
     *
     * @return \Core\Lib\Content\Html\Elements\Img
     */
    public function setTitle($title)
    {
        $this->attribute['title'] = $title;

        return $this;
    }

    /**
     * Set width attribute
     *
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->attribute['width'] = (int) $width;

        return $this;
    }

    /**
     * Sets height attribute.
     *
     * @param int $height
     * @return \Core\Lib\Content\Html\Elements\Img
     */
    public function setHeight($height)
    {
        $this->attribute['height'] = (int) $height;

        return $this;
    }

    /**
     * Sets ismap attribute.
     *
     * @param string $flag
     *
     * @return \Core\Lib\Content\Html\Elements\Img
     */
    public function setIsMap()
    {
        $this->attribute['ismap'] = false;

        return $this;
    }

    /**
     * Sets the name of map to use
     *
     * @param string $name
     * @return \Core\Lib\Content\Html\Elements\Img
     */
    public function useMap($name)
    {
        $this->attribute['usemap'] = $name;

        return $this;
    }
}
