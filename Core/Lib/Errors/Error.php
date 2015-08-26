<?php
namespace Core\Lib\Errors;

/**
 * Error.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */

// Do not show errors!
ini_set('display_errors', 0);

/**
 * Throws exceptions based on E_* error types
 */
function ErrorHandler($err_severity, $err_msg, $err_file, $err_line, array $err_context)
{
    global $di;

    // error was suppressed with the @-operator
    if (error_reporting() === 0 || empty($di)) {
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

    if (! empty($di)) {
        echo $di->get('core.error')->handleException($e);
    }
}

function shutDownFunction()
{
    $error = error_get_last();

    if (! empty($error['type'])) {
        ErrorHandler($error['type'], $error['message'], $error['file'], $error['line'], []);
    }
}

// Register handler
set_error_handler('\Core\Lib\Errors\ErrorHandler', E_ALL);
set_exception_handler('\Core\Lib\Errors\ExceptionHandler');
register_shutdown_function('\Core\Lib\Errors\shutdownFunction');

