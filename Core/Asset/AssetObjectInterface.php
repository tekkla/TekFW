<?php
namespace Core\Asset;

/**
 * AssetObjectInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface AssetObjectInterface
{

    public function setMinify($minify);

    public function getMinify();

    /**
     * Sets objects content
     *
     * @param string $content
     */
    public function setContent($content);

    /**
     * Get objects content
     *
     * @return string
     */
    public function getContent();

    /**
     * Sets objects type
     *
     * @param string $type
     */
    public function setType($type);

    /**
     * Returns objects type
     *
     * @return string
     */
    public function getType();

    public function setCombine($combine);

    public function getCombine();

    public function setId($id);

    public function getId();
}
