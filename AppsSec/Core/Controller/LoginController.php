<?php
namespace AppsSec\Core\Controller;

use Core\Amvc\Controller;

/**
 * SecurityController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class LoginController extends Controller
{

    protected $access = [];

    public function Index()
    {
        $this->redirect('Login', $this->router->getParam());
    }

    /**
     *
     * @throws \Core\Errors\Exceptions\InvalidArgumentException
     */
    public function Login()
    {
        if ($this->security->login->loggedIn()) {
            $this->redirect('AlreadyLoggedIn');
            return;
        }

        if ($this->security->users->checkBan()) {
            $this->redirectExit($this->url('index'));
        }

        $data = $this->http->post->get();

        if ($data) {

            // Validate the send login data
            $this->model->checkLoginData($data);

            // Errors on login data check meands that login failed
            if ($this->model->hasErrors()) {
                $logged_in = false;
            }

            // Data ok. Let's run login process.
            else {

                $logged_in = $this->security->login->doLogin($data['username'], $data['password'], isset($data['remember']) ? (bool) $data['remember'] : false);

                // Login successful? Redirect to index page
                if ($logged_in == true) {

                    $route = $this->cfg->get('home.user.route');
                    $params = parse_ini_string($this->cfg->get('home.user.params'));
                    $url = $this->url($route, $params);

                    return $this->redirectExit($url);
                }
            }

            // Login failed?
            if (empty($logged_in)) {

                // Store failed attempt as flag in session
                $_SESSION['Core']['login_failed'] = true;

                $this->model->addError('@', $this->text('login.failed'));
            }
        }
        else {

            // Get container
            $data = [];
        }

        // Autologin on or off by default?
        $data['remember'] = $this->cfg->get('security.login.autologin.active');

        $fd = $this->getFormDesigner('core-login');
        $fd->setName('core-login');
        $fd->mapData($data);
        $fd->mapErrors($this->model->getErrors());

        if (isset($_SESSION['Core']['display_activation_notice'])) {
            $group = $fd->addGroup();
            $group->addCss('alert alert-info');
            $group->setRole('alert');
            $group->setInner($this->text('register.activation.notice'));
        }

        // Create element group
        $group = $fd->addGroup();

        $controls = [
            'username' => 'text',
            'password' => 'password'
        ];

        if ($data['remember']) {
            $controls['remember'] = 'checkbox';
        }

        foreach ($controls as $name => $type) {

            // Create control object
            $control = $group->addControl($type, $name);

            // Label and placeholder
            $text = $this->text('login.form.' . $name);

            $methods = [
                'setPlaceholder'
            ];

            foreach ($methods as $method_name) {
                if (method_exists($control, $method_name)) {
                    $control->$method_name($text);
                }
            }

            switch ($name) {
                case 'username':
                case 'password':
                    $control->noLabel();
                    break;

                case 'remember':
                    $control->setLabel($this->text('login.form.remember'));
                    break;
            }
        }

        // login button
        $control = $group->addControl('Submit');
        $control->setUnbound();
        $control->addCss('btn-block');

        $icon = $this->html->create('Elements\Icon');
        $icon->useIcon('key');

        $control->setInner($icon->build() . ' ' . $this->text('login.form.login'));

        // @TODO Create links for 'Forgot Password?' and 'New user?'

        #if ($this->cfg->get('security.login.reset_password')) {}

        #if ($this->cfg->get('security.login.register')) {}

        $this->setVar([
            'headline' => $this->text('login.text'),
            'form' => $fd
        ]);

        $this->page->breadcrumbs->createActiveItem($this->text('user.action.login'));
    }

    public function Logout()
    {
        $this->security->login->doLogout();

        return $this->redirectExit($this->cfg->get('url.home'));
    }

    public function AlreadyLoggedIn()
    {
        $this->setVar('loggedin', $this->text('already_loggedin'));
    }
}
