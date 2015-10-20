<?php
namespace Core\Lib\Content\Html\Controls;

use Core\Lib\Content\Html\Elements\Div;

/**
 * Group.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Group extends Div
{

    private $new_row = false;

    /**
     * Heading text
     *
     * @var string
     */
    private $heading_text;

    /**
     * Heading size
     *
     * @var int
     */
    private $heading_size;

    /**
     * Lead text
     *
     * @var string
     */
    private $description;

    /**
     * Closing text
     *
     * @var string
     */
    private $footer;

    /**
     * Use bootstrap panel
     *
     * @var boolean
     */
    private $use_panel = false;

    /**
     * When BS panel which style
     *
     * @var string
     */
    private $panel_type = 'default';

    private $row = false;

    /**
     * Group content
     *
     * @var string
     */
    private $content = '';

    /**
     * Sets group to be displayed as Bootstrap panel
     *
     * @param bool $use_panel
     *
     * @return \Core\Lib\Content\Html\Controls\Group
     */
    public function usePanel($use_panel = true)
    {
        $this->use_panel = (bool) $use_panel;

        return $this;
    }

    /**
     * Set heading text and size
     *
     * @param string $heading_text
     * @param number $heading_size
     *
     * @return \Core\Lib\Content\Html\Controls\Group
     */
    public function setHeading($heading_text, $heading_size = 2)
    {
        $this->heading_text = $heading_text;
        $this->heading_size = is_int($heading_size) ? $heading_size : 2;

        return $this;
    }

    /**
     * Set lead description text
     *
     * @param string $description
     *
     * @return \Core\Lib\Content\Html\Controls\Group
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set footer text
     *
     * @param string $footer
     *
     * @return \Core\Lib\Content\Html\Controls\Group
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * Adds the content of group
     *
     * @param string $content
     *
     * @return \Core\Lib\Content\Html\Controls\Group
     */
    public function addContent($content)
    {
        $this->content .= $content;

        return $this;
    }

    /**
     * Unset row display mode
     *
     * @return \Core\Lib\Content\Html\Controls\Group
     */
    public function noRow()
    {
        $this->row = false;

        return $this;
    }

    /**
     * Force content to be displayed in a new row.
     * This is important for content elements with grid sizes set.
     *
     * @return \Core\Lib\Content\Html\Controls\Group
     */
    public function newRow()
    {
        $this->row = true;

        return $this;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Abstracts\HtmlAbstract::build()
     */
    public function build()
    {
        if ($this->use_panel == true) {

            // Bootstrap panel template
            $this->inner .= '<div class="panel panel-' . $this->panel_type . '">';

            if (isset($this->heading_text)) {
                $this->inner .= '{heading}';
            }

            $this->inner .= '<div class="panel-body">';

            if (isset($this->description)) {
                $this->inner .= '{description}';
            }

            if ($this->row) {
                $this->inner .= '<div class="row">';
            }

            $this->inner .= '{content}</div>';

            if ($this->row) {
                $this->inner .= '</div>';
            }

            if (isset($this->footer)) {
                $this->inner .= '{footer}';
            }

            $this->inner .= '</div>';
        }
        else {

            if ($this->row) {
                $this->inner .= '<div class="row';
            }

            if (isset($this->heading_text)) {
                $this->inner .= '{heading}';
            }

            if (isset($this->description)) {
                $this->inner .= '{description}';
            }

            $this->inner .= '{content}';

            if (isset($this->footer)) {
                $this->inner .= '{footer}';
            }

            if ($this->row) {
                $this->inner .= '</div>';
            }
        }

        // Create possible heading
        if (isset($this->heading_text)) {

            // Heading: plain or withe BS title?
            $heading = '<h' . $this->heading_size . ($this->use_panel == true ? ' class="panel-title"' : '') . '>' . $this->heading_text . '</h' . $this->heading_size . '>';

            // Replace heading in BS panel template...
            $this->inner = str_replace('{heading}', $this->use_panel == true ? '<div class="panel-heading">' . $heading . '</div>' : $heading, $this->inner);
        }

        // Is there a description do create?
        if (isset($this->description)) {

            // The description with small
            $description = '<p class="small">' . $this->description . '</p>';

            // Into the panel template...
            $this->inner = str_replace('{description}', $description, $this->inner);
        }

        // Add the content
        $this->inner = str_replace('{content}', $this->content, $this->inner);

        if (isset($this->footer)) {
            $footer = '<span class="help-block">' . $this->description . '</span>';
            $this->inner = str_replace('{footer}', $this->use_panel == true ? '<div class="panel-footer">' . $footer . '</div>' : $footer, $this->inner);
        }

        $this->inner = $this->inner;

        return parent::build();
    }
}
