<?php
namespace Core\Logger;

use Psr\Log\AbstractLogger;
use Core\Logger\Streams\StreamAbstract;

/**
 * Logger.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Logger extends AbstractLogger
{

    /**
     *
     * @var array
     */
    private $streams = [];

    /**
     *
     * {@inheritDoc}
     *
     * @see \Psr\Log\LoggerInterface::log()
     */
    public function log($level, $message, array $context = array())
    {
        if (empty($this->streams)) {
            $this->createStream('ErrorLog');
        }

        // TODO Auto-generated method stub
        foreach ($this->streams as $stream) {
            $stream->log($level, $message, $context);
        }
    }

    /**
     *
     * @param StreamAbstract $stream
     */
    public function registerStream(StreamAbstract $stream)
    {
        $this->streams[] = $stream;
    }

    /**
     *
     * @param string $stream_type
     * @param array $config
     *
     * @return StreamAbstract
     */
    public function &createStream($stream_type, array $config = [])
    {
        Try {

            $stream_class = '\Core\Logger\Streams\\' . $stream_type . 'Stream';
            $stream = new $stream_class($config);

            $this->streams[] = $stream;

            return $stream;
        }
        catch (\Throwable $t) {
            Throw new LoggerException(sprintf('Stream of type "%s" does not exist.'), $stream_type);
        }
    }
}
