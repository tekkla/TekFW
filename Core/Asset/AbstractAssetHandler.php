<?php
namespace Core\Asset;

use Core\Asset\Processor\ProcessorInterface;

/**
 * AbstractAssetHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractAssetHandler implements AssetHandlerInterface
{

    /**
     * Type of asset handler
     *
     * @var string
     */
    protected $type;

    /**
     * Stack of asset objects
     *
     * @var array
     */
    protected $objects = [];

    /**
     * Stack of processors to run before
     *
     * @var array
     */
    protected $processors = [];

    /**
     *
     * @var AssetFilehandlerInterface
     */
    protected $filehandler;

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AssetHandlerInterface::getType()
     */
    public function getType()
    {
        if (empty($this->type)) {
            Throw new AssetHandlerException('No type set for this AssetHandler.');
        }

        return $this->type;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AssetHandlerInterface::setType()
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AssetHandlerInterface::createObject()
     */
    public function createObject()
    {
        $class = get_called_class();
        return new $class();
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AssetHandlerInterface::addObject()
     */
    public function addObject(AssetObjectInterface $aio)
    {
        $this->objects[] = $aio;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AssetHandlerInterface::getObjects()
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AssetHandlerInterface::addProcessor()
     */
    public function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }



    /**
     * {@inheritDoc}
     * @see \Core\Asset\AssetHandlerInterface::setFileHandler()
     */
    public function setFileHandler(\Core\Asset\AssetFilehandlerInterface $filehandler)
    {
        $this->filehandler = $filehandler;
    }



    /**
     * {@inheritDoc}
     * @see \Core\Asset\AssetHandlerInterface::process()
     */
    public function process()
    {

        if (!isset($this->filehandler)) {
            Throw new AssetHandlerException('No filemanager set.');
        }

        if ($this->filehandler->checkTTL() == false) {
            $this->filehandler->setContent($this->getContent());
            $this->filehandler->write();
        }

    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AssetHandlerInterface::getContent()
     */
    abstract public function getContent();
}
