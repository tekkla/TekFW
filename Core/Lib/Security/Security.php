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
     * Access to current user
     *
     * @var User
     */
    public $user;

    /**
     * Access to Users service
     *
     * @var Users
     */
    public $users;

    /**
     * Access to Group service
     *
     * @var Group
     */
    public $group;

    /**
     * Access to Permission service
     *
     * @var Permission
     */
    public $permission;

    /**
     * Access to Token service
     *
     * @var Token
     */
    public $token;

    /**
     * Access to Login service
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
