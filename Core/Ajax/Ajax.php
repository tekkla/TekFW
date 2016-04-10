<?php
namespace Core\Ajax;

use Core\IO\Files;
use Core\Cfg\Cfg;
use Core\Page\Body\Message\Message;

/**
 * Ajax.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Ajax
{

    /**
     *
     * @var array
     */
    private $ajax = [];

    /**
     *
     * @var Message
     */
    private $message;

    /**
     *
     * @var Files
     */
    private $files;

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     * Constructor
     *
     * @param Message $message
     *            Message service dependency
     * @param Files $files
     *            Files services dependency
     * @param Cfg $cfg
     *            Cfg service dependency
     */
    public function __construct(Message $message, Files $files, Cfg $cfg)
    {
        $this->message = $message;
        $this->files = $files;
        $this->cfg = $cfg;
    }

    /**
     * Builds ajax definition and adds it to the ajaxlist
     */
    public function add(AjaxCommandAbstract $cmd)
    {
        // Create alert on missing target when type is in need-target list
        if ($cmd->getType() == 'dom' && ! $cmd->getSelector()) {

            $this->ajax['act'][] = [
                'f' => 'console',
                'a' => 'Your DOM ajax response from "' . $cmd->getId() . '" needs a selector but none is set.'
            ];

            $this->ajax['act'][] = [
                'f' => 'console',
                'a' => $cmd->getArgs()
            ];

            return;
        }

        // Publish ajax definition to ajaxlist
        $ajax = [
            'f' => $cmd->getFn(),
            'a' => $cmd->getArgs()
        ];

        if ($cmd->getType() == 'dom') {
            $this->ajax['dom'][$cmd->getSelector()][] = $ajax;
        } else {
            $this->ajax['act'][] = $ajax;
        }
    }

    /**
     * Builds the ajax command structure
     */
    public function process()
    {

        // With each ajax request processing all currently
        // diplayed messages will be cleared.
        /*
         * $this->ajax['dom']['#core-message'][] = [
         * 'f' => 'html',
         * 'a' => ''
         * ];
         */

        // Add messages
        $messages = $this->message->getMessages();

        if ($messages) {

            foreach ($messages as $msg) {

                if ($msg->getType() == 'clear') {
                    $this->ajax['dom']['#core-message'][] = [
                        'f' => 'html',
                        'a' => ''
                    ];
                    continue;
                }

                $html = '
                <div class="alert alert-' . $msg->getType();

                // Message dismissable?
                if ($msg->getDismissable()) {
                    $html .= ' alert-dismissable';
                }

                // Fadeout message?
                if ($this->cfg->data['Core']['js.style.fadeout_time'] > 0 && $msg->getFadeout()) {
                    $html .= ' fadeout';
                }

                $html .= '">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    ' . $msg->getMessage() . '
                    </div>';

                $this->ajax['dom']['#core-message'][] = [
                    'f' => 'append',
                    'a' => $html
                ];
            }
        }

        // Return JSON encoded ajax command stackk
        return json_encode($this->ajax);
    }

    /**
     * Returns the complete ajax command stack as it is
     *
     * @return array
     */
    public function getCommandStack()
    {
        return $this->ajax;
    }

    /**
     * Cleans the current ajax command stack
     *
     * @return \Core\Ajax\Ajax
     */
    public function cleanCommandStack()
    {
        $this->ajax = [];

        return $this;
    }

    /**
     * Creates and returns a named ajax command
     *
     * Commands are split into DOM (Dom) manipulation and predifined actions (Act) to call.
     *
     * @param string $command_name
     *            Name of command to create. Default: Dom\Html
     *
     * @throws AjaxException
     *
     * @return \Core\Ajax\AjaxCommandAbstract
     */
    public function createCommand($command_name = 'Dom\Html')
    {
        if (empty($command_name)) {
            $command_name = 'Dom\Html';
        }

        $class = '\Core\Ajax\Commands\\' . $command_name;

        if (! $this->files->checkClassFileExists($class)) {
            Throw new AjaxException(sprintf('Classfile for command "%s" does not exist.', $class));
        }

        return new $class($this);
    }
}
