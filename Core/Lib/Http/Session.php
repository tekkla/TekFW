<?php
namespace Core\Lib\Http;

use Core\Lib\Data\DataAdapter;
use Core\Lib\Data\Adapter\Db\Connection;

/**
 * Basic class for session handling
 * For now it's only useful for init the web tree in session
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
final class Session
{

	/**
	 *
	 * @var Database
	 */
	private $adapter;

	public function __construct(DataAdapter $adapter)
	{
		$this->adapter = $adapter;

		// Set handler to overide SESSION
		session_set_save_handler([
			$this,
			"open"
		], [
			$this,
			"close"
		], [
			$this,
			"read"
		], [
			$this,
			"write"
		], [
			$this,
			"destroy"
		], [
			$this,
			"gc"
		]);
	}

	public function get($key)
	{
		if (isset($_SESSION[$key])) {
			return $_SESSION[$key];
		} else {
			Throw new \InvalidArgumentException('Session key "' . $key .'" not found.');
		}
	}

	public function set($key, $val)
	{
		$_SESSION[$key] = $val;
	}

	public function add($key, $val)
	{
		if (! isset($_SESSION[$key])) {
			$_SESSION[$key] = [];
		} else {
			if (!is_array($_SESSION[$key])) {
				$_SESSION[$key] = (array) $_SESSION[$key];
			}
		}

		$_SESSION[$key][] = $val;
	}

	public function exists($key)
	{
		return isset($_SESSION[$key]);
	}

	public function remove($key)
	{
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
	}

	public function init()
	{
		// Start the session
		session_start();

		if (! isset($_SESSION['id_user'])) {
			$_SESSION['id_user'] = 0;
			$_SESSION['logged_in'] = false;
		}

		// Create session id constant
		define('SID', session_id());
	}

	/**
	 * Open session
	 */
	public function open()
	{
		// If successful return true
		return $this->adapter ? true : false;
	}

	/**
	 * Close session
	 */
	public function close()
	{
		// Close the database connection - If successful return true
		return $this->adapter->close() ? true : false;
	}

	/**
	 * Read session
	 */
	public function read($id_session)
	{
		// Set query
		$this->adapter->query('SELECT data FROM {db_prefix}sessions WHERE id_session = :id_session');

		// Bind the Id
		$this->adapter->bindValue(':id_session', $id_session);

		// Attempt execution - If successful return the data
		return $this->adapter->execute() ? $this->adapter->single()['data'] : '';
	}

	/**
	 * Write session
	 */
	public function write($id_session, $data)
	{
		// Create timestamp
		$access = time();

		// Set query
		$this->adapter->query('REPLACE INTO {db_prefix}sessions VALUES (:id_session, :access, :data)');

		// Bind data
		$this->adapter->bindValue(':id_session', $id_session);
		$this->adapter->bindValue(':access', $access);
		$this->adapter->bindValue(':data', $data);

		// Attempt Execution - If successful return true
		return $this->adapter->execute() ? true : false;
	}

	/**
	 * Destroy
	 */
	public function destroy($id_session)
	{
		// Set query
		$this->adapter->query('DELETE FROM {db_prefix}sessions WHERE id_session = :id_session');

		// Bind data
		$this->adapter->bindValue(':id_session', $id_session);

		// Attempt execution - If successful return True
		return $this->adapter->execute() ? true : false;
	}

	/**
	 * Garbage Collection
	 */
	public function gc($max)
	{
		// Calculate what is to be deemed old
		$old = time() - $max;

		// Set query
		$this->adapter->query('DELETE * FROM {db_prefix}sessions WHERE access < :old');

		// Bind data
		$this->adapter->bindValue(':old', $old);

		// Attempt execution
		return $this->adapter->execute() ? true : false;
	}
}
