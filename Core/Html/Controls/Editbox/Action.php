<?php
namespace Core\Html\Controls\Editbox;

/**
 * Action.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Action
{

    const SAVE = 'save';

    const CANCEL = 'cancel';

    const DELETE = 'delete';

    const CONTEXT = 'context';

    /**
     *
     * @var string
     */
    private $text = 'no text set';

    /**
     *
     * @var string
     */
    private $href = 'no href set';

    /**
     *
     * @var boolean
     */
    private $ajax = false;

    /**
     *
     * @var string
     */
    private $confirm = '';

    /**
     *
     * @var string
     */
    private $type = 'context';

    /**
     *
     * @var string
     */
    private $icon = '';

    /**
     *
     * @return the $text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     *
     * @return the $href
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     *
     * @param string $href
     */
    public function setHref($href)
    {
        $this->href = $href;
    }

    /**
     *
     * @return the $ajax
     */
    public function getAjax()
    {
        return $this->ajax;
    }

    /**
     *
     * @param boolean $ajax
     */
    public function setAjax($ajax)
    {
        $this->ajax = (bool) $ajax;
    }

    /**
     *
     * @return the $confirm
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     *
     * @param string $confirm
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;
    }

    /**
     *
     * @return the $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param string $type
     */
    public function setType($type)
    {
        $allowed = [
            'save',
            'delete',
            'cancel',
            'context'
        ];

        if (! in_array($type, $allowed)) {
            Throw new EditboxException(sprintf('Type "%s" is not a allowed editbox actiontype', $type));
        }

        $this->type = $type;
    }

    /**
     *
     * @return the $icon
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     *
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }
}
