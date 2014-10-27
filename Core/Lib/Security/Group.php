<?php
namespace Core\Lib\Security;

use Core\Lib\Data\Db\Database;

/**
 *
 * @author Michael
 *
 */
class Group
{

	/**
	 * Default groups that cannot be overridden
	 *
	 * @var array
	 */
	private $default_groups = [
		- 1 => 'guest',
		1 => 'admin',
		2 => 'user'
	];

	/**
	 * Groups array we work with
	 *
	 * @var array
	 */
	private $groups = [];

	/**
	 * DB Handler
	 *
	 * @var Database
	 */
	private $db;

	/**
	 */
	function __construct(Database $db)
	{
		$this->db = $db;

		$this->loadGroups();
	}

	public function loadGroups()
	{
		// Copy default groups to
		#$this->groups = $this->default_groups;

		$this->db->query('SELECT id_group, title FROM {db_prefix}groups ORDER BY id_group');
		$this->db->execute();

		$groups = $this->db->resultset();

		foreach ($groups as $g) {
			$this->addGroup($g['id_group'], $g['title']);
		}
	}

	/**
	 *
	 * @throws \PDOException
	 */
	public function saveGroups()
	{
		// Get usergroups without the default ones
		$groups = array_intersect_key($this->default_groups, $this->groups);

		try {

			// Important: Use a transaction!
			$this->db->beginTransaction();

			// Delete current groups
			$this->db->query('DELETE FROM {db_prefix}groups');
			$this->db->execute();

			// Prepare statement for group insert
			$this->db->query('INSERT INTO {db_prefix}groups (id_group, title) VALUES (:id_group, :title)');

			// Insert the groups each by each into the groups table
			foreach ($groups as $id_group => $title) {
				$this->db->bindValue(':id_group', $id_group);
				$this->db->bindValue(':title', $title);
				$this->db->execute();
			}

			// End end or transaction
			$this->db->endTransaction();
		}
		catch (\PDOException $e) {
			$this->db->cancelTransaction();
			Throw new \PDOException($e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 *
	 * @param unknown $id_group
	 * @param unknown $title
	 *
	 * @throws \RuntimeException
	 */
	public function addGroup($id_group, $title)
	{
		// Check for group id already in use
		if (array_key_exists($id_group, $this->groups)) {
			Throw new \RuntimeException('A usergroup with id "' . $id_group . '" already exists.');
		}

		// Check for group name already in use
		if (array_search($title, $this->groups)) {
			Throw new \RuntimeException('A usergroup with title "' . $title . '" already exists.');
		}

		$this->groups[$id_group] = $title;
	}

	/**
	 * Removes a group from DB and groups list
	 *
	 * @param integer $id_group
	 */
	public function removeGroup($id_group)
	{
		try {

			$this->db->beginTransaction();

			// Delete usergroup
			$this->db->query('DELETE FROM {db_prefix}groups WHERE id_group = :id_group');
			$this->db->bindValue(':id_group', $id_group);
			$this->db->execute();

			// Delete permissions related to this group
			$this->db->query('DELETE FROM {db_prefix}permissions WHERE id_group = :id_group');
			$this->db->bindValue(':id_group', $id_group);
			$this->db->execute();

			// Remove group from current grouplist
			unset($this->groups[$id_group]);

			$this->db->endTransaction();

		}
		catch (\PDOException $e)
		{
			$this->db->cancelTransaction();

			Throw new \PDOException($e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Returns all groups
	 *
	 * @return array
	 */
	public function getGroups()
	{
		return $this->groups;
	}
}
