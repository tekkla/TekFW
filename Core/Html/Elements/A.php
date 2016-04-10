<?php
namespace Core\Html\Elements;

use Core\Html\HtmlAbstract;
use Core\Errors\Exceptions\InvalidArgumentException;

/**
 * A.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class A extends HtmlAbstract
{

    protected $element = 'a';

    /**
     * Sets an alternate text for the link.
     * Required if the href attribute is present.
     *
     * @param string $alt
     *
     * @return \Core\Html\Elements\A
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
     *
     * @return \Core\Html\Elements\A
     */
    public function setHref($url)
    {
        $this->attribute['href'] = $url;

        return $this;
    }

    /**
     * Sets the language of the target URL
     *
     * @param string $lang_code
     *
     * @return \Core\Html\Elements\A
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
     *
     * @return \Core\Html\Elements\A
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
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Html\Elements\A
     */
    public function setRel($rel)
    {
        $rels = [
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
        ];

        if (! in_array($rel, $rels)) {
            Throw new InvalidArgumentException('Not valid rel attribute', 1000);
        }

        $this->attribute['rel'] = $rel;

        return $this;
    }

    /**
     * Sets that the target will be downloaded when a user clicks on the link
     *
     * @return \Core\Html\Elements\A
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
     *
     * @return \Core\Html\Elements\Link
     */
    public function setMedia($media)
    {
        $this->attribute['media'] = $media;

        return $this;
    }

    /**
     * Sets the MIME type of the target URL
     *
     * @param string $type
     *
     * @return \Core\Html\Elements\A
     */
    public function setType($type)
    {
        $this->attribute['type'] = $type;

        return $this;
    }

    /**
     * Build method with href and set alt check
     *
     * @see \Core\Abstracts\HtmlAbstract::build()
     */
    public function build()
    {
        if (isset($this->attribute['href']) && (! isset($this->attribute['alt']))) {
            $this->attribute['alt'] = $this->attribute['href'];
        }

        return parent::build();
    }
}
