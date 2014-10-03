<?php
namespace Core\Lib\IO;

/**
 * Class for accessing web content
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 * @todo Very rudimental for now. Used this only for an app.
 *       => Needs to be more complex and maybe it is a good idea to
 *       => make an object of http request it in the future.
 */
class Http
{

    /**
     * Loads an url and writes the returned content to a file
     * 
     * @param string $url URL to request
     * @param string $target Filepath to store the result
     */
    public static function saveUrlToFile($url, $target)
    {
        try {
            $result = self::getURL($url);
            
            if (preg_match('/Found/', $result))
                return false;
            
            $fp = fopen($target, 'wb');
            fwrite($fp, $result);
            fclose($fp);
            
            return true;
        } catch (\Exception $e) {
            echo 'Error on webload ...';
            return false;
        }
    }

    /**
     * Queries an Url
     * 
     * @param string $url Url to load
     * @param number $return
     * @param number $timeout
     * @param string $lang
     * @return mixed
     */
    public static function getURL($url, $return = 1, $timeout = 10, $lang = 'de-DE')
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
     * Sends a header error code
     * 
     * @param unknown $code
     */
    public function sendHttpStatus($code = 500)
    {
        $status = array(
            403 => 'Forbidden',
            404 => 'Not Found',
            410 => 'Gone',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable'
        );
        
        $protocol = preg_match('~HTTP/1\.[01]~i', $_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
        
        if (! isset($statuses[$code]))
            header($protocol . ' 500 Internal Server Error');
        else
            header($protocol . ' ' . $code . ' ' . $status[$code]);
    }
}

