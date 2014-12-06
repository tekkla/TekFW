<?php
namespace Core\Lib\Http;

use Core\Lib\Data\DataAdapter;

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
		$this->adapter->query([
			'tbl' => 'sessions',
			'field' => 'data',
			'filter' => 'id_session = :id_session',
			'params' => [
				':id_session' => $id_session
			]
		]);

		$data = $this->adapter->single();

		return $data ? $data['data'] : '';
	}

	/**
	 * Write session
	 */
	public function write($id_session, $data)
	{
		// Create timestamp
		$access = time();

		// Set query
		$this->adapter->query([
			'method' => 'REPLACE INTO',
			'tbl' => 'sessions',
			'values' => [
				':id_session',
				':access',
				':data'
			],
			'params' => [
				':id_session' => $id_session,
				':access' => $access,
				':data' => $data
			]
		]);

		// Attempt Execution - If successful return true
		return $this->adapter->execute() ? true : false;
	}

	/**
	 * Destroy
	 */
	public function destroy($id_session)
	{
		// Set query
		$this->adapter->query([
			'method' => 'DELETE',
			'tbl' => 'sessions',
			'filter' => 'id_session=:id_session',
			'params' => [
				'id_session' => $id_session
			]
		]);

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
		$this->adapter->query([
			'method' => 'DELETE',
			'tbl' => 'sessions',
			'filter' => 'access<:old',
			'params' => [
				':old' => $old
			]
		]);

		// Attempt execution
		return $this->adapter->execute() ? true : false;
	}
}
