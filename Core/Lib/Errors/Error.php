<?php
namespace Core\Lib\Errors;

use Core\Lib\Security\User;
use Core\Lib\Http\Router;
use Core\Lib\Ajax\Ajax;
use Core\Lib\Data\DataAdapter;
use Core\Lib\Content\Message;
use Core\Lib\Data\Adapter\Database;

/**
 * throw exceptions based on E_* error types
 */
function ErrorHandler($err_severity, $err_msg, $err_file, $err_line, array $err_context)
{
    // error was suppressed with the @-operator
    if (error_reporting() === 0) {
        return false;
    }

    switch ($err_severity) {
        case E_ERROR:
            throw new \ErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_WARNING:
            throw new Exceptions\WarningException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_PARSE:
            throw new Exceptions\ParseException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_NOTICE:
            throw new Exceptions\NoticeException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_CORE_ERROR:
            throw new Exceptions\CoreErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_CORE_WARNING:
            throw new Exceptions\CoreWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_COMPILE_ERROR:
            throw new Exceptions\CompileErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_COMPILE_WARNING:
            throw new Exceptions\CoreWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_USER_ERROR:
            throw new Exceptions\UserErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_USER_WARNING:
            throw new Exceptions\UserWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_USER_NOTICE:
            throw new Exceptions\UserNoticeException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_STRICT:
            throw new Exceptions\StrictException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_RECOVERABLE_ERROR:
            throw new Exceptions\RecoverableErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_DEPRECATED:
            throw new Exceptions\DeprecatedException($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_USER_DEPRECATED:
            throw new Exceptions\UserDeprecatedException($err_msg, 0, $err_severity, $err_file, $err_line);
    }
}

set_error_handler('\Core\Lib\Errors\ErrorHandler', E_ALL);

/**
 * Class for TekFW errors handling
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
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
     * @var string
     */
    private $message;

    /**
     * Constructor
     *
     * @param Router $router
     * @param User $user
     * @param Ajax $ajax
     * @param DataAdapter $adapter
     */
    public function __construct(Router $router, User $user, Ajax $ajax, Message $message, DataAdapter $adapter)
    {
        $this->router = $router;
        $this->user = $user;
        $this->ajax = $ajax;
        $this->message = $message;
        $this->adapter = $adapter;



        $this->error_id = uniqid('#error_');
    }

    /**
     * Creates html error message
     */
    private function createErrorHtml()
    {
        $this->error_html = '
        <div class="alert alert-danger alert-dismissible" role="alert" id="' . $this->error_id . '">
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';

        // Append more informations for admin users
        if ($this->user->isAdmin() == true) {

            $this->error_html .= '
            <p><small>Error Code ' . $this->exception->getCode() . '</small></p>
            <h3 class="no-top-margin">' . $this->exception->getMessage() . '</h3>
            <p><small><strong>File:</strong> ' . $this->exception->getFile() . ' (Line: ' . $this->exception->getLine() . ')</small></p>
            <hr>
            <pre><small>' . $this->exception->getTraceAsString() . '</small></pre>';

        } else {

            $this->error_html .= '
            <h3 class="no-top-margin">Error</h3>
            <p>Sorry for that! Webmaster has been informed. Please try again later.</p>';
        }

        $this->error_html .= '
        </div>';
    }

    /**
     * Returns html error message
     */
    public function getErrorHtml()
    {
        if (! $this->error_html) {
            $this->createErrorHtml();
        }

        return $this->error_html;
    }

    /**
     * Exceptionhandler
     *
     * @param \Exception $e
     * @param boolean $clean_buffer
     *            Optional: Flag to switch buffer clean on/off. (Default: false)
     * @param string $log_error
     *            Optional: Flag to switch error logging on/off. (Default: true)
     *
     * @return boolean|string
     */
    public function handleException(\Exception $e, $clean_buffer = false, $log_error = true)
    {
        // Store exception
        $this->exception = $e;

        // Clean outpub buffer?
        if ($clean_buffer === true) {
            ob_clean();
        }

        // Write error log entry?
        if ($log_error == true) {
            $this->logError();
            $this->writeToServerLog();
        }

        // Ajax output
        if ($this->router->isAjax()) {
            $this->ajax->fnError($this->getErrorHtml(), $this->error_id);
            return false;
        }

        // Normal output to message stack
        $this->message->danger($this->getErrorHtml(), true);

        // return html
        return $this->getErrorHtml();
    }

    private function getTraceCalls($ignore = 2)
    {
        $trace = '';

        foreach ($this->exception->getTrace() as $k => $v) {

            if ($k < $ignore) {
                continue;
            }

            array_walk($v['args'], function (&$item, $key)
            {
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
            'field' => [
                'stamp',
                'user',
                'code',
                'message',
                'file',
                'line',
                'trace'
            ]
            // 'exception'
            ,
            'params' => [
                ':stamp' => time(),
                ':user' => $this->user->getId(),
                ':code' => $this->exception->getCode(),
                ':message' => $this->exception->getMessage(),
                ':file' => $this->exception->getFile(),
                ':line' => $this->exception->getLine(),
                ':trace' => $this->exception->getTraceAsString()
            ]
            // ':exception' => serialize($this->exception)

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
        $this->sendHttpStatus(500);

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
				<div class="container">' . $this->getMessage() . '</div>
			</body>

	   </html>');
    }
}
