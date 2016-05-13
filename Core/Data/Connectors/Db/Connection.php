<?php
namespace Core\Data\Connectors\Db;

/**
 * Connection.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Connection extends \PDO
{

    /**
     *
     * @var string
     */
    private $dsn;

    /**
     *
     * {@inheritDoc}
     *
     * @see PDO::__construct()
     */
    public function __construct($dsn, $username = null, $passwd = null, $options = null)
    {
        $this->dsn = $dsn;

        parent::__construct($dsn, $username, $passwd, $options);
    }

    /**
     * Returns the prefix from DSN
     *
     * @return string|null
     */
    public function getPrefix()
    {
        if (isset($this->dsn)) {
            return explode(':', $this->dsn)[0];
        }
    }

    /**
     * Returns content of PDO::ATTR_SERVER_INFO
     *
     * @return string
     */
    public function getServerInfo()
    {
        return $this->getAttribute(self::ATTR_SERVER_INFO);
    }

    /**
     * Returns set DSN
     *
     * @return string|null
     */
    public function getDSN()
    {
        if (isset($this->dsn)) {
            return $this->dsn;
        }
    }
}

