<?php
namespace Core\Logger\Streams;

/**
 * FileStream.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class FileStream extends StreamAbstract
{

    use InterpolateTrait;

    /**
     *
     * @var string
     */
    protected $filename = '';

    /**
     *
     * @var string
     */
    protected $template = "[{time}]\t[{level}]\t{message}";

    /**
     *
     * {@inheritDoc}
     *
     * @see \Psr\Log\LoggerInterface::log()
     */
    public function log($level, $message, array $context = array())
    {
        if (empty($this->filename)) {
            Throw new StreamException('FileStream logger needs a proper filename to write to.');
        }

        if (!is_writable(dirname($this->filename))) {
            Throw new StreamException(sprintf('Directory "%s" is not writable.', dirname($this->filename)));
        }

        try {

            $message = $this->interpolate($message, $context);

            $strings = [
                'time' => date('Y-m-d H:i:s'),
                'level' => $level,
                'message' => $message
            ];

            $replace = [];

            foreach ($strings as $key => $value) {
                $replace['{' . $key . '}'] = $value;
            }

            $message = strtr($this->template, $replace);

            file_put_contents($this->filename, $message . PHP_EOL, FILE_APPEND);
        }
        catch (\Throwable $t) {
            Throw new StreamException($t->getMessage(), 'Logger::FileStream');
        }
    }

    /**
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }
}
