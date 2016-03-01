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
     *            Access to current User service
     * @param Users $users
     *            Access to Users service
     * @param Group $group
     *            Access to Group service
     * @param Token $token
     *            Access to token service
     * @param Login $login
     *            Access to Login service
     */
    public function __construct(User $user, Users $users, Group $group, Token $token, Login $login)
    {
        $this->user = $user;
        $this->users = $users;
        $this->group = $group;
        $this->token = $token;
        $this->login = $login;
    }
}
