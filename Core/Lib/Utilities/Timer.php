<?php
namespace Core\Lib\Utilities;

/**
 * Timer class for time measurement
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 */
class Timer
{

    /**
     *
     * @var integer
     */
    private $start;

    /**
     *
     * @var integer
     */
    private $end;

    /**
     *
     * @var array
     */
    private $checkpoints = [];

    /**
     * Starts timer and creates firt checkpoint
     */
    public function start()
    {
        $this->start = microtime(true);
        $this->checkpoints['start'] = $this->start;
    }

    /**
     * Stopps timer and returns difference from start
     *
     * @return number
     */
    public function stop()
    {
        $this->end = microtime(true);

        return $this->getDiff();
    }

    /**
     * Adds a new named checkpoint which measures the time between now and the last checkpooint.
     *
     * @param string $name
     */
    public function checkpoint($name)
    {
        $this->checkpoints[$name] = microtime(true) - end($this->checkpoints);
    }

    /**
     * Returns the start time.
     *
     * @return integer
     */
    private function getStart()
    {
        return $this->start;
    }

    /**
     * Stopps timer and returns it's ending time.
     * Sets also endcheckpoint
     *
     * @return mixed
     */
    private function getEnd()
    {
        $this->stop();

        $this->checkpoints['end'] = $this->end;

        return $this->end;
    }

    /**
     * Calculates and returns the time between start and end.
     *
     * @return number
     */
    public function getDiff()
    {
        return $this->getEnd() - $this->getStart();
    }

    /**
     * Resets timer to current time.
     */
    public function reset()
    {
        $this->start = microtime(true);
    }
}
