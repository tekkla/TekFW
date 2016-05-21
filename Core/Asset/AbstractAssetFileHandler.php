<?php
namespace Core\Asset;

/**
 * AbstractAssetFileHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractAssetFileHandler implements AssetFileHandlerInterface
{

    /**
     *
     * @var string
     */
    protected $content;

    /**
     *
     * @var int
     */
    protected $ttl = 0;

    /**
     *
     * @var string
     */
    protected $filename;

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetFileInterface::setContent()
     *
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetFileInterface::setTTL()
     *
     */
    public function setTTL($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\AssetFileInterface::setFilename()
     *
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AssetFilehandlerInterface::checkTTL()
     */
    public function checkTTL()
    {
        if (!file_exists($this->filename) || filemtime($this->filename) + $this->ttl < time()) {
            return false;
        }

        return true;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AssetFilehandlerInterface::write()
     */
    public function write()
    {
        file_put_contents($this->filename, $this->content);
    }
}

