<?php
namespace Core\Lib\Security;

/**
 * Security.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Security
{

    /**
     *
     * @var User
     */
    public $user;

    /**
     *
     * @var Users
     */
    public $users;

    /**
     *
     * @var Group
     */
    public $group;

    /**
     *
     * @var Permission
     */
    public $permission;

    /**
     *
     * @var Token
     */
    public $token;

    /**
     *
     * @var Login
     */
    public $login;

    /**
     * Constructor
     *
     * @param User $user
     * @param Users $users
     * @param Group $group
     * @param Permission $permission
     * @param Token $token
     * @param Login $login
     */
    public function __construct(User $user, Users $users, Group $group, Permission $permission, Token $token, Login $login)
    {
        $this->user = $user;
        $this->users = $users;
        $this->group = $group;
        $this->permission = $permission;
        $this->token = $token;
        $this->login = $login;
    }
}
