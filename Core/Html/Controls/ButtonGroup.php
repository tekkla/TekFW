<?php
namespace Core\Html\Controls;

use Core\Html\Elements\Div;
use Core\Html\Form\Button;
use Core\Errors\Exceptions\InvalidArgumentException;
use Core\Errors\Exceptions\UnexpectedValueException;
use Core\Html\HtmlException;

/**
 * ButtonGroup.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class ButtonGroup extends Div
{

    /**
     * Button stroage
     *
     * @var array
     */
    private $buttons = [];

    /**
     * Adds a button to the group
     *
     * @param Button $button
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Html\Controls\ButtonGroup
     */
    public function addButton($button)
    {
        if (! $button instanceof Button && ! $button instanceof UiButton) {
            Throw new HtmlException('Buttons for a buttongroup must be an instance of Button or UiButton');
        }

        if (! $button->checkCss('btn')) {
            $button->addCss('btn');
        }

        $this->buttons[] = $button;

        return $this;
    }

    /**
     * Builds buttongroup
     *
     * @throws UnexpectedValueException
     *
     * @return string
     *
     * @see \Core\Abstracts\HtmlAbstract::build()
     */
    public function build()
    {
        if (empty($this->buttons)) {
            Throw new HtmlException('No buttons for buttongroup set.');
        }

        /* @var $button Button */
        foreach ($this->buttons as $button) {
            $this->inner .= $button->build();
        }

        $this->css[] = 'btn-group';

        return parent::build();
    }
}
