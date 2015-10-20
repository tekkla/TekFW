<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Content\Html\HtmlAbstract;
use Core\Lib\Errors\Exceptions\UnexpectedValueException;

/**
 * Source.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Source extends HtmlAbstract
{

    protected $element = 'source';

    /**
     * Sets the type of media resource
     *
     * @param string $media
     *
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
     *
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
     *
     * @return \Core\Lib\Content\Html\Elements\Source
     */
    public function setType($type)
    {
        $this->attribute['type'] = $type;

        return $this;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Content\Html\HtmlAbstract::build()
     *
     * @throws UnexpectedValueException
     */
    public function build()
    {
        if (! isset($this->attribute['source'])) {
            Throw new UnexpectedValueException('No mediasource set.', 1000);
        }

        if (! isset($this->attribute['type'])) {
            Throw new UnexpectedValueException('No media type set.', 1000);
        }

        return parent::build();
    }
}
