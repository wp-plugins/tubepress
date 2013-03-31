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
 * An HTTP request.
 */
class ehough_shortstop_api_HttpRequest extends ehough_shortstop_api_HttpMessage
{
    const HTTP_HEADER_USER_AGENT      = 'User-Agent';
    const HTTP_HEADER_ACCEPT_ENCODING = 'Accept-Encoding';

    const HTTP_METHOD_GET  = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT  = 'PUT';

    private $_method;

    private $_url;

    /**
     * Constructor.
     *
     * @param string $method The HTTP method.
     * @param mixed  $url    The URL.
     */
    public final function __construct($method, $url)
    {
        $this->setMethod($method);
        $this->setUrl($url);
    }

    /**
     * Get the HTTP method.
     *
     * @return string The HTTP method. One of GET, PUT, DELETE, or POST.
     */
    public final function getMethod()
    {
        return $this->_method;
    }

    /**
     * Sets the HTTP method.
     *
     * @param string $method The HTTP method.
     *
     * @throws ehough_shortstop_api_exception_InvalidArgumentException
     *              If the method is not a string matching GET, PUT, POST, or DELETE.
     *
     * @return void
     */
    public final function setMethod($method)
    {
        if (preg_match('/get|post|put|delete/i', $method, $matches) !== 1) {

            throw new ehough_shortstop_api_exception_InvalidArgumentException(
                'Method must be PUT, GET, POST, or DELETE'
            );
        }

        $this->_method = strtoupper($method);
    }

    /**
     * Get the URL of this request.
     *
     * @return ehough_curly_Url The URL of this request.
     */
    public final function getUrl()
    {
        return $this->_url;
    }

    /**
     * Sets the URL of this request.
     *
     * @param mixed $url The URL of this request.
     *
     * @throws ehough_shortstop_api_exception_InvalidArgumentException If the given URL is not a valid string URL
     *                                  or instance of ehough_curly_Url
     *
     * @return void
     */
    public final function setUrl($url)
    {
        if (is_string($url)) {

            $this->_url = new ehough_curly_Url($url);

            return;
        }

        if (! $url instanceof ehough_curly_Url) {

            throw new ehough_shortstop_api_exception_InvalidArgumentException(
                'setUrl() only takes a string or a ehough_curly_Url instance'
            );
        }

        $this->_url = $url;
    }

    /**
     * Generate string representation of this request.
     *
     * @return string A string representation of this request.
     */
    public final function toHTML()
    {
        return sprintf('%s to <a href="%s">URL</a>', $this->getMethod(), $this->getUrl());
    }

    /**
     * Generate string representation of this request.
     *
     * @return string A string representation of this request.
     */
    public final function toString()
    {
        return sprintf('%s to %s', $this->_method, $this->_url);
    }

    /**
     * Delegates to toString();
     *
     * @return string A string representation of this request.
     */
    public final function __toString()
    {
        return $this->toString();
    }
}