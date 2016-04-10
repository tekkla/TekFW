<?php
namespace Core\Html\Elements;

use Core\Html\HtmlAbstract;
use Core\Errors\Exceptions\InvalidArgumentException;

/**
 * Audio.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Audio extends HtmlAbstract
{

    protected $element = 'audio';

    private $sources = [];

    private $no_support_text = 'Html is not supported';

    /**
     * Set the text to be shown when the browser does not support the audio element.
     *
     * @param string $text
     * @return \Core\Html\Elements\Audio
     */
    public function setNoSupportText($text)
    {
        $this->no_support_text = $text;

        return $this;
    }

    /**
     * Defines that audio controls should be displayed
     *
     * @return \Core\Html\Elements\Audio
     */
    public function useControls()
    {
        $this->attribute['controls'] = false;

        return $this;
    }

    /**
     * Defines that the audio starts playing as soon as it is ready
     *
     * @return \Core\Html\Elements\Audio
     */
    public function useAutoplay()
    {
        $this->attribute['autoplay'] = false;

        return $this;
    }

    /**
     * Defines that the audio will start over again, every time it is finished
     *
     * @return \Core\Html\Elements\Audio
     */
    public function isLoop()
    {
        $this->attribute['loop'] = false;

        return $this;
    }

    /**
     * Defines that the audio output should be muted by default
     *
     * @return \Core\Html\Elements\Audio
     */
    public function isMuted()
    {
        $this->attribute['muted'] = false;

        return $this;
    }

    /**
     * Sets if and how the audio should be loaded when the page loads
     *
     * @param string $preload
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Html\Elements\Audio
     */
    public function setPreload($preload = 'none')
    {
        $preloads = [
            'auto',
            'metadata',
            'none'
        ];

        if (! in_array($preload, $preloads)) {
            Throw new InvalidArgumentException('Prelaod type not supported', 1000);
        }

        $this->attribute['preload'] = $preload;

        return $this;
    }

    /**
     * Sets the URL of the audio file
     *
     * @param string $url
     *
     * @return \Core\Html\Elements\Audio
     */
    public function setSrc($url)
    {
        $this->attribute['src'] = $url;

        return $this;
    }

    /**
     * Creates an source element, adds it to the audio element and returns a reference to the source element.
     *
     * @param string $source
     * @param string $type
     *
     * @return Source
     */
    public function &createSourceElement($source, $type)
    {
        $id = uniqid('audio_src_');
        $source[$id] .= Source::factory()->setSource($source)
            ->setType($type)
            ->build();

        return $source[$id];
    }

    public function build()
    {
        // Build source elements and add them to inner html
        foreach ($this->sources as $source) {
            $this->inner .= $source->build() . PHP_EOL;
        }

        if (empty($this->no_support_text)) {
            $this->no_support_text = 'Html is not supported';
        }

        $this->inner .= $this->no_support_text;

        return parent::build();
    }
}
