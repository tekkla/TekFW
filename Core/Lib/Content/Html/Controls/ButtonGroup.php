<?php
namespace Core\Lib\Content\Html\Controls;

use Core\Lib\Content\Html\Elements\Div;
use Core\Lib\Content\Html\Form\Button;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;
use Core\Lib\Errors\Exceptions\UnexpectedValueException;

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
     * @return \Core\Lib\Content\Html\Controls\ButtonGroup
     */
    public function addButton($button)
    {
        if (! $button instanceof Button && ! $button instanceof UiButton) {
            Throw new InvalidArgumentException('Buttons for a buttongroup must be an instance of Button or UiButton');
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
     * @see \Core\Lib\Abstracts\HtmlAbstract::build()
     */
    public function build()
    {
        if (empty($this->buttons)) {
            Throw new UnexpectedValueException('No buttons for buttongroup set.');
        }

        /* @var $button Button */
        foreach ($this->buttons as $button) {
            $this->inner .= $button->build();
        }

        $this->css[] = 'btn-group';

        return parent::build();
    }
}
