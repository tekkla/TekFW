<?php
namespace Core\Asset\Javascript;

use Core\Asset\AssetObjectInterface;
use Core\Asset\AbstractAssetHandler;

/**
 * JavascriptHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class JavascriptHandler extends AbstractAssetHandler
{

    const AREA_TOP = 'top';

    const AREA_BELOW = 'below';

    /**
     * This is a javascript handler
     *
     * @var string
     */
    protected $type = self::TYPE_JS;

    /**
     *
     * @var array
     */
    protected $objects = [];

    /**
     *
     * @var array
     */
    private static $files_used = [];

    /**
     *
     * @var int
     */
    private static $filecounter = 0;

    /**
     * Adds an javascript objectto the content.
     *
     * @param JavascriptHandler $script
     *
     * @return JavascriptObject
     */
    public function addObject(AssetObjectInterface $aoi)
    {
        // Store f
        if ($aoi->getType() == 'file') {

            $dt = debug_backtrace();

            self::$files_used[self::$filecounter . '-' . $dt[1]['function']] = $aoi->getContent();
            self::$filecounter++;
        }

        $this->objects[] = $aoi;
    }

    /**
     * Adds a file javascript object to the output queue
     *
     * @param string $url
     *            The url of the file to use
     * @param boolean $defer
     *            Optional flag to put objects output right before the closing body tag. (Default: true)
     * @param boolean $combine
     *            Optional flag to switch combining for this object on or off. (Default: true)
     * @param boolean $minify
     *            Optional flag to switch minifying for this object on or off. (Default: true)
     *            Applies only when $combine argument is set to true.
     *
     * @throws JavascriptException
     *
     * @return JavascriptObject
     */
    public function &createFile($url, $defer = true, $combine = true, $minify = true)
    {
        // Do not add files already added
        if (in_array($url, self::$files_used)) {
            Throw new JavascriptException(sprintf('Url "%s" is already set as included js file.', $url));
        }

        $obj = new JavascriptObject();

        $obj->setType($obj::TYPE_FILE);
        $obj->setContent($url);
        $obj->setDefer($defer);
        $obj->setCombine($combine);
        $obj->setMinify($minify);

        $this->addObject($obj);

        return $obj;
    }

    /**
     * Adds an script javascript object to the output queue
     *
     * @param string $content
     *            Objects content
     * @param boolean $defer
     *            Optional flag to put objects output right before the closing body tag. (Default: true)
     * @param boolean $combine
     *            Optional flag to switch combining for this object on or off. (Default: true)
     * @param boolean $minify
     *            Optional flag to switch minifying for this object on or off. (Default: true)
     *            Applies only when $combine argument is set to true.
     *
     * @return JavascriptObject
     */
    public function &createScript($content, $defer = true, $combine = true, $minify = true)
    {
        $obj = new JavascriptObject();

        $obj->setType($obj::TYPE_SCRIPT);
        $obj->setContent($content);
        $obj->setDefer($defer);
        $obj->setCombine($combine);
        $obj->setMinify($minify);

        $this->addObject($obj);

        return $obj;
    }

    /**
     * Creats a ready javascript object
     *
     * @param string $content
     *            Objects content
     * @param boolean $defer
     *            Optional flag to put objects output right before the closing body tag. (Default: true)
     * @param boolean $combine
     *            Optional flag to switch combining for this object on or off. (Default: true)
     * @param boolean $minify
     *            Optional flag to switch minifying for this object on or off. (Default: true)
     *            Applies only when $combine argument is set to true.
     *
     * @return JavascriptObject
     */
    public function &createReady($content, $defer = true, $combine = true, $minify = true)
    {
        $obj = new JavascriptObject();

        $obj->setType('ready');
        $obj->setContent($content);
        $obj->setDefer($defer);

        $this->addObject($obj);

        return $obj;
    }

    /**
     * Blocks with complete code
     *
     * Use this for conditional scripts!
     *
     * @param string $content
     *            Objects content
     * @param boolean $defer
     *            Optional flag to put objects output right before the closing body tag. (Default: true)
     * @param boolean $combine
     *            Optional flag to switch combining for this object on or off. (Default: true)
     * @param boolean $minify
     *            Optional flag to switch minifying for this object on or off. (Default: true)
     *            Applies only when $combine argument is set to true.g.
     *
     * @return JavascriptObject
     */
    public function &createBlock($content, $defer = true, $combine = true, $minify = true)
    {
        $obj = new JavascriptObject();

        $obj->setType($obj::TYPE_BLOCK);
        $obj->setContent($content);
        $obj->setDefer($defer);
        $obj->setCombine($combine);
        $obj->setMinify($minify);

        $this->addObject($obj);

        return $obj;
    }

    /**
     * Creates and returns a var javascript object
     *
     * @param string $name
     *            Variables name
     * @param mixed $value
     *            Variables values
     * @param boolean $is_string
     *            Flag variable as string to automatically applying quotes
     * @param boolean $defer
     *            Optional flag to put objects output right before the closing body tag. (Default: true)
     * @param boolean $combine
     *            Optional flag to switch combining for this object on or off. (Default: true)
     * @param boolean $minify
     *            Optional flag to switch minifying for this object on or off. (Default: true)
     *            Applies only when $combine argument is set to true.
     *
     * @return JavascriptObject
     */
    public function &createVariable($name, $value, $is_string = false, $defer = false, $combine = true, $minify = true)
    {
        if ($is_string == true) {
            $value = '"' . $value . '"';
        }

        $obj = new JavascriptObject();

        $obj->setType($obj::TYPE_VAR);
        $obj->setContent([
            $name,
            $value
        ]);
        $obj->setDefer($defer);
        $obj->setCombine($combine);
        $obj->setMinify($minify);

        $this->addObject($obj);

        return $obj;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AbstractAssetHandler::getContent()
     */
    public function getContent()
    {
        // Init js storages
        $files = $blocks = $inline = $scripts = $ready = $vars = $local_files = [];

        /* @var $script JavascriptObject */
        foreach ($this->objects as $key => $script) {

            switch ($script->getType()) {

                // File to lin
                case 'file':
                    $filename = $script->getContent();

                    if (strpos($filename, BASEURL) !== false && $script->getCombine()) {
                        $local_files[] = str_replace(BASEURL, BASEDIR, $filename);
                    }
                    else {
                        $files[] = $filename;
                    }
                    break;

                // Script to create
                case 'script':
                    $inline[] = $script->getContent();
                    break;

                // Dedicated block to embaed
                case 'block':
                    $blocks[] = $script->getContent();
                    break;

                // A variable to publish to global space
                case 'var':
                    $var = $script->getContent();
                    $vars[$var[0]] = $var[1];
                    break;

                // Script to add to $.ready()
                case 'ready':
                    $ready[] = $script->getContent();
                    break;
            }

            // Remove worked script object
            unset($this->objects[$key]);
        }

        // Check cache
        if ($local_files || $inline || $vars || $scripts || $ready) {

            // Strat combining all parts
            $combined = '';

            if ($local_files) {
                foreach ($local_files as $js_file) {
                    $combined .= file_get_contents($js_file);
                }
            }

            if ($inline) {
                $combined .= implode('', $inline);
            }

            if ($vars) {
                foreach ($vars as $name => $val) {
                    $combined .= 'var ' . $name . ' = ' . (is_string($val) ? '"' . $val . '"' : $val) . ';';
                }
            }

            if ($ready) {
                $combined .= '$(document).ready(function() {' . implode('', $ready) . '});';
            }

            if ($blocks) {
                $combined .= implode($blocks);
            }
        }

        foreach ($this->processors as $processor) {
            $combined = $processor->process($combined);
        }

        return $combined;
    }
}
