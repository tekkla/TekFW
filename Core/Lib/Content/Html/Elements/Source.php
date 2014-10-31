<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Content\Html\HtmlAbstract;

/**
 * Source Html Element
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Elements
 * @license MIT
 * @copyright 2014 by author
 */
class Source extends HtmlAbstract
{

    protected $element = 'source';

    /**
     * Sets the type of media resource
     * 
     * @param string $media
     * @return \Core\Lib\Content\Html\Elements\Source
     */
    public function setMedia($media)
    {
        $this->attribute['media'] = $media;
        return $this;
    }

    /**
     * Sets the URL of the media file
     * 
     * @param string $source
     * @return \Core\Lib\Content\Html\Elements\Source
     */
    public function setSource($source)
    {
        $this->attribute['source'] = $source;
        return $this;
    }

    /**
     * Sets the MIME type of the media resource
     * 
     * @param string $type
     * @return \Core\Lib\Content\Html\Elements\Source
     */
    public function setType($type)
    {
        $this->attribute['type'] = $type;
        return $this;
    }

    public function build()
    {
        if (! isset($this->attribute['source']))
            Throw new \RuntimeException('No mediasource set.', 1000);
        
        if (! isset($this->attribute['type']))
            Throw new \RuntimeException('No media type set.', 1000);
        
        return parent::build();
    }
}
