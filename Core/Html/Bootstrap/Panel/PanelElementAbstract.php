<?php
namespace Core\Html\Bootstrap\Panel;

use Core\Html\HtmlBuildableInterface;

abstract class PanelElementAbstract implements HtmlBuildableInterface
{

    /**
     *
     * @var \Core\Html\Elements\Div
     */
    public $html;

    protected $content = [];

    abstract public function __construct();

    public function addContent($content)
    {
        $this->content[] = $content;

        return $this;
    }

    public function setContent($content)
    {
        $this->content = [
            $content
        ];

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function build()
    {
          // No content, no output
        if (empty($this->content)) {
            return;
        }

        foreach ($this->content as $content) {

            if ($content instanceof HtmlBuildableInterface || (is_object($content) && method_exists($content, 'build'))) {
                $content = $content->build();
            }

            $this->html->addInner($content);
        }

        return $this->html->build();
    }
}
