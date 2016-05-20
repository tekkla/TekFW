<?php
namespace Core\Ajax;

/**
 * ActCommand.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class ActCommand extends AbstractAjaxCommand
{
    /**
     * alert()
     *
     * @var string
     */
    const ALERT = 'alert';

    /**
     * confirm()
     *
     * @var string
     */
    const CONFIRM = 'confirm';

    /**
     * jQuery.getScript()
     *
     * @var string
     */
    const GETSCRIPT = 'getScript';

    /**
     * location.href()
     *
     * @var string
     */
    const HREF = 'href';

    /**
     * Error display
     *
     * @var string
     */
    const ERROR = 'error';

    protected $type = 'act';

    /**
     * Helper method to set command properties
     * 
     * @param string $function
     * @param mixed $args
     *
     * @throws AjaxCommandException
     */
    private function init($function, $args)
    {
        if (empty($function)) {
            Throw new AjaxCommandException('Empty command function is not permitted');
        }

        $this->setFunction($function);
        $this->setArgs($args);
    }

    /**
     * alert($message)
     *
     * @param string $message
     *            The message to alert
     */
    public function alert($message)
    {
        $this->init(self::ALERT, $message);
    }

    /**
     * location.href = $url
     *
     * @param unknown $url
     */
    public function href($url)
    {
        $this->init(self::HREF, $url);
    }

    /**
     * jQuery.getScript($url)
     *
     * @param string $url
     */
    public function getScript($url)
    {
        $this->init(self::GETSCRIPT, $url);
    }

    /**
     * Show error message
     *
     * @param string $message
     *            The error message
     * @param string $target
     *            Optional target where to append the message
     */
    public function error($message, $target = '#core-message')
    {
        $this->init(self::ERROR, [
            $target,
            $message
        ]);
    }
}