<?php
namespace Core\Lib\Data\Connectors\Db;

/**
 * Connection.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Connection
{

    /**
     *
     * @var string
     */
    private $db;

    /**
     *
     * @var string
     */
    private $driver = 'mysql';

    /**
     *
     * @var string
     */
    private $host = 'localhost';

    /**
     *
     * @var int
     */
    private $port = 3306;

    /**
     *
     * @var string
     */
    private $user = '';

    /**
     *
     * @var string
     */
    private $password = '';

    /**
     *
     * @var array
     */
    private $options = [];

    /**
     *
     * @var \PDO
     */
    private $dbh = null;

    /**
     * Constructor
     *
     * @param string $db
     *            Name of database to connet to
     * @param string $driver
     *            Optional Name of the PDO driver (Default: 'mysql')
     * @param string $host
     *            Optional host the database lies on (Default: 'localhost')
     * @param number $port
     *            Optional port of the database host (Default: 3306)
     * @param string $user
     *            Optional username to us for connection (Default: 'root')
     * @param string $password
     *            Optional passwort to use for connection (Default empty)
     * @param array $options
     *            Optional array of options to use for connection (Default: empty)
     */
    function __construct($db, $driver = 'mysql', $host = 'localhost', $port = 3306, $user = 'root', $password = '', $options = [])
    {
        $this->db = $db;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->options = $options;

        $this->setDriver($driver);
    }

    /**
     * Connects to database
     *
     * @return PDO
     */
    public function connect()
    {
        if ($this->dbh === null) {
            $dsn = $this->driver . ':host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->db;
            $this->dbh = new \PDO($dsn, $this->user, $this->password, $this->options);
        }

        return $this->dbh;
    }

    /**
     * Closes database connection
     *
     * @return boolean
     */
    public function disconnect()
    {
        $this->dbh = null;

        return true;
    }

    /**
     * Returns database name
     *
     * @return string
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Sets name of database
     *
     * @param string $db
     *            Name of the database
     *
     * @return \Core\Lib\Data\Connectors\Db\Connection
     */
    public function setDb($db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Returns the PDO driver name
     *
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Sets PDO driver name
     *
     * @param string $driver
     *            Name of the PDO driver to use
     *
     * @throws DbException
     *
     * @return \Core\Lib\Data\Connectors\Db\Connection
     */
    public function setDriver($driver)
    {
        $this->errorOnActiveConnection();

        if (! in_array($driver, \PDO::getAvailableDrivers())) {
            Throw new DbException('The PDO driver "' . $driver . '" is not installed.');
        }

        $this->driver = $driver;

        return $this;
    }

    /**
     * Returns set db host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets db host
     *
     * @param string $host
     *            Adress of the host the database resides on
     *
     * @throws DbException
     *
     * @return \Core\Lib\Data\Connectors\Db\Connection
     */
    public function setHost($host)
    {
        $this->errorOnActiveConnection();

        $this->host = $host;

        return $this;
    }

    /**
     * Returns server port
     *
     * @return the int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets port number to use
     *
     * @param int $port
     *            Portnumber to use
     *
     * @throws DbException
     *
     * @return \Core\Lib\Data\Connectors\Db\Connection
     */
    public function setPort($port)
    {
        $this->errorOnActiveConnection();

        $this->port = $port;

        return $this;
    }

    /**
     *
     * @return the string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the username to use on connection
     *
     * @param string $user
     *            Username
     *
     * @throws DbException
     *
     * @return \Core\Lib\Data\Connectors\Db\Connection
     */
    public function setUser($user)
    {
        $this->errorOnActiveConnection();

        $this->user = $user;

        return $this;
    }

    /**
     * Sets passwort to use
     *
     * @param string $password
     *            The password
     *
     * @throws DbException
     *
     * @return \Core\Lib\Data\Connectors\Db\Connection
     */
    public function setPassword($password)
    {
        $this->errorOnActiveConnection();

        $this->password = $password;

        return $this;
    }

    /**
     * Returns set connection options
     *
     * @return the array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets connection options
     *
     * @param array $options
     *            Array of connection options
     *
     * @throws DbException
     *
     * @return \Core\Lib\Data\Connectors\Db\Connection
     */
    public function setOptions(array $options)
    {
        $this->errorOnActiveConnection();

        $this->options = $options;

        return $this;
    }

    /**
     * Throws exception on an active databasehandler connection
     *
     * @throws DbException
     */
    private function errorOnActiveConnection()
    {
        if ($this->dbh !== null) {
            Throw new DbException('You cannot change databse connection properties while the connection is active.');
        }
    }
}

