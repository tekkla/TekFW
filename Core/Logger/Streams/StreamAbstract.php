<?php
namespace Core\Logger\Streams;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * StreamAbstract.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class StreamAbstract extends AbstractLogger
{

    protected $threshold = 6;

    /**
     * Log Levels
     *
     * @var array
     */
    protected $logLevels = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7
    ];

    final public function __construct(array $config = [])
    {
        if (!empty($config)) {
            foreach ($config as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->{$property} = $value;
                }
            }
        }
    }

    final public function getThreshold()
    {
        return $this->threshold;
    }
}