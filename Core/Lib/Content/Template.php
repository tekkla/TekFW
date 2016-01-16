<?php
namespace Core\Lib\Content;

use Core\Lib\Cfg;
use Core\Lib\Content\Html\HtmlFactory;
use Core\Lib\Errors\Exceptions\TemplateException;
use Core\Lib\Cache\Cache;

/**
 * Template.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Template
{

    /**
     * Layers to render
     *
     * @var array
     */
    protected $layers = [
        'Head',
        'Body'
    ];

    /**
     *
     * @var Cfg
     */
    protected $cfg;

    /**
     *
     * @var Content
     */
    protected $content;

    /**
     *
     * @var HtmlFactory
     */
    protected $html;

    /**
     *
     * @var Cache
     */
    private $cache;

    /**
     * Constructor
     *
     * @param Cfg $cfg
     *            Cfg dependency
     * @param Content $content
     *            Content dependency
     * @param HtmlFactory $html
     *            HtmlFactory dependency
     */
    public function __construct(Cfg $cfg, Content $content, HtmlFactory $html, Cache $cache)
    {
        $this->cfg = $cfg;
        $this->content = $content;
        $this->html = $html;
        $this->cache = $cache;
    }

    /**
     * Renders the template
     *
     * Uses the $layer property to look for layers to be rendered. Will throw a
     * runtime exception when a requested layer does not exist in the called
     * template file.
     *
     * @throws TemplateException
     */
    final public function render()
    {
        foreach ($this->layers as $layer) {
            
            if (! method_exists($this, $layer)) {
                Throw new TemplateException('Template Error: The requested layer "' . $layer . '" does not exist.');
            }
            
            $this->$layer();
        }
    }

    /**
     * Creates and returns meta tags
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *            
     * @return string|array
     */
    final protected function getMeta($data_only = false)
    {
        $meta_stack = $this->content->meta->getTags();
        
        if ($data_only) {
            return $meta_stack;
        }
        
        $html = '';
        
        foreach ($meta_stack as $tag) {
            
            // $meta = $this->html->create('Elements\Meta');
            
            $html .= PHP_EOL . '<meta';
            
            foreach ($tag as $attribute => $value) {
                $html .= ' ' . $attribute . '="' . $value . '"';
            }
            
            $html .= '>';
        }
        
        return $html;
    }

    /**
     * Creates and returns the title tag
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *            
     * @return string|array
     */
    final protected function getTitle($data_only = false)
    {
        if ($data_only) {
            return $this->content->getTitle();
        }
        
        return PHP_EOL . '<title>' . $this->content->getTitle() . '</title>';
    }

    /**
     * Returns html navbar or only the menu structure.
     *
     * @param boolean $data_only            
     *
     * @return string|array
     */
    final protected function getMenu($name = '')
    {
        return $this->content->menu->getItems($name);
    }

    /**
     * Creates and return OpenGraph tags
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *            
     * @return string|array
     */
    final protected function getOpenGraph($data_only = false)
    {
        $og_stack = $this->content->og->getTags();
        
        if ($data_only) {
            return $og_stack;
        }
        
        $html = '';
        
        foreach ($og_stack as $property => $content) {
            $html .= '<meta property="' . $property . '" content="' . $content . '">' . PHP_EOL;
        }
        
        return $html;
    }

    /**
     * Creates and returns all css realted content
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *            
     * @return string|array
     */
    final protected function getCss($data_only = false)
    {
        $files = $this->content->css->getFiles();
        
        if ($data_only) {
            return $files;
        }
        
        $html = '';
        
        // Start reading
        foreach ($files as $file) {
            $html .= PHP_EOL . '<link rel="stylesheet" type="text/css" href="' . $file . '">';
        }
        
        return $html;
    }

    /**
     * Creates and returns js script stuff for the requested area.
     *
     * @param string $area
     *            Valid areas are 'top' and 'below'.
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *            
     * @return string|array
     */
    final protected function getScript($area, $data_only = false)
    {
        $files = $this->content->js->getFiles($area);
        
        if ($data_only) {
            return $files;
        }
        
        // Init output var
        $html = '';
        
        // Create files
        foreach ($files as $file) {
            
            // Create script html object
            $html .= PHP_EOL . '<script src="' . $file . '"></script>';
        }
        
        return $html;
    }

    /**
     * Create and returns head link elements
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *            
     * @return string|array
     */
    final protected function getHeadLinks($data_only = false)
    {
        $link_stack = $this->content->link->getLinkStack();
        
        if ($data_only) {
            return $link_stack;
        }
        
        $html = '';
        
        foreach ($link_stack as $link) {
            
            $html .= PHP_EOL . '<link';
            
            foreach ($link as $attribute => $value) {
                $html .= ' ' . $attribute . '="' . $value . '"';
            }
            
            $html .= '>';
        }
        
        return $html;
    }

    /**
     * Creates and returns stored messages
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *            
     * @return string|array
     */
    final protected function getMessages($data_only = false)
    {
        $messages = $this->content->msg->getMessages();
        
        if ($data_only) {
            return $messages;
        }
        
        ob_start();
        
        echo '<div id="core-message">';
        
        foreach ($messages as $msg) {
            
            echo PHP_EOL, '
            <div class="alert alert-', $msg->getType(), $msg->getDismissable() ? ' alert-dismissable' : '';
            
            // Fadeout message?
            if ($this->cfg->get('Core', 'js.fadeout_time') > 0 && $msg->getFadeout()) {
                echo ' fadeout';
            }
            
            echo '">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                ', $msg->getMessage(), '
            </div>';
        }
        
        echo '</div>';
        
        return ob_get_clean();
    }

    /**
     * Creates breadcrumb html content or returns it's data-
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *            
     * @return string|array
     */
    final protected function getBreadcrumbs($data_only = false)
    {
        $breadcrumbs = $this->content->breadcrumbs->getBreadcrumbs();
        
        if ($data_only) {
            return $breadcrumbs;
        }
        
        // Add home button
        $text = $this->content->txt('home');
        
        if ($breadcrumbs) {
            $home_crumb = $this->content->breadcrumbs->createItem($text, BASEURL, $text);
        } else {
            $home_crumb = $this->content->breadcrumbs->createActiveItem($text, $text);
        }
        
        array_unshift($breadcrumbs, $home_crumb);
        
        ob_start();
        
        if ($breadcrumbs) {
            
            echo '<ol class="breadcrumb">';
            
            foreach ($breadcrumbs as $breadcrumb) {
                
                echo '<li';
                
                if ($breadcrumb->getActive()) {
                    echo ' class="active">' . $breadcrumb->getText();
                } else {
                    echo '><a href="' . $breadcrumb->getHref() . '">' . $breadcrumb->getText() . '</a>';
                }
                
                echo '</li>';
            }
            
            echo '</ol>';
        }
        
        return ob_get_clean();
    }

    /**
     * Returns default "core-scrolltotop" div html.
     *
     * @return string
     */
    protected function getScrollToTop()
    {
        return '<div id="core-scrolltotop"></div>';
    }

    /**
     * Returns default "core-modal" div html.
     *
     * @return string
     */
    protected function getModal()
    {
        return '<div id="core-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>';
    }

    /**
     * Returns default "core-tooltip" div html.
     *
     * @return string
     */
    protected function getTooltip()
    {
        return '<div id="core-tooltip"></div>';
    }

    /**
     * Returns default "core-tooltip", "core-modal" and "core-scrolltotop" divs html.
     *
     * @return string
     */
    protected function getDisplayEssentials()
    {
        return $this->getTooltip() . $this->getModal() . $this->getScrollToTop();
    }

    /**
     * Returns the content generated by app call
     */
    final protected function getContent()
    {
        return $this->content->getContent();
    }
}
