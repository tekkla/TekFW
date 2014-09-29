<?php
namespace Core\Lib\Utilities;

use Core\Lib\Cfg;

/**
 * Class with debugging functions
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class Debug
{
	/**
	 * The content to inspect
	 * @var mixed
	 */
	private $data = '';

	/**
	 * How to inspect the var
	 * @var string
	 */
	private $mode = 'plain';

	/**
	 * How to return the inspection information
	 * @var unknown
	 */
	private $target = 'console';

	/**
	 *
	 * @var Cfg
	 */
	private $cfg;

	public function __construct(Cfg $cfg)
	{
		$this->cfg = $cfg;
	}

	/**
	 * Sets the var by reference
	 * @param mixed $var
	 * @return \Core\Lib\Debug
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * Sets the mode how to inspect the var.
	 * Select from 'print' (print_r) or 'dump' (var_dump).
	 * @param string $mode
	 * @throws NoValidParameterError
	 * @return \Core\Lib\Debug
	 */
	public function setMode($mode = 'plain')
	{
		$modes = array(
			'print',
			'dump',
			'plain'
		);

		if (!in_array($mode, $modes))
			Throw new \InvalidArgumentException('Not existing debugmode set.', 1000);

		$this->mode = $mode;
		return $this;
	}

	/**
	 * Sets the target to what the result of var inspection will be send.
	 * Select from 'return' or 'echo' or the third option called 'console'
	 * which only available on ajax requests.
	 * On non ajax requests all 'console' targets will be 'returns'
	 * @param string $target
	 * @throws NoValidParameterError
	 * @return \Core\Lib\Debug
	 */
	public function setTarget($target = 'console')
	{
		$targets = array(
			'return',
			'echo',
			'console'
		);

		if (!in_array($target, $targets))
			Throw new \InvalidArgumentException('Not existing debugtarget set.', 1000);

		$this->target = $target;

		return $this;
	}

	/**
	 * Sends data to FirePHP console
	 */
	public function toConsole($data)
	{
		$debug = $this->di['core.utilities.debug'];
		$debug->run([
			'data' => $data
		]);
	}

	/**
	 * Var dumps the given var to the given target
	 */
	public function dumpVar($var, $target = '')
	{
		$debug = $this->di['core.utilities.debug'];
		$debug->run([
			'data' => $var,
			'target' => $target,
			'mode' => 'dump'
		]);
	}

	/**
	 * Light version of debug_backtrace() which only creates and returns a trace of function and method calls.
	 * @param number $ignore Numeber of levels to ignore
	 * @return string
	 */
	public function traceCalls($ignore = 2, $target = '')
	{
		$trace = '';

		$dt = debug_backtrace();

		if (!$dt)
			return false;

		foreach ( $dt as $k => $v )
		{
			if ($k < $ignore)
				continue;

			array_walk($v['args'], function (&$item, $key)
			{
				$item = var_export($item, true);
			});

			$trace .= '#' . ( $k - $ignore ) . ' ' . $v['file'] . '(' . $v['line'] . '): ' . ( isset($v['class']) ? $v['class'] . '' : '' ) . $v['function'] . "\n";
		}

		$debug = $this->di['core.utilities.debug'];
		$debug->run([
			'data' => $trace,
			'target' => $target
		]);
	}

	/**
	 * Var dumps the given var to the given target
	 * @return string
	 */
	public static function printVar($var, $target = '')
	{
		$debug = $this->di['core.utilities.debug'];
		$debug->run([
			'data' => $var,
			'target' => $target,
			'mode' => 'print'
		]);
	}

	/**
	 * Debugs given data with various output
	 * @return void string
	 */
	public function run($definition = array())
	{
		// Small debug definition parser
		if ($definition && is_array($definition))
		{
			foreach ( $definition as $property => $value )
				if (property_exists($this, $property))
					$this->{$property} = $value;
		}

		// Which display mode is requested?
		switch ($this->mode)
		{
			case 'print' :
				$dt = debug_backtrace();
				$output = $this->target == 'echo' ? '<div class="panel panel-info panel-body"><p>Called by: ' . $dt[0]['file'] . ' (' . $dt[0]['line'] . ')</p><pre>' . htmlspecialchars(print_r($this->data, true), ENT_QUOTES) . '</pre></div>' : $this->data;
				break;

			case 'dump' :
				ob_start();
				var_dump($this->data);
				$output = ob_get_clean();
				break;

			default :
				$output = $this->data;
				break;
		}

		// If var is not set explicit, the calling object will
		// be used for debug output.
		if (!$output)
			Throw new \RuntimeException('Data to debug not set.', 1001);

			// Target 'console' is used for ajax requests and
			// returns the debug content to the browser console
		if ($this->target == 'console')
		{

			// Load FirePHP classfile only when class not exists
			if (!class_exists('FirePHP'))
				require_once ( $this->cfg->get('Core', 'dir_tools') . '/FirePHPCore/FirePHP.class.php' );

			// Create the ajax console.log ajax
			\FirePHP::getInstance(true)->log($output);
			return;
		}

		// Echoing debug content and end this
		elseif ($this->target == 'echo')
		{
			echo '<h1>Debug</h1>' . $output;
			return;
		}

		// Falling through here means to return the output
		else
		{
			return $output;
		}
	}
}
