<?php
namespace Core\Error;

/**
 * HighLevelHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class HighLevelHandler extends HandlerAbstract
{

    /***
     *
     * @var \Throwable
     */
    private $t;

    protected $dependencies = [
        'router' => 'core.router',
        'user' => 'core.security.user.current',
        'ajax' => 'core.ajax',
        'message' => 'core.message',
        'db' => 'db.default',
        'cfg' => 'core.config'
    ];

    /**
     *
     * @var \Core\Router\Router
     */
    protected $router;

    /**
     *
     * @var \Core\Security\User
     */
    protected $user;

    /**
     *
     * @var \Core\Ajax\Ajax
     */
    protected $ajax;

    /**
     *
     * @var \Core\Data\Connectors\Db\Db
     */
    protected $db;

    /**
     *
     * @var \Core\Page\Body\Message\Message
     */
    protected $message;

    /**
     *
     * @var Core\Cfg\Cfg
     */
    protected $config;

    public function run(\Throwable $t)
    {
        // Store error
        $this->t = $t;

        // The basic data of exception
        $message = $this->t->getMessage();
        $file = $this->t->getFile();
        $line = $this->t->getLine();
        $trace = $this->t->getTraceAsString();

        $fatal = true;
        $clean_buffer = true;
        $log_error = true;
        $send_mail = true;
        $to_db = true;
        $public = ini_get('display_errors') ? true : false;

        // Exception settings alway ovveride set methods parameter
        if ($this->t instanceof CoreException) {
            $fatal = $this->t->getFatal();
            $clean_buffer = $this->t->getCleanBuffer();
            $log_error = $this->t->getErrorLog();
            $send_mail = $this->t->getSendMail();
            $to_db = $this->t->getToDb();
            $public = $this->t->getPublic();
        }

        // Override db logging on PDO exceptions
        if ($this->t instanceof \PDOException) {

            $error_log = true;
            $send_mail = true;

            // Prevent db logging on PDOException!!!
            $to_db = false;
        }

        if ($this->t instanceof \Core\Mailer\MailerException) {
            $send_mail = false;
        }

        // Log error
        if (!empty($this->config->Core['error.log.use'])) {

            // Write to error log?
            if (!empty($error_log) || !empty($this->config->Core['error.log.modes.php'])) {
                error_log($message . ' (' . $file . ':' . $line . ')');
            }

            // Write to db error log? Take care of avoid flag (-1) due to PDOExceptions
            if (!empty($to_db) || !empty($this->config->Core['error.log.modes.db'])) {

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

            $cmd = $this->ajax->createActCommand();
            $cmd->error($this->createErrorHtml(true));

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
        <div class="alert alert-danger' . ($dismissable == true ? ' alert-dismissible' : '') . '" role="alert" id="' . $this->id . '">';

        if ($dismissable == true) {
            $this->error_html .= '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
        }

        switch (true) {
            case method_exists($this->t, 'getPublic') && $this->t->getPublic():
            case (bool) $this->user->isAdmin():
            case ! empty($this->config->Core['error.display.skip_security_check']):
                $this->error_html .= '
                <h3 class="no-v-margin">' . $this->t->getMessage() . '<br>
                <small><strong>File:</strong> ' . $this->t->getFile() . ' (Line: ' . $this->t->getLine() . ')</small></h3>
                <strong>Trace</strong>
                <pre>' . $this->t->getTraceAsString() . '</pre>
                <strong>Router</strong>
                <pre>' . print_r($this->router->getStatus(), true) . '</pre>';

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

        return '
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
        </html>';
    }
}
