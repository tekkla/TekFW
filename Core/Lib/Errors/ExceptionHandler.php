<?php
namespace Core\Lib\Errors;

use Core\Lib\Errors\Exceptions\BasicException;
use Core\Lib\Http\Router;
use Core\Lib\Security\User;
use Core\Lib\Ajax\Ajax;
use Core\Lib\Content\Message;
use Core\Lib\Data\DataAdapter;
use Core\Lib\Cfg;

/**
 * ExceptionHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class ExceptionHandler
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
     * Exceptionhandler
     *
     * @param \Exception $e
     * @param boolean $fatal Optional flags exception to be a fatal error (Default: false)
     * @param boolean $clean_buffer Optional flag to switch buffer clean on/off. (Default: false)
     * @param boolean $error_log Optional flag to switch error logging on/off. (Default: true)
     * @param boolean $send_mail Optional flag to send error message to admins. (Default: false)
     * @param boolean $to_db Optional flag to switch error logging to db driven errorlog on/off. (Default: true)
     *
     * @return boolean|string
     */
    public function handleException(\Exception $e, $fatal = false, $clean_buffer = false, $error_log = true, $send_mail = false, $to_db = true, $public = false)
    {
        // Store exception
        $this->exception = $e;

        // The basic data of exception
        $message = $this->exception->getMessage();
        $file = $this->exception->getFile();
        $line = $this->exception->getLine();
        $trace = $this->exception->getTraceAsString();

        // Exception settings alway ovveride set methods parameter
        if ($this->exception instanceof BasicException) {
            $fatal = $this->exception->getFatal();
            $clean_buffer = $this->exception->getCleanBuffer();
            $log_error = $this->exception->getErrorLog();
            $send_mail = $this->exception->getSendMail();
            $to_db = $this->exception->getToDb();
            $public = $this->exception->getPublic();
        }

        // Override db logging on
        if ($this->exception instanceof \PDOException) {

            $error_log = true;
            $send_mail = true;

            // Prevent db logging on PDOException!!!
            $to_db = false;
        }

        // Log error
        if ($this->cfg->get('Core', 'error_logger')) {

            // Write to error log?
            if ($error_log == true || $this->cfg->get('Core', 'error_to_log') == true) {
                error_log($message . ' (' . $file . ':' . $line . ')');
            }

            // Write to db error log? Take care of avoid flag (-1) due to PDOExceptions
            if ($to_db == true || $this->cfg->get('Core', 'error_to_db') == true) {

                try {

                    $this->adapter->qb([
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
                            ':msg' => $message,
                            ':trace' => $trace,
                            ':file' => $file,
                            ':line' => $line
                        ]
                    ]);

                    $this->adapter->execute();
                }
                catch (\Exception $e) {
                    // Handle this exception without trying to save it to db
                    $this->handleException($e, false, false, true, true, false);
                }
            }
        }

        // Ajax output
        if ($this->router->isAjax()) {

            // Stop output buffering by removing content
            ob_end_clean();

            // Clean current command stack
            $this->ajax->cleanCommandStack();

            $command = $this->ajax->createCommand('Act\Error');

            $command->error($this->createErrorHtml(true));
            $command->send();

            // We have to send a 200 response code or jQueries ajax handler
            // recognizes the error and cancels result processing
            http_response_code(200);
            header('Content-type: application/json; charset=utf-8');

            die($this->ajax->process());
        }

        // Clean output buffer?
        if ($clean_buffer == true) {
            ob_clean();
        }

        if ($fatal == true) {
            $this->fatal();
        }

        return $this->createErrorHtml(false);
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

        switch (true) {
            case method_exists($this->exception, 'getPublic') && $this->exception->getPublic():
            case (bool) $this->user->isAdmin():
            case (bool) $this->cfg->get('Core', 'skip_security_check'):
                $this->error_html .= '
                <p><small>Error Code ' . $this->exception->getCode() . '</small></p>
                <h4 class="no-top-margin">' . $this->exception->getMessage() . '</h4>
                <p><strong>File:</strong> ' . $this->exception->getFile() . ' (Line: ' . $this->exception->getLine() . ')</p>
                <hr>
                <h4>Router</h4>
                <div style="max-height: 200px; overflow-y: scroll;">
                    <pre><small>' . print_r($this->router->getStatus(), true) . '</small></pre>
                </div>
                <hr>
                <pre><small>' . $this->exception->getTraceAsString() . '</small></pre>';

                break;

            default:
                $this->error_html .= '
                <h3 class="no-top-margin">Error</h3>
                <p>Sorry for that! Webmaster has been informed. Please try again later.</p>';
        }

        $this->error_html .= '
        </div>';

        return $this->error_html;
    }

    /**
     */
    private function fatal()
    {
        // Clean buffer with all output done so far
        ob_clean();

        // Send 500 http status
        http_response_code(500);

        die('
        <html>
            <head>
                <title>Error</title>
                <link href="/Cache/combined.css" rel="stylesheet">
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
