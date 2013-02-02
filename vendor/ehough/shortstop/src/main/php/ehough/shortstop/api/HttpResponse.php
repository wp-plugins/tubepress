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
 * An HTTP response.
 */
class ehough_shortstop_api_HttpResponse extends ehough_shortstop_api_HttpMessage
{
    const HTTP_STATUS_CODE_OK = 200;

    const HTTP_HEADER_TRANSFER_ENCODING = 'Transfer-Encoding';

    private $_statusCode;

    /**
     * Gets the HTTP status code.
     *
     * @return int The HTTP status code.
     */
    public final function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * Sets the HTTP status code.
     *
     * @param int $code The HTTP status code.
     *
     * @throws ehough_shortstop_api_exception_InvalidArgumentException
     *          If the given code is not an integer between 100 and 599.
     *
     * @return void
     */
    public final function setStatusCode($code)
    {
        if (! is_numeric($code)) {

            throw new ehough_shortstop_api_exception_InvalidArgumentException(
                'Status code must be an integer (' . $code . ')'
            );
        }

        $code = intval($code);

        if ($code < 100 || $code > 599) {

            throw new ehough_shortstop_api_exception_InvalidArgumentException(
                'Status code must be in the range of 100 - 599'
            );
        }

        $this->_statusCode = $code;
    }
}