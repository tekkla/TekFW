<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Abstracts\HtmlAbstract;
use Core\Lib\Url;

/**
 * A Html Element
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Lib
 * @license MIT
 * @copyright 2014 by author
 */
class A extends HtmlAbstract
{

    protected $element = 'a';

    /**
     * Factory method
     * 
     * @param string|Url $url
     * @return \Core\Lib\Content\Html\Elements\Link
     */
    public static function factory($url = null)
    {
        $obj = new self();
        
        if (isset($url))
            $obj->setHref($url);
        
        return $obj;
    }

    /**
     * Sets an alternate text for the link.
     * Required if the href attribute is present.
     * 
     * @param string $alt
     * @return \Core\Lib\Content\Html\Elements\Link
     */
    public function setAlt($alt)
    {
        $this->attribute['alt'] = $alt;
        return $this;
    }

    /**
     * Sets the href attribute.
     * 
     * @param string $href
     */
    public function setHref($url)
    {
        if ($url instanceof Url)
            $url->getUrl();
        
        $this->attribute['href'] = $url;
        return $this;
    }

    /**
     * Sets the language of the target URL
     * 
     * @param string $lang_code
     * @return \Core\Lib\Content\Html\Elements\Link
     */
    public function setHrefLang($lang_code)
    {
        $this->attribute['hreflang'] = $lang_code;
        return $this;
    }

    /**
     * Sets the target attribute
     * 
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->attribute['target'] = $target;
        return $this;
    }

    /**
     * Sets he relationship between the current document and the target URL
     * 
     * @param string $rel
     */
    public function setRel($rel)
    {
        $rels = array(
            'alternate',
            'author',
            'bookmark',
            'help',
            'license',
            'next',
            'nofollow',
            'noreferrer',
            'prefetch',
            'prev',
            'search',
            'tag'
        );
        
        if (! in_array($rel, $rels))
            throw new \InvalidArgumentException('Not valid rel attribute', 1000);
        
        $this->attribute['rel'] = $rel;
        return $this;
    }

    /**
     * Sets that the target will be downloaded when a user clicks on the link
     * 
     * @return \Core\Lib\Content\Html\Elements\Link
     */
    public function isDownload()
    {
        $this->attribute['download'] = false;
        return $this;
    }

    /**
     * Sets what media/device the target URL is optimized for
     * 
     * @param string $media
     * @return \Core\Lib\Content\Html\Elements\Link
     */
    public function setMedia($media)
    {
        $this->attribute['media'] = $media;
        return $this;
    }

    /**
     * Sets the MIME type of the target URL
     * 
     * @param string $media
     * @return \Core\Lib\Content\Html\Elements\Link
     */
    public function setType($type)
    {
        $this->attribute['type'] = $type;
        return $this;
    }

    /**
     * Build method with href and set alt check
     * 
     * @see \Core\Lib\Abstracts\HtmlAbstract::build()
     */
    public function build()
    {
        if (isset($this->attribute['href']) && (! isset($this->attribute['alt'])))
            $this->attribute['alt'] = '';
        
        return parent::build();
    }
}
