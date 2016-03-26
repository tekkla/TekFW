<?php
namespace Core\Lib\Http;

/**
 * Header.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Header
{

    /**
     *
     * @var array
     */
    private $header = [];

    /**
     * Sends all headers of stack
     */
    public function send()
    {
        foreach ($this->header as $header) {
            header($header[0], $header[1], $header[2]);
        }

        $this->header = [];
    }

    /**
     * Generic header
     *
     * @param unknown $string
     * @param unknown $replace
     * @param unknown $http_response_code
     */
    public function generic($string, $replace = null, $http_response_code = null)
    {
        $this->header[] = [
            $string,
            $replace,
            $http_response_code
        ];
    }

    public function location($location, $permanent = false)
    {
        $this->header[] = [
            'Location: ' . str_replace(' ', '%20', $location),
            null,
            $permanent ? 301 : 302
        ];
    }

    /**
     * Content tyoe header
     *
     * @param string $content_type
     * @param string $charset
     */
    public function contentType($content_type, $charset = '')
    {
        $content_type = 'Content-Type:' . $content_type;

        if ($charset) {
            $content_type .= '; charset=' . $charset;
        }

        $this->header[] = [
            $content_type,
            null,
            null
        ];
    }

    /**
     * No cahing headers
     */
    public function noCache()
    {
        $this->header[] = [
            'Expires: Mon, 26 Jul 1997 05:00:00 GMT',
            null,
            null
        ];

        $this->header[] = [
            'Cache-Control: no-cache',
            null,
            null
        ];

        $this->header[] = [
            'Pragma: no-cache',
            null,
            null
        ];
    }

    /**
     * Sends a header error code
     *
     * @param number $code
     *            HTTP statuscode number
     */
    public function sendHttpError($code = 500)
    {
        $status = array(
            403 => 'Forbidden',
            404 => 'Not Found',
            410 => 'Gone',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable'
        );

        $protocol = preg_match('~HTTP/1\.[01]~i', $_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';

        if (empty($status[$code])) {
            $string = $protocol . ' 500 Internal Server Error';
        }
        else {
            $string = $protocol . ' ' . $code . ' ' . $status[$code];
        }

        $this->header[] = [
            $string,
            null,
            null
        ];
    }
}
