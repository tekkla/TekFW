<?php
namespace Core\Lib\Errors;

use Core\Lib\Security\User;
use Core\Lib\Http\Router;
use Core\Lib\Ajax\Ajax;
use Core\Lib\Data\DataAdapter;
use Core\Lib\Content\Message;
use Core\Lib\Data\Adapter\Database;
use Core\Lib\Errors\Exceptions\BasicException;
use Core\Lib\Cfg;


/**
 * Error.php
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */

/**
 * throw exceptions based on E_* error types
 */
function ErrorHandler($err_severity, $err_msg, $err_file, $err_line, array $err_context)
{
    global $di;

    // error was suppressed with the @-operator
    if (error_reporting() === 0) {
        return false;
    }

    switch ($err_severity) {
        case E_ERROR:
            $exception = 'ErrorException';
            break;
        case E_WARNING:
            $exception = 'WarningException';
            break;
        case E_PARSE:
            $exception = 'ParseException';
            break;
        case E_NOTICE:
            $exception = 'NoticeException';
            break;
        case E_CORE_ERROR:
            $exception = 'CoreErrorException';
            break;
        case E_CORE_WARNING:
            $exception = 'CoreWarningException';
            break;
        case E_COMPILE_ERROR:
            $exception = 'CompileErrorException';
            break;
        case E_COMPILE_WARNING:
            $exception = 'CoreWarningException';
            break;
        case E_USER_ERROR:
            $exception = 'UserErrorException';
            break;
        case E_USER_WARNING:
            $exception = 'UserWarningException';
            break;
        case E_USER_NOTICE:
            $exception = 'UserNoticeException';
            break;
        case E_STRICT:
            $exception = 'StrictException';
            break;
        case E_RECOVERABLE_ERROR:
            $exception = 'RecoverableErrorException';
            break;
        case E_DEPRECATED:
            $exception = 'DeprecatedException';
            break;
        case E_USER_DEPRECATED:
            $exception = 'UserDeprecatedException';
            break;
    }

    $exception = '\Core\Lib\Errors\Exceptions\\' . $exception;

    $di->get('core.error')->handleException(new $exception($err_msg, 0, $err_severity, $err_file, $err_line));
}

function ExceptionHandler(\Exception $e)
{
    global $di;
    echo $di->get('core.error')->handleException($e);
}

function shutDownFunction()
{
    global $di;

    $error = error_get_last();

    if (!empty($error['type'])) {
        $di->get('core.error')->handleException(new \Exception($error['message'], $error['type']));
    }
}

register_shutdown_function('\Core\Lib\Errors\shutdownFunction');
set_exception_handler('\Core\Lib\Errors\ExceptionHandler');
set_error_handler('\Core\Lib\Errors\ErrorHandler', E_ALL);

ini_set('display_errors', 0);

/**
 * Class for TekFW errors handling
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Error
{

    /**
     *
     * @var Router
     */
    private $router;

    /**
     *
     * @var User;
     */
    private $user;

    /**
     *
     * @var Ajax
     */
    private $ajax;

    /**
     *
     * @var string
     */
    private $error_html = '';

    /**
     *
     * @var Database
     */
    private $adapter;

    /**
     *
     * @var \Exception
     */
    private $exception;

    /**
     *
     * @var string
     */
    private $error_id;

    /**
     *
     * @var Message
     */
    private $message;

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     * Constructor
     *
     * @param Router $router
     * @param User $user
     * @param Ajax $ajax
     * @param DataAdapter $adapter
     * @param Cfg $cfg
     */
    public function __construct(Router $router, User $user, Ajax $ajax, Message $message, DataAdapter $adapter, Cfg $cfg)
    {
        $this->router = $router;
        $this->user = $user;
        $this->ajax = $ajax;
        $this->message = $message;
        $this->adapter = $adapter;
        $this->cfg = $cfg;

        $this->error_id = uniqid('#error_');
    }

    /**
     * Creates html error message
     */
    private function createErrorHtml($dismissable = false)
    {
        $this->error_html = '
        <div class="alert alert-danger' . ($dismissable == true ? ' alert-dismissible' : '') . '" role="alert" id="' . $this->error_id . '">';

        if ($dismissable == true) {
            $this->error_html .= '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
        }

        // Append more informations for admin users
        if ($this->user->isGuest() == true || $this->user->isAdmin() == false || $this->cfg->get('Core', 'skip_security_check') == true) {

            $this->error_html .= '
            <h3 class="no-top-margin">Error</h3>
            <p>Sorry for that! Webmaster has been informed. Please try again later.</p>';
        }
        else {

            $this->error_html .= '
            <p><small>Error Code ' . $this->exception->getCode() . '</small></p>
            <h4 class="no-top-margin">' . $this->exception->getMessage() . '</h4>
            <p><strong>File:</strong> ' . $this->exception->getFile() . ' (Line: ' . $this->exception->getLine() . ')</p>
            <hr>
            <h4>Router</h4>
            <pre><small>' . print_r($this->router->getStatus(), true) . '</small></pre>
            <hr>
            <pre><small>' . $this->exception->getTraceAsString() . '</small></pre>';
        }

        $this->error_html .= '
        </div>';

        return $this->error_html;
    }

    /**
     * Exceptionhandler
     *
     * @param \Exception $e
     * @param boolean $fatal Optional: Flags exception to be a fatal error (Default: false)
     * @param boolean $clean_buffer Optional: Flag to switch buffer clean on/off. (Default: false)
     * @param boolean $error_log Optional: Flag to switch error logging on/off. (Default: true)
     * @param boolean $send_mail Optional: Flag to send error message to admins. (Default: false)
     * @param boolean $to_db Optional: Flag to switch error logging to db driven errorlog on/off. (Default: true)
     *
     * @return boolean|string
     */
    public function handleException(\Exception $e, $fatal = false, $clean_buffer = false, $error_log = true, $send_mail = false, $to_db = true)
    {
        // Store exception
        $this->exception = $e;

        if ($this->exception instanceof BasicException) {
            $fatal = $this->exception->getFatal();
            $clean_buffer = $this->exception->getCleanBuffer();
            $log_error = $this->exception->getErrorLog();
            $send_mail = $this->exception->getSendMail();
            $to_db = $this->exception->getToDb();
        }

        // Always send mail on
        if ($this->exception instanceof \PDOException) {

            $error_log = true;
            $send_mail = true;

            // Prevent db logging on PDOException!!!
            $to_db = - 1;
        }

        if ($fatal == true) {
            // $clean_buffer = true;
        }

        // Clean outpub buffer?
        if ($clean_buffer === true) {
            // ob_clean();
        }

        // Log error
        if ($this->cfg->get('Core', 'error_logger')) {

            // Write to error log?
            if ($error_log == true || $this->cfg->get('Core', 'to_log') == true) {
                $message = $this->exception->getMessage() . ' (' . $this->exception->getFile() . ':' . $this->exception->getLine() . ')';
                error_log($message);
            }

            // Write to db error log? Taker care of avoid flag (-1) due to PDOExceptions
            if ($to_db !== - 1) {

                try {

                    if ($to_db == true || $this->cfg->get('Core', 'to_db') == true) {

                        $this->adapter->query([
                            'method' => 'INSERT',
                            'tbl' => 'error_log',
                            'fields' => [
                                'stamp',
                                'msg',
                                'trace',
                                'file',
                                'line'
                            ],
                            'params' => [
                                ':stamp' => time(),
                                ':msg' => $this->exception->getMessage(),
                                ':trace' => $this->exception->getTraceAsString(),
                                ':file' => $this->exception->getFile(),
                                ':line' => $this->exception->getLine()
                            ]
                        ]);

                        $this->adapter->execute();
                    }
                }
                catch (\Exception $e) {
                    $this->di->get('core.error')->handleException($e);
                }
            }
        }

        // Ajax output
        if ($this->router->isAjax()) {

            $command = $this->ajax->createCommand('Act\Error');
            $command->error($this->createErrorHtml(true), uniqid());
            $command->send();

            return false;
        }

        $this->createErrorHtml(false);

        // return html
        return $this->error_html;
    }

    private function getTraceCalls($ignore = 2)
    {
        $trace = '';

        foreach ($this->exception->getTrace() as $k => $v) {

            if ($k < $ignore) {
                continue;
            }

            array_walk($v['args'], function (&$item, $key) {
                $item = var_export($item, true);
            });

            $trace .= '#' . ($k - $ignore) . ' ' . $v['file'] . '(' . $v['line'] . '): ' . (isset($v['class']) ? $v['class'] . '->' : '') . $v['function'] . "\n";
        }

        return $trace;
    }

    /**
     * Writes error into error log table and php error log
     */
    private function logError()
    {
        $this->adapter->query([
            'tbl' => 'errors',
            'method' => 'INSERT',
            'fields' => [
                'stamp',
                'user',
                'code',
                'message',
                'file',
                'line',
                'trace'
            ],
            // 'exception'
            'params' => [
                ':stamp' => time(),
                ':user' => $this->user->getId(),
                ':code' => $this->exception->getCode(),
                ':message' => $this->exception->getMessage(),
                ':file' => $this->exception->getFile(),
                ':line' => $this->exception->getLine(),
                ':trace' => $this->exception->getTraceAsString()
            ]
        ]);

        $this->adapter->execute();
    }

    /**
     * Write php error log
     */
    private function writeToServerLog()
    {
        error_log($this->exception->getMessage() . ' - ' . $this->exception->getFile() . ' (' . $this->exception->getLine() . ')', 0);
    }

    public function fatal()
    {
        // Clean buffer with all output done so far
        ob_clean();

        // Send 500 http status
        http_response_code(500);

        die('
        <html>
            <head>
                <title>Error</title>
                <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
                <style type="text/css">
                    * { margin: 0; padding: 0; }
                    body { background-color: #aaa; color: #eee; font-family: Sans-Serif; }
                    h1 { margin: 3px 0 7px; }
                    p, pre { margin-bottom: 7px; }
                    pre { padding: 5px; border: 1px solid #333; max-height: 400px; overflow-y: scroll; background-color: #fff; display: block; }
                </style>
            </head>

            <body>
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">' . $this->createErrorHtml(false) . '</div>
                    </div>
                </div>
            </body>

        </html>');
    }
}
