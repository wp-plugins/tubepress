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
 * Handles HTTP client functionality.
 */
interface ehough_shortstop_api_HttpClient
{
    /**
     * Execute a given HTTP request.
     *
     * @param ehough_shortstop_api_HttpRequest $request The HTTP request.
     *
     * @throws ehough_shortstop_api_exception_RuntimeException If something goes wrong.
     *
     * @return ehough_shortstop_api_HttpResponse The HTTP response.
     */
    function execute(ehough_shortstop_api_HttpRequest $request);

    /**
     * Execute a given HTTP request.
     *
     * @param ehough_shortstop_api_HttpRequest         $request The HTTP request.
     * @param ehough_shortstop_api_HttpResponseHandler $handler The HTTP response handler.
     *
     * @throws ehough_shortstop_api_exception_RuntimeException If something goes wrong.
     *
     * @return string The raw entity data in the response. May be empty or null.
     */
    function executeAndHandleResponse(
        ehough_shortstop_api_HttpRequest $request,
        ehough_shortstop_api_HttpResponseHandler $handler
    );
}