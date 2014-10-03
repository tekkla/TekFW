<?php
namespace Core\Lib;

// Check for direct file access
if (! defined('TEKFW'))
    die('Cannot run without TekFW framework...');

/**
 * Logger class
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 *             @Inject user
 *             @Inject request
 *             @Inject cfg
 *             @Inject debug
 *             @inject fileio
 *             @inject fire
 */
final class Log
{

    /**
     * Log message
     * 
     * @var string
     */
    private $message = '';

    /**
     * Log type
     * 
     * @var string
     */
    private $type = '';

    /**
     * Memory used
     * 
     * @var int
     */
    private $memory = 0;

    /**
     * Timestamp
     * 
     * @var int
     */
    private $time = 0;

    /**
     * Trace flag for appending traces to log
     * 
     * @var boolean
     */
    private $trace = false;

    /**
     * Returns log type
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns log message
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns memory value
     * 
     * @return int
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * Returns timestamp
     * 
     * @return number
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Sets log message
     * 
     * @param string $message
     * @return \Core\Lib\Log
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Sets timestamp
     * 
     * @param int $time
     * @return \Core\Lib\Log
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    public function setMemory($memory)
    {
        $this->memory = $memory;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Writes a message to the logfile
     * 
     * @param string $msg
     */
    public function add($msg, $app = 'Global', $function = 'Info', $check_setting = '', $trace = false)
    {
        // Do not log when settingto check is wsitched off
        if (! empty($check_setting) && ! $this->cfg->get('Core', $check_setting))
            return;
            
            // Logging only when log is activated
        if (! $this->cfg->get('Core', 'log') || ! $this->user->isAdmin())
            return;
            
            // Debug the message if it is not of type string
        if (is_object($msg))
            $msg = $this->debugdumpVar($msg);
            
            // SSI tag if SSI and not in log
        if (SMF == 'SSI' && strpos($function, 'SSI') === false)
            $function .= ' (SSI)';
            
            // Trace append requested?
        if ($trace == true)
            $msg .= '<pr' . print_r($this->debugtraceCalls(), true) . '<pr';
            
            // Putting all together to the log
        $this->setType($app . '::' . $function);
        $this->setMessage($msg);
        $this->setTime(microtime(true));
        $this->setMemory($this->fileioconvFilesize(memory_get_usage()));
        $this->saveLog();
    }

    /**
     * Writes debug backtrace with an individual depth to the log
     * 
     * @param number $depth
     */
    public function trace($depth = 10)
    {
        // Logging only when log is activated
        if (! $this->cfg->get('Core', 'log') || ! $this->user->isAdmin())
            return;
        
        $dt = debug_backtrace();
        
        $logs = [];
        
        for ($i = 0; $i < $depth; $i ++) {
            $key = $i + 1;
            
            $file = isset($dt[$key]['file']) ? $dt[$key]['file'] . ' => ' : '';
            $line = isset($dt[$key]['line']) ? '[' . $dt[$key]['line'] . ']' : '';
            
            if ($key == 1)
                $logs[] = '<stron' . $file . $dt[$key]['function'] . '() ' . $line . '</stron';
            else
                $logs[] = $file . $dt[$key]['function'] . '() ' . $line . ']';
        }
        
        // Putting all together to the log
        $this->setMessage(implode('<b', $logs));
        $this->setType('Trace');
        $this->setTime(microtime(true));
        $this->setMemory($this->fileioconvFilesize(memory_get_usage()));
        $this->saveLog();
    }

    /**
     * Adds an error message to the log
     * 
     * @param unknown $msg
     */
    public function error($msg)
    {
        $this->add($msg, 'ERROR');
    }

    /**
     * Adds a notice to the log
     * 
     * @param unknown $msg
     */
    public static function notice($msg)
    {
        self::Add($msg, 'NOTICE');
    }

    /**
     * Handles how the log entry is stored.
     * By default the log will be sent to the end of the page output.
     * If set to ON in TekFW config, the output will be send to FirePHP
     * extension of your Brwoser.
     */
    public function saveLog()
    {
        // Write log to file
        if ($this->cfg->get('Core', 'log_handler') == 'file') {
            // @todo: seriously?
        }        // For ajax request logs and when FirePHP is set as log handler, we use FirePHP for log output!
        elseif ($this->cfg->get('Core', 'log_handler') == 'fire' || $this->request->isAjax()) {
            $this->firelog($this->message, $this->type);
        }         // All else goes to session log
        else {
            // Still her? Output to session so the output can go to page
            if (empty($_SESSION['logs']))
                $_SESSION['log'] = [];
            
            $_SESSION['logs'][] = $this;
        }
    }

    /**
     * Returns a formatted html output of created log entries.
     * 
     * @return boolean string
     */
    public function get()
    {
        // No admin? no output wanted? return false!
        if (! $this->user->isAdmin() || ! $this->cfg->get('Core', 'show_log_output'))
            return false;
            
            // Simple counter
        $log_counter = 0;
        
        $html = '
		<h
		<div class="container
			<hTekFW Logs</h
			<div id="log" class="clearfix
				<table class="table table-striped table-bordered table-condensed small
					<captio
						<thea
						 =>  <t
							 => <th width="40#</t
							 => <tInfo</t
							 => <tText</t
							 => <th width="100Mem</t
						 =>  </t
						</thea
						<tbod';
        
        if (empty($_SESSION['logs']))
            $html .= '
						<t
						 => <td colspan="4" class="text-center<stronNo logs to show.</stron</t
						</t';
        
        else {
            /* @var $log Log */
            foreach ($_SESSION['logs'] as $log) {
                $html .= '
						 <tr class="' . ($log_counter % 2 == 0 ? 'odd' : 'even') . '
							 <t' . $log_counter . '</t
							 <t' . $log->getType() . '</t
							 <t' . $log->getMessage() . '</t
							 <t' . $log->getMemory() . '</t
						 </t';
                
                $log_counter ++;
            }
        }
        
        $html .= '
					</tbod
				</tabl
			</di
		</di';
        
        self::resetLogs();
        
        return $html;
    }

    public function resetLogs()
    {
        $_SESSION['logs'] = [];
    }
}

