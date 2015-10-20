<?php
namespace Core\Lib\Security;

use Core\Lib\Cfg;
use Core\Lib\Http\Session;
use Core\Lib\Http\Cookie;
use Core\Lib\Data\DataAdapter;
use Core\Lib\Logging\Logging;
use Core\Lib\Data\Adapter\Database;
use Core\Lib\Traits\DebugTrait;

/**
 * Security.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Security
{

    use DebugTrait;

    /**
     * Cookiename
     *
     * @var string
     */
    private $cookie_name = 'tekfw98751822';

    /**
     * Pepper instead of salt.
     * I like it more!
     *
     * @var string
     */
    private $pepper = 'Sfgg$%fsa""sdfsddf#123WWdä,-$';

    /**
     * Days until cookies expire
     *
     * @var int
     */
    private $days = 30;

    /**
     * Timestamp for cookie expire date
     *
     * @var int
     */
    private $expire_time = 0;

    /**
     *
     * @var Database
     */
    private $adapter;

    /**
     *
     * @var Cookie
     */
    private $cookie;

    /**
     *
     * @var User
     */
    private $user;

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     *
     * @var Session
     */
    private $session;

    /**
     *
     * @var Group
     */
    private $group;

    /**
     *
     * @var Permission
     */
    private $permission;

    /**
     *
     * @var Logging
     */
    private $logging;

    /**
     * Constructor
     *
     * @param DataAdapter $adapter
     * @param Cfg $cfg
     * @param Session $session
     * @param Cookie $cookie
     * @param User $user
     * @param Group $group
     * @param Permission $permission
     * @param Logging $logging
     */
    public function __construct(DataAdapter $adapter, Cfg $cfg, Session $session, Cookie $cookie, User $user, Group $group, Permission $permission, Logging $logging)
    {
        $this->adapter = $adapter;
        $this->cfg = $cfg;
        $this->session = $session;
        $this->cookie = $cookie;
        $this->user = $user;
        $this->group = $group;
        $this->permission = $permission;
        $this->logging = $logging;
    }

    /**
     * Initiates security model.
     * Sets object parameter by using config values.
     * Tries to autologin the current user
     */
    public function init()
    {
        // Set parameter
        if ($this->cfg->exists('Core', 'cookie_name')) {
            $this->cookie_name = $this->cfg->get('Core', 'cookie_name');
        }

        if ($this->cfg->exists('Core', 'security_pepper')) {
            $this->pepper = $this->cfg->get('Core', 'security_pepper');
        }

        if ($this->cfg->exists('Core', 'security_autologin_expire_days')) {
            $this->days = $this->cfg->get('Core', 'security_autologin_expire_days');
        }

        // Create random session token
        $this->generateRandomSessionToken();

        // Try autologin
        $this->doAutoLogin();
    }

    /**
     * Sets the cookie name to be used in autologin cookie name
     *
     * @param string $cookie_name
     * @return \Core\Lib\Security
     */
    public function setCookieName($cookie_name)
    {
        $this->cookie_name = $cookie_name;

        return $this;
    }

    /**
     * Sets custom pepper string used to create usertoken
     *
     * @param string $pepper
     *
     * @return \Core\Lib\Security
     */
    public function setPepper($pepper)
    {
        $this->pepper = $pepper;

        return $this;
    }

    /**
     * Sets the number of days the login cookie should be valid when user requests autologin.
     *
     * @param int $days
     *
     * @return \Core\Lib\Security
     */
    public function setDaysUntilCookieExpires($days)
    {
        $this->days = (int) $days;

        // Auto calculate expiretime
        $this->generateExpireTime();

        return $this;
    }

    /**
     * Returns the set cookiename
     *
     * @return string
     */
    public function getCookieName()
    {
        return $this->cookie_name;
    }

    /**
     * Returns set pepper string.
     *
     * @return string
     */
    public function getPepper()
    {
        return $this->pepper;
    }

    /**
     * Returns the number of days the autologin cookie stys valid
     *
     * @return number
     */
    public function getDaysUntilCookieExpires()
    {
        return $this->days;
    }

    /**
     * Validates the provided data against user data to perform user login.
     * Offers option to activate autologin.
     *
     * @param unknown $login Login name
     * @param unknown $password Password to validate
     * @param boolean $remember_me Option to activate autologin
     *
     * @return boolean|mixed
     */
    public function login($username, $password, $remember_me = false)
    {
        // Empty username or password
        if (empty(trim($username)) || empty(trim($password))) {
            return false;
        }

        // Try to load user from db
        $this->adapter->qb([
            'table' => 'users',
            'fields' => [
                'id_user',
                'password'
            ],
            'filter' => 'username=:username',
            'params' => [
                ':username' => $username
            ]
        ]);

        $login = $this->adapter->single();

        // No user found => login failed
        if (! $login) {

            // Log login try with not existing username
            $this->logLogin(0, $username, false, true);
            return false;
        }

        // @todo Append pepper. But is it neccessary?
        $password .= $this->pepper;

        // Password ok?
        if (password_verify($password, $login['password'])) {

            // Needs hash to be updated?
            if (password_needs_rehash($login['password'], PASSWORD_DEFAULT)) {
                $this->adapter->qb([
                    'table' => 'users',
                    'method' => 'UPDATE',
                    'fields' => [
                        'password'
                    ],
                    'filter' => 'id_user = :id_user',
                    'params' => [
                        ':password' => password_hash($password, PASSWORD_DEFAULT),
                        ':id_user' => $login['id_user']
                    ]
                ], true);
            }

            // Load User
            $this->user->load($login['id_user']);

            // Store essential userdata in session
            $this->session->set('logged_in', true);
            $this->session->set('id_user', $login['id_user']);

            // Remember for autologin?
            if ($remember_me === true) {
                $this->setAutoLoginCookies($login['id_user']);
            }

            // Log successfull login
            $this->logLogin($login['id_user']);

            // Login is ok, return user id
            return $login['id_user'];
        }
        else {

            // Log try with wrong password and start ban counter
            $this->logLogin(0,false,true, true);
            return false;
        }
    }

    /**
     * Logout of the user and clean up autologin cookies.
     */
    public function logout()
    {

        $id_user = $this->session->get('id_user');

        // Clean up session
        $this->session->set('autologin_failed', true);
        $this->session->set('id_user', 0);
        $this->session->set('logged_in', false);

        // Calling logout means to revoke autologin cookies
        $this->cookie->remove($this->cookie_name . 'Token');


        $this->logging->logout('User:' . $id_user);

    }

    /**
     * Tries to autologin the user by comparing token stored in cookie with a generated token created of user credentials.
     *
     * @return boolean
     */
    public function doAutoLogin()
    {
        // User already logged in?
        if ($this->session->exists('logged_in') && $this->session->get('logged_in') === true) {
            $this->user->load($this->session->get('id_user'));
            return true;
        }

        // Cookiename of token cookie
        $cookie = $this->cookie_name . 'Token';

        // No autologin when autologin already failed
        if ($this->session->exists('autologin_failed')) {

            // Remove fragments/all of autologin cookies
            $this->cookie->remove($cookie);

            // Remove the flag which forces the log off
            $this->session->remove('autologin_failed');

            return false;
        }

        // No autologin cookie no autologin ;)
        if (! $this->cookie->exists($cookie)) {
            return false;
        }

        // Let's find the user for the token in cookie
        list ($selector, $token) = explode(':', $this->cookie->get($cookie));

        $this->adapter->qb([
            'table' => 'auth_tokens',
            'fields' => [
                'id_auth_token',
                'id_user',
                'token',
                'selector',
                'expires'
            ],
            'filter' => 'selector=:selector',
            'params' => [
                ':selector' => $selector
            ]
        ]);
        $data = $this->adapter->all();

        foreach ($data as $auth_token) {

            // Check if token is expired?
            if (strtotime($auth_token['expires']) < time()) {
                $this->deleteAuthTokenFromDb($auth_token['id_user']);
            }

            // Matches the hash in db with the provided token?
            if (hash_equals($auth_token['token'], $token)) {

                // Refresh autologin cookie so the user stays logged in
                // as long as he comes back before his cookie has been expired.
                $this->setAutoLoginCookies($auth_token['id_user']);

                // Login user, set session flags and return true
                $this->session->set('logged_in', true);
                $this->session->set('id_user', $auth_token['id_user']);

                // Remove possible autologin failed flag
                $this->session->remove('autologin_failed');

                // And finally load user
                $this->user->load($auth_token['id_user']);

                return true;
            }
        }

        // !!! Reaching this point means autologin validation failed in all ways
        // Clean up the mess and return a big bad fucking false as failed autologin result.

        // Remove token cookie
        $this->cookie->remove($cookie);

        // Set flag that autologin failed
        $this->session->set('autologin_failed', true);

        // Set logged in flag explicit to false
        $this->session->set('logged_in', false);

        // Set id of user explicit to 0 (guest)
        $this->session->set('id_user', 0);

        // sorry, no autologin
        return false;
    }

    /**
     * Removes the token of a user from auth_token table and all tokens expired.
     *
     * @param int $id_user
     */
    private function deleteAuthTokenFromDb($id_user)
    {
        // Yep! Delete token and return false for failed autologin
        $this->adapter->qb([
            'table' => 'auth_tokens',
            'method' => 'DELETE',
            'filter' => 'expires < :expires OR id_user=:id_user',
            'params' => [
                ':expires' => date('Y-m-d H:i:s'),
                ':id_user' => $id_user
            ]
        ], true);
    }

    /**
     * Set auto login cookies with user generated token
     *
     * @param int $id_user
     *
     * @throws Error
     *
     * @todo Take care of cookie parameters
     */
    private function setAutoLoginCookies($id_user)
    {
        // Check for empty expire time and generate time if it is empty
        if (! $this->expire_time) {
            $this->generateExpireTime();
        }

        $selector = bin2hex($this->generateRandomToken(6));
        $token = $this->generateRandomToken(64);

        $hash = hash('sha256', $token);

        // Store selector and hash in DB
        $this->adapter->qb([
            'table' => 'auth_tokens',
            'method' => 'INSERT',
            'fields' => [
                'selector',
                'token',
                'id_user',
                'expires'
            ],
            'params' => [
                ':selector' => $selector,
                ':token' => $hash,
                ':id_user' => $id_user,
                ':expires' => date('Y-m-d H:i:s', $this->expire_time)
            ]
        ], true);

        // Set autologin token cookie only when token is stored successfully in db!!!
        if ($this->adapter->lastInsertId()) {

            // Get new cookie
            $cookie = $this->cookie->getInstance();

            // Expiretime for both cookies
            $cookie->setExpire($this->expire_time);

            // Set token cookie
            $cookie->setName($this->cookie_name . 'Token');
            $cookie->setValue($selector . ':' . $hash);
            $cookie->set();
        }
    }

    /**
     * Returns login state of current user
     *
     * @return boolean
     */
    public function loggedIn()
    {
        return $this->session->get('logged_in') == true && $this->session->get('id_user') > 0 ? true : false;
    }

    /**
     * Checks login state and overrides the router current data to force display of loginform.
     *
     * @return boolean
     */
    public function forceLogin()
    {
        if ($this->loggedIn()) {
            return true;
        }

        /* @var $router \Core\Lib\Http\Router */
        $router = $this->di->get('core.http.router');
        $router->setApp('Core');
        $router->setController('Security');
        $router->setAction('Login');

        return false;
    }

    /**
     * Generates the expiring timestamp for cookies
     *
     * @return number
     */
    private function generateExpireTime()
    {
        // Create expire date of autologin
        return $this->expire_time = time() + 3600 * 24 * $this->days;
    }

    /**
     * Returns the list of permissions the current user owns
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->user->getPermissions();
    }

    public function getGroups()
    {
        return $this->group->getGroups();
    }

    /**
     * Checks user access by permissions
     *
     * @param array $perms
     * @param boolean $force
     *
     * @return boolean
     */
    public function checkAccess($perms = [], $force = false)
    {
        // Guests are not allowed by default
        if ($this->user->isGuest()) {
            $this->debugFbLog('is Guest');
            return false;
        }

        // Allow access to all users when perms argument is empty
        if (empty($perms)) {
            $this->debugFbLog('no Perms');
            return true;
        }

        // Administrators are supermen :P
        if ($this->user->isAdmin()) {
            $this->debugFbLog('is Admin');
            $this->debugFbLog($this->user->getGroups());
            return true;
        }

        // Explicit array conversion of perms arg
        if (! is_array($perms)) {
            $perms = (array) $perms;
            $this->debugFbLog($perms);
        }

        // User has the right to do this?
        if (count(array_intersect($perms, $this->user->getPermissions())) > 0) {
            return true;
        }

        // You aren't allowed, by default.
        return false;
    }

    /**
     * Generates a random session token.
     *
     * @param number $size
     *
     * @return Security
     */
    public function generateRandomSessionToken($size = 32)
    {
        if (! $this->session->exists('token')) {

            // Store random token in session
            $this->session->set('token', hash('sha256', $this->generateRandomToken($size)));
        }

        return $this;
    }

    /**
     * Generates a random token.
     *
     * @param number $size
     *
     * @return string
     */
    public function generateRandomToken($size = 32)
    {
        return function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes($size) : mcrypt_create_iv($size);
    }

    /**
     * Validates $token against the random token stored in session.
     *
     * @param string $token
     *
     * @return boolean
     */
    public function validateRandomSessionToken($token)
    {
        return $token == $this->session->get('token');
    }

    /**
     * Checks $_POST for sent token value and validates this token (if present) against the random token stored in session.
     *
     * @return boolean
     */
    public function validatePostToken()
    {
        // Show warning in error log when using a form without a token
        if (! isset($_POST['token'])) {
            return false;
        }

        // Token sent so let's check it
        if (isset($_POST['token'])) {

            if (! $this->validateRandomSessionToken($_POST['token'])) {
                return false;
            }

            unset($_POST['token']);

            return true;
        }
    }

    /**
     * Security method to log suspisious actions and start banning process.
     *
     * @param string $msg Message to log
     * @param boolean|int $ban Set this to the number of tries the user is allowed to do other suspicious things until he gets banned.
     *
     * @return Security
     */
    public function logSuspicious($msg, $ban = false)
    {
        $this->logging->suspicious($msg);

        return $this;
    }

    /**
     * Logs login attemps.
     *
     * @param integer $id_user
     * @param boolean $username
     * @param boolean $password
     * @param boolean $ban
     */
    private function logLogin($id_user, $username = false, $password = false, $ban = false)
    {
        $text = 'Login for user:' . $id_user;
        $state = 0;

        if (! $username || !$password) {

            $text .= ' failed!';

            if (!$username) {
                $state = 1;
            }

            if (!$password) {
                $state = 2;
            }
        }
        else  {
            $text .= ' success';
        }


        $this->logging->login($text, $state);

        // Start ban process only when requested and only when state
        // indicates a login error from user credentials
        if ($state > 0 && $ban) {
            $this->logging->ban('Ban event');
        }
    }

    /**
     * Ban check
     */
    public function checkBan()
    {
            // Get max tries until get banned from config
            $max_tries = $this->cfg->get('Core', 'ban_max_counter');

            // Max tries of 0 means no ban check at all
            if ($max_tries == 0) {
                return true;
            }

            // Get ban counter for the visitors IP
            $counter = $this->logging->countBanLogEntries($_SERVER['REMOTE_ADDR']);

            // As long as the ban counter is smaller than allowed the check is ok.
            if ($counter < $max_tries) {
                return true;
            }

            //
    }
}
