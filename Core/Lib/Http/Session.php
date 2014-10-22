<?php
namespace Core\Lib\Http;

use Core\Lib\Data\Database;

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
	private $db;

	public function __construct(Database $db)
	{
		$this->db = $db;

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
			Throw new \InvalidArgumentException('Session key not found.');
		}
	}

	public function set($key, $val)
	{
		$_SESSION[$key] = $val;
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
		return $this->db ? true : false;
	}

	/**
	 * Close session
	 */
	public function close()
	{
		// Close the database connection - If successful return true
		return $this->db->close() ? true : false;
	}

	/**
	 * Read session
	 */
	public function read($id_session)
	{
		// Set query
		$this->db->query('SELECT data FROM {db_prefix}sessions WHERE id_session = :id_session');

		// Bind the Id
		$this->db->bindValue(':id_session', $id_session);

		// Attempt execution - If successful return the data
		return $this->db->execute() ? $this->db->single()['data'] : '';
	}

	/**
	 * Write session
	 */
	public function write($id_session, $data)
	{
		// Create timestamp
		$access = time();

		// Set query
		$this->db->query('REPLACE INTO {db_prefix}sessions VALUES (:id_session, :access, :data)');

		// Bind data
		$this->db->bindValue(':id_session', $id_session);
		$this->db->bindValue(':access', $access);
		$this->db->bindValue(':data', $data);

		// Attempt Execution - If successful return true
		return $this->db->execute() ? true : false;
	}

	/**
	 * Destroy
	 */
	public function destroy($id_session)
	{
		// Set query
		$this->db->query('DELETE FROM {db_prefix}sessions WHERE id_session = :id_session');

		// Bind data
		$this->db->bindValue(':id_session', $id_session);

		// Attempt execution - If successful return True
		return $this->db->execute() ? true : false;
	}

	/**
	 * Garbage Collection
	 */
	public function gc($max)
	{
		// Calculate what is to be deemed old
		$old = time() - $max;

		// Set query
		$this->db->query('DELETE * FROM {db_prefix}sessions WHERE access < :old');

		// Bind data
		$this->db->bindValue(':old', $old);

		// Attempt execution
		return $this->db->execute() ? true : false;
	}
}
