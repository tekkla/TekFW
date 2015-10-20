<?php
namespace Core\Lib\Content\Html\Controls;

use Core\Lib\Content\Html\Elements\Div;
use Core\Lib\Traits\ArrayTrait;
use Core\Lib\Traits\TextTrait;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;

/**
 * Actionbar.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Actionbar extends Div
{
    use ArrayTrait;
    use TextTrait;

    /**
     * Storage for the UiButtons
     *
     * @var array
     */
    public $buttons = [];

    /**
     * Some generic icons for edit, delete, and save.
     * This list will be extended, maybe.
     *
     * @var array
     */
    protected $icons = array(
        'new' => 'plus',
        'edit' => 'edit',
        'save' => 'save',
        'delete' => 'trash-o',
        'cancel' => 'ban'
    );

    /**
     * The displaymode
     *
     * @var string Default 'auto'
     */
    protected $mode = 'auto';

    /**
     * Size of the actionbar
     *
     * @var string Default 'sm' = small
     */
    protected $size = 'sm';

    /**
     * Add one UiButton to the actionbar
     *
     * @param string $name
     * @param UiButton $button
     *
     * @return \Core\Lib\Content\Html\Controls\Actionbar
     */
    public function addUiButton($name, UiButton $button)
    {
        $this->buttons[$name] = $button;

        return $this;
    }

    /**
     * Add a list of UiButtons to the actionbar
     *
     * @param array $ui_buttons
     *
     * @return \Core\Lib\Content\Html\Controls\Actionbar
     *
     * @throws InvalidArgumentException
     */
    public function addUiButtons(array $ui_buttons)
    {
        if (! $this->isAssoc($ui_buttons)) {
            Throw new InvalidArgumentException('Your list of ui buttons needs to be an assoc array with name an UiButton object.');
        }

        foreach ($ui_buttons as $name => $button) {

            if (! $button instanceof UiButton) {
                Throw new InvalidArgumentException('One button to be inserted into actionbar is not of type UiButton');
            }

            $this->buttons[$name] = $button;
        }

        return $this;
    }

    /**
     * Creates a UiButton for the actionbar.
     *
     * @param string $name
     * @param string $mode optional
     * @param string $type optional
     *
     * @return UiButton
     */
    public function &createButton($name, $mode = 'ajax', $type = 'icon')
    {
        $button = UiButton::factory($mode, $type);

        // Add icons for edit, delete, save and cancel automatically
        // Also add title texts
        if ($type == 'icon' && isset($this->icons[$name])) {
            $button->setIcon($this->icons[$name])->setTitle($this->txt('' . $name));
        }

        // Add confirm dialog by default to UiButtons named delete
        if ($name == 'delete') {
            $button->setConfirm($this->txt('delete_confirm'));
        }

        $this->buttons[$name] = $button;

        return $this->buttons[$name];
    }

    /**
     * Builds and returns actionbar element
     *
     * @return boolean string
     */
    public function build()
    {
        // no buttons no actionbar but an empty string
        if (empty($this->buttons)) {
            return false;
        }

        $this->css[] = 'actionbar';

        // How many buttons do we have?
        $count = count((array) $this->buttons);

        // More than two buttons will be wrapped into a dropdown
        if ($count > 2) {
            $this->inner .= '
            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle btn-' . $this->size . '" data-toggle="dropdown">
                    <i class="fa fa-angle-down"></i>
                </button>
                <ul class="dropdown-menu" role="menu">';

            // implode possible buttons array to a combinded string
            foreach ($this->buttons as $button) {
                /* @var $button UiButton */
                $button->addCss('btn-' . $this->size);
                $this->inner .= '<li>' . $button->build() . '</li>';
            }

            $this->inner .= '
                </ul>
            </div>';
        }
        else {

            // implode possible buttons array to a combinded string
            foreach ($this->buttons as $button) {

                /* @var $button UiButton */
                $button->addCss('btn-' . $this->size);

                $this->inner .= $button->build();
            }
        }

        return parent::build();
    }
}
