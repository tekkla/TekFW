<?php
namespace Core\Lib\Security;

use Core\Lib\Data\DataAdapter;
use Core\Lib\Errors\Exceptions\DatabaseException;
use Core\Lib\Errors\Exceptions\SecurityException;

/**
 * Group.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
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
    private $adapter;

    /**
     */
    function __construct(DataAdapter $adapter)
    {
        $this->adapter = $adapter;

        $this->loadGroups();
    }

    public function loadGroups()
    {
        // Copy default groups to
        // $this->groups = $this->default_groups;
        $this->adapter->query([
            'table' => 'groups',
            'fields' => [
                'id_group',
                'title'
            ],
            'order' => 'id_group'
        ]);
        $this->adapter->execute();

        $groups = $this->adapter->fetchAll();

        foreach ($groups as $g) {
            $this->addGroup($g['id_group'], $g['title']);
        }
    }

    /**
     *
     * @throws DatabaseException
     */
    public function saveGroups()
    {
        // Get usergroups without the default ones
        $groups = array_intersect_key($this->default_groups, $this->groups);

        try {

            // Important: Use a transaction!
            $this->adapter->beginTransaction();

            // Delete current groups
            $this->adapter->query([
                'table' => 'groups',
                'method' => 'DELETE',
            ]);
            $this->adapter->execute();

            // Prepare statement for group insert
            $this->adapter->query([
                'table' => 'groups',
                'method' => 'INSERT',
                'fields' => [
                    'id_group',
                    'title',
                 ]
            ]);

            // Insert the groups each by each into the groups table
            foreach ($groups as $id_group => $title) {
                $this->adapter->bindValue(':id_group', $id_group);
                $this->adapter->bindValue(':title', $title);
                $this->adapter->execute();
            }

            // End end or transaction
            $this->adapter->endTransaction();
        }
        catch (\PDOException $e) {
            $this->adapter->cancelTransaction();
            Throw new DatabaseException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     *
     * @param integer $id_group
     * @param string $title
     *
     * @throws SecurityException
     */
    public function addGroup($id_group, $title)
    {
        // Check for group id already in use
        if (array_key_exists($id_group, $this->groups)) {
            Throw new SecurityException('A usergroup with id "' . $id_group . '" already exists.');
        }

        // Check for group name already in use
        if (array_search($title, $this->groups)) {
            Throw new SecurityException('A usergroup with title "' . $title . '" already exists.');
        }

        $this->groups[$id_group] = $title;
    }

    /**
     * Removes a group from DB and groups list
     *
     * @param integer $id_group
     *
     * @throws DatabaseException
     */
    public function removeGroup($id_group)
    {
        try {

            $this->adapter->beginTransaction();

            // Delete usergroup
            $this->adapter->query([
                'table' => 'groups',
                'method' => 'DELETE',
                'filter' => 'id_group = :id_group',
                'params' => [
                    ':id_group' => $id_group
                ]
            ]);
            $this->adapter->execute();

            // Delete permissions related to this group
            $this->adapter->query([
                'table' => 'permissions',
                'method' => 'DELETE',
                'filter' => 'id_group = :id_group',
                'params' => [
                    ':id_group' => $id_group
                ]
            ]);
            $this->adapter->execute();

            // Remove group from current grouplist
            unset($this->groups[$id_group]);

            $this->adapter->endTransaction();
        }
        catch (\PDOException $e) {
            $this->adapter->cancelTransaction();

            Throw new DatabaseException($e->getMessage(), $e->getCode(), $e->getPrevious());
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
