<?php
namespace Core\Asset;

/**
 * AbstractAssetObject.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractAssetObject implements AssetObjectInterface
{

    /**
     *
     * @var string
     */
    protected  $content;

    /**
     *
     * @var boolean
     */
    protected  $combine = true;

    /**
     *
     * @var boolean
     */
    protected  $minify = true;

    /**
     *
     * @var string
     */
    protected  $id = '';

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetObjectInterface::getMinify()
     *
     */
    public function getMinify()
    {
        return $this->minify;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetObjectInterface::setContent()
     *
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetObjectInterface::setCombine()
     *
     */
    public function setCombine($combine)
    {
        $this->combine = (bool) $combine;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetObjectInterface::getType()
     *
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetObjectInterface::getContent()
     *
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetObjectInterface::getCombine()
     *
     */
    public function getCombine()
    {
        return $this->combine;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetObjectInterface::setId()
     *
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetObjectInterface::getId()
     *
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetObjectInterface::setMinify()
     *
     */
    public function setMinify($minify)
    {
        $this->minify = (bool) $minify;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetObjectInterface::setType()
     *
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
