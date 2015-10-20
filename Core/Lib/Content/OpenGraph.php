<?php
namespace Core\Lib\Content;

/**
 * OpenGraph.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class OpenGraph
{

    /**
     * Tags storage
     *
     * @var array
     */
    private $tags = [];

    /**
     * Adds a generic tag.
     *
     * @param array $properties
     *
     * @return \Core\Lib\Content\OpenGraph
     */
    public function setGenericTag($property, $content)
    {
        $this->tags[$property] = $content;

        return $this;
    }

    /**
     * Sets title tag.
     *
     * @param string $charset
     *
     * @return \Core\Lib\Content\OpenGraph
     */
    public function setTitle($title = '')
    {
        $this->tags['og:title'] = $title;

        return $this;
    }

    /**
     * Sets type tag.
     *
     * @param string $type
     *
     * @return \Core\Lib\Content\OpenGraph
     */
    public function setType($type = '')
    {
        $this->tags['og:type'] = $type;

        return $this;
    }

    /**
     * Sets url tag.
     *
     * @param string $url
     *
     * @return \Core\Lib\Content\OpenGraph
     */
    public function setUrl($url = '')
    {
        $this->tags['og:url'] = $url;

        return $this;
    }

    /**
     * Sets image tag.
     *
     * @param string $image
     *
     * @return \Core\Lib\Content\OpenGraph
     */
    public function setImage($image = '')
    {
        $this->tags['og:image'] = $image;

        return $this;
    }

    /**
     * Returns all set tags.
     */
    public function getTags()
    {
        return $this->tags;
    }
}
