<?php
namespace Core\Lib\Utilities;

/**
 * Timer class for time measurement
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class Timer
{
	private $start;
	private $end;

	public function start()
	{
		$this->start = microtime(true);
	}

	public function stop()
	{
		$this->end = microtime(true);
		return $this->GetDiff();
	}

	private function getStart()
	{
		if (isset($this->start))
			return $this->start;
		else
			return false;
	}

	private function getEnd()
	{
		if (isset($this->end))
			return $this->end;
		else
			return false;
	}

	public function getDiff()
	{
		return $this->getEnd() - $this->getStart();
	}

	public function reset()
	{
		$this->start = microtime(true);
	}
}

