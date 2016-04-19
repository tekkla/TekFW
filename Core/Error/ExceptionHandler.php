<?php
namespace Core\Error;

use Core\Router\Router;
use Core\Security\User;
use Core\Ajax\Ajax;
use Core\Cfg\Cfg;
use Core\Data\Connectors\Db\Db;
use Core\Page\Body\Message\Message;

/**
 * ExceptionHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 *
 * @todo REWRITE THIS MONSTER!
 *
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
    private $db;

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
     * @param Db $db
     * @param Cfg $cfg
     */
    public function __construct(Router $router, User $user, Ajax $ajax, Message $message, Db $db, Cfg $cfg)
    {
        $this->router = $router;
        $this->user = $user;
        $this->ajax = $ajax;
        $this->message = $message;
        $this->db = $db;
        $this->cfg = $cfg;

        $this->error_id = uniqid('#error_');
    }

    /**
     * Exceptionhandler
     *
     * @param \Exception $e
     *            The exception or the error (PHP7)
     * @param boolean $fatal
     *            Optional flags exception to be a fatal error (Default: false)
     * @param boolean $clean_buffer
     *            Optional flag to switch buffer clean on/off. (Default: false)
     * @param boolean $error_log
     *            Optional flag to switch error logging on/off. (Default: true)
     * @param boolean $send_mail
     *            Optional flag to send error message to admins. (Default: false)
     * @param boolean $to_db
     *            Optional flag to switch error logging to db driven errorlog on/off. (Default: true)
     *
     * @return boolean|string
     */
    public function handleException($e, $fatal = false, $clean_buffer = false, $error_log = true, $send_mail = false, $to_db = true, $public = false)
    {

        // Store exception
        $this->exception = $e;

        // The basic data of exception
        $message = $this->exception->getMessage();
        $file = $this->exception->getFile();
        $line = $this->exception->getLine();
        $trace = $this->exception->getTraceAsString();

        // Exception settings alway ovveride set methods parameter
        if ($this->exception instanceof CoreException) {
            $fatal = $this->exception->getFatal();
            $clean_buffer = $this->exception->getCleanBuffer();
            $log_error = $this->exception->getErrorLog();
            $send_mail = $this->exception->getSendMail();
            $to_db = $this->exception->getToDb();
            $public = $this->exception->getPublic();
        }

        // Override db logging on PDO exceptions
        if ($this->exception instanceof \PDOException) {

            $error_log = true;
            $send_mail = true;

            // Prevent db logging on PDOException!!!
            $to_db = false;
        }

        // Log error
        if (isset($this->cfg->data['Core']['error.log.use'])) {

            // Write to error log?
            if ($error_log == true || $this->cfg->data['Core']['error.log.modes.php'] == true) {
                error_log($message . ' (' . $file . ':' . $line . ')');
            }

            // Write to db error log? Take care of avoid flag (-1) due to PDOExceptions
            if ($to_db == true || $this->cfg->data['Core']['error.log.modes.db'] == true) {

                try {

                    $this->db->qb([
                        'method' => 'INSERT',
                        'table' => 'core_error_logs',
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

                    $this->db->execute();
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
            case !empty($this->cfg->data['Core']['error.display.skip_security_check']):
                $this->error_html .= '
                <h3 class="no-v-margin">' . $this->exception->getMessage() . '<br>
                <small><strong>File:</strong> ' . $this->exception->getFile() . ' (Line: ' . $this->exception->getLine() . ')</small></h3>
                <strong>Details</strong>
                <div style="max-height: 200px; overflow-y: scroll; border: 1px solid #333; padding: 5px; margin: 5px 0;">
                    <strong>Trace</strong>
                    <pre>' . $this->exception->getTraceAsString() . '</pre>
                    <strong>Router</strong>
                    <pre>' . print_r($this->router->getStatus(), true) . '</pre>
                </div>';

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
     * Fatal error!
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
                <link href="' . BASEURL . '/Cache/combined.css" rel="stylesheet">
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