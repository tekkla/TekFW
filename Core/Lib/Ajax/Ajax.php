<?php
namespace Core\Lib\Ajax;

use Core\Lib\Content\Message;
use Core\Lib\IO\Files;
use Core\Lib\Errors\Exceptions\AjaxException;

/**
 * Ajax.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Ajax
{

    /**
     * Storage for ajax commands
     *
     * @var \stdClass
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

    public function __construct(Message $message, Files $files)
    {
        $this->message = $message;
        $this->files = $files;
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
        }
        else {
            $this->ajax['act'][] = $ajax;
        }
    }

    /**
     * Builds the ajax command structure
     */
    public function process()
    {

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
                if ($this->di->get('core.cfg')->get('Core', 'js_fadeout_time') > 0 && $msg->getFadeout()) {
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

        // Output is json encoded
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
     * Cleans the current ajax command stack.
     *
     * @return \Core\Lib\Ajax\Ajax
     */
    public function cleanCommandStack()
    {
        $this->ajax = [];

        return $this;
    }

    /**
     * Creates and returns a named ajax command.
     * Commands are split into DOM (Dom) manipulation and predifined actions (Act) to call.
     *
     * @param string $command_name Name of command to create. Default: Dom\Html
     *
     * @throws AjaxException
     *
     * @return \Core\Lib\Ajax\Command
     */
    public function createCommand($command_name = 'Dom\Html')
    {
        if (empty($command_name)) {
            $command_name = 'Dom\Html';
        }

        $class = '\Core\Lib\Ajax\Commands\\' . $command_name;

        if (!$this->files->checkClassFileExists($class)) {
            Throw new AjaxException('Classfile for command "' . $command_name . '" does not exist.');
        }

        return $this->di->instance($class, [
            'core.ajax'
        ]);
    }
}
