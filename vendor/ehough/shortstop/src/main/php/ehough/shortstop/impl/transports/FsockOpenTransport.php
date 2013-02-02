<?php
/**
 * Copyright 2012 Eric D. Hough (http://ehough.com)
 *
 * This file is part of shortstop (https://github.com/ehough/shortstop)
 *
 * shortstop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * shortstop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with shortstop.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Lifted from http://core.trac.wordpress.org/browser/tags/3.0.4/wp-includes/class-http.php
 *
 * HTTP request method uses fsockopen function to retrieve the url.
 *
 * This would be the preferred method, but the fsockopen implementation has the most overhead of all
 * the HTTP transport implementations.
 *
 */
class ehough_shortstop_impl_transports_FsockOpenTransport extends ehough_shortstop_impl_transports_AbstractHttpTransport
{
    private $_handle;

    private $_rawMessage;

    /** @var ehough_epilog_api_ILogger */
    private $_logger;

    public function __construct(ehough_shortstop_spi_HttpMessageParser $messageParser)
    {
        parent::__construct($messageParser);

        $this->_logger = ehough_epilog_api_LoggerFactory::getLogger('FsockOpen Transport');
    }

    /**
     * Perform handling of the given request.
     *
     * @param ehough_shortstop_api_HttpRequest $request The HTTP request.
     *
     * @throws ehough_shortstop_api_exception_RuntimeException If something goes wrong.
     *
     * @return string The raw response for this request. May be empty or null.
     */
    protected function handleRequest(ehough_shortstop_api_HttpRequest $request)
    {
        $url  = $request->getUrl();
        $port = $url->getPort() === null ? 80 : $url->getPort();
        $host = $url->getHost();

        //fsockopen has issues with 'localhost' with IPv6 with certain versions of PHP, It attempts to connect to ::1,
        // which fails when the server is not set up for it. For compatibility, always connect to the IPv4 address.
        if ('localhost' == strtolower($host)) {

            $fsockopen_host = '127.0.0.1';
        }

        if ($this->_logger->isDebugEnabled()) {

            $this->_logger->debug('Now calling fsockopen()...');
        }

        $this->_handle = @fsockopen("$host:$port", $port, $iError, $strError, 5);

        if (false === $this->_handle) {

            throw new ehough_shortstop_api_exception_RuntimeException($iError . ': ' . $strError);
        }

        if ($this->_logger->isDebugEnabled()) {

            $this->_logger->debug('Successfully opened handle');
        }

        fwrite($this->_handle, self::_buildHeaderString($request));

        stream_set_timeout($this->_handle, 5);

        if ($this->_logger->isDebugEnabled()) {

            $this->_logger->debug('Reading response...');
        }

        $rawResponse = '';
        while (! feof($this->_handle)) {

            $rawResponse .= fread($this->_handle, 4096);
        }

        if ($this->_logger->isDebugEnabled()) {

            $this->_logger->debug('Done reading response');
        }

        $this->_rawMessage = $rawResponse;

        return $rawResponse;
    }

    /**
     * Get the name of this transport.
     *
     * @return string The name of this transport.
     */
    protected function getTransportName()
    {
        return 'fsockopen()';
    }

    /**
     * Get the response code.
     *
     * @return int the HTTP response code.
     */
    protected function getResponseCode()
    {
        $lines     = explode("\n", $this->_rawMessage);
        $firstLine = $lines[0];

        $pieces = explode(" ", $firstLine);
        return $pieces[1];
    }

    /**
     * Perform optional tear down after handling a request.
     *
     * @return void
     */
    protected function tearDown()
    {
        @fclose($this->_handle);
        unset($this->_handle);
        unset($this->_rawMessage);
    }

    /**
     * Determines whether or not this transport is available on the system.
     *
     * @return bool True if this transport is available on the system. False otherwise.
     */
    public function isAvailable()
    {
        if (! function_exists('fsockopen')) {

            if ($this->_logger->isDebugEnabled()) {

                $this->_logger->debug('fsockopen() does not exist');
            }

            return false;
        }

        return true;
    }

    /**
     * Determines if this transport can handle the given request.
     *
     * @param ehough_shortstop_api_HttpRequest $request The request to handle.
     *
     * @return bool True if this transport can handle the given request. False otherwise.
     */
    public function canHandle(ehough_shortstop_api_HttpRequest $request)
    {
        return true;
    }

    private static function _buildHeaderString(ehough_shortstop_api_HttpRequest $request)
    {
        $url         = $request->getUrl();
        $path        = $url->getPath();
        $query       = $url->getQuery();
        $host        = $url->getHost();
        $entity      = $request->getEntity();
        $headerArray = $request->getAllHeaders();
        $toRequest   = '/';

        if ($path !== null) {

            $toRequest = $path;
        }

        if ($query !== null) {

            $toRequest .= '?' . $query;
        }

        /** Use HTTP 1.0 unless you want this to run SLOW. */
        $strHeaders  = $request->getMethod() . " $toRequest HTTP/1.0\r\n";
        $strHeaders .= "Host: $host\r\n";

        foreach ($headerArray as $name => $value) {

            $strHeaders .= "$name: $value\r\n";
        }

        $strHeaders .= "\r\n";

        if ($entity !== null && $entity->getContent() !== null) {

            $strHeaders .= $entity->getContent();
        }

        return $strHeaders;
    }

    /**
     * @return ehough_epilog_api_ILogger
     */
    protected function getLogger()
    {
        return $this->_logger;
    }
}