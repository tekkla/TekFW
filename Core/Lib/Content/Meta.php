<?php
namespace Core\Lib\Content;

/**
 * Class to create and store html meta tag defintition by php code
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license
 *
 */
class Meta
{

    /**
     * Tags storage
     *
     * @var array
     */
    private $tags = [
        'charset' => [
            'charset' => 'UTF-8'
        ],
        'viewport' => [
            'name' => 'viewport',
            'content' => 'width=device-width, initial-scale=1'
        ]
    ];

    /**
     * Adds a generic tag
     *
     * @param array $properties
     */
    public function setGenericTag($properties)
    {
        $this->tags[] = $properties;
    }

    public function setViewport($width = 'device-width', $initial_scale = '1', $user_scalable = '', $minimum_scale = '', $maximum_scale = '')
    {
        $tag = [
            'name' => 'viewport',
            'content' => 'width=' . $width . ', initial-scale=' . $initial_scale
        ];

        if ($user_scalable) {
            $tag['content'] .= ', user-scalable=' . $user_scalable;
        }

        if ($minimum_scale) {
            $tag['content'] .= ', minimum-scale=' . $minimum_scale;
        }

        if ($maximum_scale) {
            $tag['content'] .= ', maximum_scale=' . $maximum_scale;
        }

        $this->tags['viewport'] = $tag;
    }

    /**
     * Sets charset tag
     *
     * @param string $charset
     *
     * @return \Core\Lib\Content\Meta
     */
    public function setCharset($charset = 'UTF-8')
    {
        $this->tags['charset'] = [
            'charset' => $charset
        ];

        return $this;
    }

    /**
     * Sets description tag
     *
     * @param string $description
     *
     * @return \Core\Lib\Content\Meta
     */
    public function setDescription($description = '')
    {
        $this->tags['description'] = [
            'name' => 'description',
            'content' => $description
        ];

        return $this;
    }

    /**
     * Sets keywords tag
     *
     * @param string $keywords
     *
     * @return \Core\Lib\Content\Meta
     */
    public function setKeywords($keywords = '')
    {
        $this->tags['keywords'] = [
            'name' => 'keywords',
            'content' => $keywords
        ];

        return $this;
    }

    /**
     * Sets author tag
     *
     * @param string $author
     *
     * @return \Core\Lib\Content\Meta
     */
    public function setAuthor($author = '')
    {
        $this->tags['author'] = [
            'name' => 'author',
            'content' => $author
        ];

        return $this;
    }

    /**
     * Sets http-equiv refresh tag
     *
     * @param number $refresh
     *
     * @return \Core\Lib\Content\Meta
     */
    public function setRefresh($refresh = 30)
    {
        $this->tags['http-equiv'] = [
            'http-equiv' => 'refresh',
            'content' => $refresh
        ];

        return $this;
    }

    /**
     * Returns all set tags
     */
    public function getTags()
    {
        return $this->tags;
    }
}
