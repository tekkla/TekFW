<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Abstracts\HtmlAbstract;

// Check for direct file access
if (! defined('TEKFW'))
    die('Cannot run without TekFW framework...');

/**
 * Creates a img html object
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Element
 * @license MIT
 * @copyright 2014 by author
 */
class Img extends HtmlAbstract
{

    protected $element = 'img';

    /**
     * Factory pattern
     * 
     * @param string|Url $src
     * @return \Core\Lib\Content\Html\Elements\Img
     */
    public static function factory($src)
    {
        $obj = new Img();
        $obj->setSrc($src);
        return $obj;
    }

    /**
     * Set src attribute
     * 
     * @param string|Url $src Src value
     * @return \Core\Lib\Content\Html\Elements\Img
     */
    public function setSrc($src)
    {
        $this->attribute['src'] = $src;
        return $this;
    }

    /**
     * Sets alt attribute
     * 
     * @param string $alt
     * @return \Core\Lib\Content\Html\Elements\Img
     */
    public function setAlt($alt)
    {
        $this->attribute['alt'] = $alt;
        return $this;
    }

    /**
     * Sets title attribute
     * 
     * @param string $title
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
     * Sets height attribute
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
