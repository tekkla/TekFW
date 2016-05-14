<?php
namespace Core\Config;

/**
 * ConfigFileRepository.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class ConfigFileRepository implements ConfigRepositoryInterface
{

    /**
     *
     * @var string
     */
    private $filename;

    /**
     * Sets the filename from where the config has to be loaded and saved to
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Config\ConfigRepositoryInterface::read()
     *
     */
    public function read()
    {
        if (empty($this->filename)) {
            Throw new ConfigException(sprintf('There is no filename set for %s', __CLASS__));
        }

        if (! file_exists($this->filename)) {
            Throw new ConfigException(sprintf('Config file $s is does not exists', $this->filename));
        }

        if (! is_readable($this->filename)) {
            Throw new ConfigException(sprintf('Config file $s is not readable', $this->filename));
        }

        $array = include ($this->filename);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Config\ConfigRepositoryInterface::write()
     *
     */
    public function write($data)
    {
        if (empty($this->filename)) {
            Throw new ConfigException(sprintf('There is no filename set for %s', __CLASS__));
        }

        try {
            file_put_contents($this->filename, 'return ' . var_export($data, true));
        }
        catch (\Throwable $t) {
            Throw new ConfigException($t->getMessage(), $t->getCode());
        }
    }
}
