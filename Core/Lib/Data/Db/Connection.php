<?php
namespace Core\Lib\Data\Db;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 *
 */
class Connection
{

	/**
	 * Databasename
	 *
	 * @var string
	 */
	private $db;

	/**
	 * PDO Driver
	 *
	 * @var string
	 */
	private $driver = 'mysql';

	/**
	 * DB host
	 *
	 * @var string
	 */
	private $host = 'localhost';

	/**
	 * Serverport
	 *
	 * @var int
	 */
	private $port = 3306;

	/**
	 * Username
	 *
	 * @var string
	 */
	private $user = '';

	/**
	 * Password
	 *
	 * @var string
	 */
	private $password = '';

	/**
	 * Connection options
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 *
	 * @var \PDO
	 */
	private $dbh;

	function __construct($db, $driver = 'mysql', $host = 'localhost', $port = 3306, $user = '', $password = '', $options = [])
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
		$dsn = $this->driver . ':host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->db;

		$this->dbh = new \PDO($dsn, $this->user, $this->password, $this->options);

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
	 */
	public function setDb($db)
	{
		$this->db = $db;
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
	 * Sets PDO driver name.
	 *
	 * @param string $driver
	 */
	public function setDriver($driver)
	{
		if (!in_array($driver, \PDO::getAvailableDrivers())) {
			throw new \InvalidArgumentException('The PDO driver "' . $driver . '" is not installed.');
		}

		$this->driver = $driver;
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
	 * Sets db host.
	 *
	 * @param string $server
	 */
	public function setHost($host)
	{
		$this->host = $host;
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
	 *
	 * @param $port
	 */
	public function setPort($port)
	{
		$this->port = $port;
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
	 *
	 * @param $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 *
	 * @return the string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 *
	 * @param $password
	 */
	public function setPassword($password)
	{
		$this->password = $password;
	}

	/**
	 *
	 * @return the array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 *
	 * @param array $options
	 */
	public function setOptions(array $options)
	{
		$this->options = $options;
	}
}

