<?php
namespace Core\Logger\Streams;

use Psr\Log\LogLevel;

/**
 * FirePhpStream.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class FirePhpStream extends StreamAbstract
{

    /**
     *
     * @var unknown
     */
    protected $options = [];

    /**
     *
     * @var unknown
     */
    protected $type = 'INFO';

    /**
     *
     * @var
     */
    protected $label = null;

    /**
     *
     * {@inheritDoc}
     *
     * @see \Psr\Log\LoggerInterface::log()
     */
    public function log($level, $message, array $context = array())
    {

        foreach ($context as $property => $val) {
            if (property_exists($this, $property)) {
                $this->{$property} = $val;
            }
        }

        $fb = new \FB();

        if (!empty($this->options)) {
            $fb->setOptions($this->options);
        }

        $fb->send($message, $this->label, $level);

        if ($level == LogLevel::DEBUG) {
            foreach ($context as $label => $object) {
                $fb->send($object, $label, $level);
            }
        }

    }
}

