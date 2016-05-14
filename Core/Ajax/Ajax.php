<?php
namespace Core\Ajax;

/**
 * Ajax.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Ajax
{

    const DOM = 'Dom';

    const ACT = 'Act';

    /**
     * Ajax command stack
     *
     * @var array
     */
    private $commands = [];

    /**
     * Builds ajax definition and adds it to the ajaxlist
     */
    public function addCommand(AbstractAjaxCommand $cmd)
    {
        $this->commands[] = $cmd;
    }

    /**
     * Builds the ajax command structure
     */
    public function process()
    {
        $ajax = [];

        foreach ($this->commands as $cmd) {

            // Create alert on missing target when type is in need-target list
            if ($cmd instanceof DomCommand && empty($cmd->getSelector())) {

                $ajax['act'][] = [
                    'f' => 'error',
                    'a' => [
                        '#core-message',
                        'Your DOM ajax response from "' . $cmd->getId() . '" needs a selector but none is set.'
                    ]
                ];

                $ajax['act'][] = [
                    'f' => 'error',
                    'a' => [
                        '#core-message',
                        $cmd->getArgs()
                    ]
                ];

                continue;
            }

            // Create funcion/arguments array
            $fa = [
                'f' => $cmd->getFn(),
                'a' => $cmd->getArgs()
            ];

            if ($cmd instanceof DomCommand) {
                $ajax['dom'][$cmd->getSelector()][] = $fa;
            }
            else {
                $ajax['act'][] = $fa;
            }
        }

        // Return JSON encoded ajax command stack
        return ! empty($ajax) ? json_encode($ajax) : '';
    }

    /**
     * Returns the complete ajax command stack as it is
     *
     * @return array
     */
    public function getCommandStack()
    {
        return $this->commands;
    }

    /**
     * Cleans the current ajax command stack
     */
    public function cleanCommandStack()
    {
        $this->commands = [];
    }

    /**
     * Creates and returns as DomCommand object
     *
     * @return \Core\Ajax\DomCommand
     */
    public function &createDomCommand($autoadd = true)
    {
        $cmd = new DomCommand();

        if ($autoadd) {
            $this->addCommand($cmd);
        }

        return $cmd;
    }

    /**
     * Creates and returns as ActCommand object
     *
     * @return \Core\Ajax\ActCommand
     */
    public function &createActCommand($autoadd = true)
    {
        $cmd = new ActCommand();

        if ($autoadd) {
            $this->addCommand($cmd);
        }

        return $cmd;
    }
}
