<?php
namespace Core\Lib\Abstracts;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 *
 */
abstract class ErrorAbstract
{

	use \Core\Lib\Traits\TextTrait;

    /**
     * Error code
     *
     * @var int
     */
    protected $code = 0;

    /**
     * Parameterlist provided by constructor
     *
     * @var array
     */
    protected $params = [];

    /**
     * Flag to set the error to be fatal.
     * Defaul: false
     *
     * @var bool
     */
    protected $fatal = false;

    /**
     * Url to redirect to
     *
     * @var string
     */
    protected $redirect = '';

    /**
     * User message string
     *
     * @var string
     */
    protected $user_message;

    /**
     * Admin message string
     *
     * @var string
     */
    protected $admin_message;

    /**
     * Log message string
     *
     * @var string
     */
    protected $log_message;

    /**
     * List of error codes
     *
     * @var array
     */
    protected $codes = [];

    /**
     * Flag to log error
     *
     * @var bool
     */
    protected $log = false;

    /**
     * Flag to wrap error in a box
     *
     * @var bool
     */
    protected $box = false;

    /**
     * Constructor
     *
     * @param string $message Message provided by Error object
     * @param int $code Errorcode
     * @param array $params Optional parameters
     */
    function __construct($message = '', $code = 0, $params = array())
    {
        // On empty message the default error txt will be used
        if (! $message) {
            $message = $this->txt('error_general', 'Core');

            $this->admin_message = $message;
            $this->user_message = $message;
        }

        // None array messages are for admins. Users will get a default error.
        if ($message && ! is_array($message)) {
            $this->admin_message = $message;
            $this->user_message = $this->txt('error_general', 'Core');
        }

        // Message as array means:
        // The first entry with text for admins
        // The second entry with text for normal users
        if (is_array($message)) {
            // Set default error message if not set in message array
            if (! isset($message[1]))
                $message[1] = $this->txt('error_general', 'Core');

            $this->admin_message = $message[0];
            $this->user_message = $message[1];
        }

        // Store provided code and parameter
        $this->code = $code;
        $this->params = $params;

        // Set admin message as log message
        $this->log_message = $this->admin_message;
    }

    /**
     * Returns error message.
     * Checks the user type and returns the message defined for him.
     *
     * @return string
     * @todo Add user access check
     */
    public function getMessage()
    {
        return $this->user_message;
    }

    /**
     * Returns admin error string
     *
     * @return sting
     */
    public function getAdminMessage()
    {
        return $this->admin_message;
    }

    /**
     * Returns user error string
     *
     * @return string
     */
    public function getUserMessage()
    {
        return $this->user_message;
    }

    /**
     * Returns log error string
     *
     * @return sting
     */
    public function getLogMessage()
    {
        return $this->log_message;
    }

    /**
     * Returns error code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Checks for set redirect
     *
     * @return boolean
     */
    public function isRedirect()
    {
        return $this->redirect !== false;
    }

    /**
     * Returns redirect value
     *
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Checks for set redirect
     *
     * @return boolean
     */
    public function isFatal()
    {
        return $this->fatal !== false;
    }

    /**
     * Returns log state
     *
     * @return boolean
     */
    public function logError()
    {
        return $this->log !== false;
    }

    /**
     * Error processor
     */
    public function process()
    {
        if (isset($this->codes[$this->code]) && method_exists($this, 'process' . $this->codes[$this->code]))
            $this->{'process' . $this->codes[$this->code]}();
        else
            $this->processGeneral();
    }

    /**
     * General processor when error handler has no specific process methods defined.
     */
    protected function processGeneral()
    {
        $this->admin_message = '
		<hError (Code: ' . $this->code . ')</h
		<p class="lead' . $this->admin_message . '</';

        if ($this->params)
            $this->admin_message .= '
			<hParameter</h
			<pr' . print_r($this->params, true) . '</pr';

        $trace = $this->trace(5);

        if ($trace)
            $this->admin_message .= '
			<hTrace</h
			<pr' . $trace . '</pr';
    }

    /**
     * Returns box wrapper flag
     *
     * @return boolean
     */
    public function inBox()
    {
        return $this->box;
    }
}

