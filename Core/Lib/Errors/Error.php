<?php
namespace Core\Lib\Error;

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

    private $redirectUrl = false;

    private $codes = [
        0 => 'General',
        1000 => 'ParameterValue',
        2000 => 'File',
        3000 => 'Db',
        4000 => 'Config',
        5000 => 'Object',
        6000 => 'Router'
    ];

    private $params = [];

    /**
     * Error handler object
     *
     * @var ErrorAbstract
     */
    private $error_handler;

    /**
     *
     * @var User;
     */
    private $user;

    private $error;

    /**
     *
     * @var \Exception
     */
    protected $exception;

    /**
     * Constructor
     *
     * @param string $message
     * @param number $code
     * @param Error $previous
     * @param string $trace
     */
    public function __construct(\Exception $exception)
    {
    	$this->exception = $exception;

        // Get error handler group code from sent $code parameter
        $code = floor($exception->getCode() / 1000) * 1000;

        foreach ($this->codes as $error_code => $handler_name) {
            if ($error_code == $code)
                break;
        }

        $handler_class = '\Core\Lib\Errors\\' . $handler_name . 'Error';

        $this->error_handler = new $handler_class($this);
        $this->error_handlerprocess();
    }

    public function setError(\Exception $e)
    {
        $this->error = $e;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Exception::__toString()
     */
    public function __toString()
    {
        return $this->getComplete();
    }

    /**
     * Returns a Bootstrap formatted error message
     *
     * @return string
     */
    public function getComplete($admin = true)
    {
        $message = '<h1>TekFW error code: ' . $this->getCode() . '</h1>';

        $message .= $this->getMessage();

        // Append more informations for admin users
        if ($admin) {
            $message .= '
			<h1>Source</h1>
			<p>In file: ' . $this->getFile() . ' (Line: ' . $this->getLine() . ')</p>
			<h2>Trace</h2>
			<pre>' . $this->getTraceAsString() . '</pre>';
        }

        if ($this->error_handler->inBox())
            $message = '<div style="border: 2px solid darkred; background-color: #eee; padding: 5px; border-radius: 5px; margin: 10px; color: #222;">' . $message . '</div>';

        return $message;
    }

    /**
     * Returns the redirect url value
     */
    public function getRedirect()
    {
        return $this->error_handlergetRedirect();
    }

    /**
     * Checks for set redirect url
     *
     * @return boolean
     */
    public function isRedirect()
    {
        return $this->error_handler->isRedirect();
    }

    /**
     * Returns the fatal state of the error handler
     *
     * @return boolean
     */
    public function isFatal()
    {
        return $this->error_handler->isFatal();
    }

    public function getAdminMessage()
    {
        return $this->error_handler->getAdminMessage();
    }

    public function getUserMessage()
    {
        return $this->error_handler->getUseRMessage();
    }

    public function logError()
    {
        return $this->error_handler->logError();
    }

    public function getLogMessage()
    {
        return $this->error_handler->getLogMessage();
    }

    public function endHere()
    {}

    public function handle()
    {
        echo $this->getAdminMessage() . ' [' . $this->getFile() . '(' . $this->getLine() . ')]';
        return;

        // Write error to log?
        if ($this->logError())
            error_log($this->getLogMessage());

            // Ajax request errors will end with an alert(error_message)
        if ($this->router->isAjax()) {
            // Echo processed ajax
            echo $this->di['core.content.ajax']->process();

            // And finally stop execution
            exit();
        }

        if ($this->isFatal()) {
            // Falling through here means we have a really big error. Usually we will never come this far
            // but reaching this point causes stopping all further actions.
            $this->sendHttpStatus(500);

            $html = '
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
				<div class="container">' . $this->getMessage() . ' in <strong>' . $this->file . ' (' . $this->line . ')</strong></div>
			</body>

			</html>';

            die($html);
        }

        // Create error message
        $this->di['core.content.message']->danger($this->getMessage());

        // If error has a redirection, the error message will be sent as
        // a message before redirecting to the redirect url
        if ($this->isRedirect())
            header("Location: " . $this->getRedirect());
    }
}
