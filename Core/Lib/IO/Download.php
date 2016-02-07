<?php
namespace Core\Lib\IO;

use Core\Lib\Http\Header;

/**
 * Download.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Download
{

    /**
     * Header Service
     *
     * @var Header
     */
    private $header;

    /**
     *
     * @var Files
     */
    private $files;

    /**
     * Constructor
     *
     * @param Header $header
     *            Dependency to header service
     */
    public function __construct(Header $header, Files $files)
    {
        $this->header = $header;
        $this->files = $files;
    }

    /**
     * Loads an url and writes the returned content to a file
     *
     * @param string $url
     *            URL to request
     * @param string $target
     *            Filepath to store the result
     *
     * @throws IOException
     */
    public function saveToFile($url, $target)
    {
        try {
            $result = self::getURL($url);

            if (preg_match('/Found/', $result)) {
                return false;
            }

            $fp = fopen($target, 'wb');

            fwrite($fp, $result);
            fclose($fp);

            return true;
        }
        catch (\Exception $e) {

            Throw new IOException('Error on webload: ' . $url);

            return false;
        }
    }

    /**
     * Queries an Url and returns the returned data
     *
     * @param string $url
     *            Url to load
     * @param number $return
     * @param number $timeout
     * @param string $lang
     * @return mixed
     */
    public function loadFromURL($url, $return = 1, $timeout = 10, $lang = 'de-DE')
    {
        // Define useragent
        $agent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ' . $lang . '; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12';

        // Url encode
        $url = urlencode($url);

        // Language of request
        $lang = array(
            'Accept-Language: ' . $lang
        );

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $lang);
        curl_setopt($curl, CURLOPT_USERAGENT, $agent);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, $return);
        curl_setopt($curl, CURLOPT_URL, $url);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    /**
     * Sends file for download
     *
     * @param string $filepath
     *            Full path to file to send
     * @param string $content_type
     *            File content type
     * @param boolean $inline
     *            Flag to show inline. When set to false, the file will be downloaded
     * @param string $name
     *            Optional name of download. When empty
     */
    public function sendFile($file, $content_type = '', $inline = false, $name = '', $download_rate = 0)
    {
        // Check file exists!
        $this->files->exists($file);

        // No content type provided?
        if (! $content_type) {
            $content_type = $this->files->getMimeType($file);
        }

        // Do we have to find out the filename by our own?
        if (! $name) {
            $name = basename($file);
        }

        // Send headers
        $headers = [
            'Content-type: ' . $content_type,
            'Content-Disposition: ' . $inline ? 'inline' : 'attachement' . '; filename="' . $name . '"',
            'Content-Transfer-Encoding: binary',
            'Content-Length: ' . filesize($file),
            'Accept-Ranges: bytes'
        ];

        foreach ($headers as $header) {
            $this->http->header->generic($header);
        }

        if ($download_rate > 0) {

            flush();

            // Open file
            $stream = fopen($file, "r");

            while (! feof($stream)) {

                // Send current file part to the browser
                print fread($stream, round($download_rate * 1024));

                // Flush content to the browser
                flush();

                // Sleep one second
                sleep(1);
            }
            fclose($file);
        }
        else {
            readfile($file);
            exit();
        }
    }
}

