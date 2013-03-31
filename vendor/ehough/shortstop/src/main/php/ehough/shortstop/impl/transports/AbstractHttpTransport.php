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
 * Base HTTP command.
 */
abstract class ehough_shortstop_impl_transports_AbstractHttpTransport
    implements ehough_chaingang_api_Command, ehough_shortstop_spi_HttpTransport
{
    private $_httpMessageParser;

    public function __construct(ehough_shortstop_spi_HttpMessageParser $httpMessageParser)
    {
        $this->_httpMessageParser = $httpMessageParser;
    }

    /**
     * Execute a unit of processing work to be performed.
     *
     * This Command may either complete the required processing and return true,
     * or delegate remaining processing to the next Command in a Chain containing
     * this Command by returning false.
     *
     * @param ehough_chaingang_api_Context $context The Context to be processed by this Command.
     *
     * @throws ehough_shortstop_api_exception_RuntimeException If something goes wrong.
     *
     * @return boolean True if the processing of this Context has been completed, or false if the
     *                 processing of this Context should be delegated to a subsequent Command
     *                 in an enclosing Chain.
     */
    public final function execute(ehough_chaingang_api_Context $context)
    {
        /* this will never be null if the parent chain does its job */
        $request = $context->get(ehough_shortstop_impl_HttpClientChain::CHAIN_KEY_REQUEST);
        $logger  = $this->getLogger();

        if ($logger->isDebugEnabled()) {

            $logger->debug(sprintf('Seeing if able to handle %s', $request));
        }

        if ($this->isAvailable() === false || $this->canHandle($request) === false) {

            if ($logger->isDebugEnabled()) {

                $logger->debug(sprintf('Declined to handle %s', $request));
            }

            return false;
        }

        if ($logger->isDebugEnabled()) {

            $logger->debug(sprintf('Offered to handle %s. Now initializing.', $request));
        }

        try {

            $context->put(ehough_shortstop_impl_HttpClientChain::CHAIN_KEY_RESPONSE, $this->handle($request));

            return true;

        } catch (Exception $e) {

            $logger->error(sprintf('Caught exception when handling %s (%s). Will re-throw after tear down.', $request, $e->getMessage()));
            $this->tearDown();
            throw new ehough_shortstop_api_exception_RuntimeException($e->getMessage());
        }
    }

    /**
     * Execute the given HTTP request.
     *
     * @param ehough_shortstop_api_HttpRequest $request The request to execute.
     *
     * @return ehough_shortstop_api_HttpResponse The HTTP response.
     */
    public final function handle(ehough_shortstop_api_HttpRequest $request)
    {
        $logger = $this->getLogger();

        if ($logger->isDebugEnabled()) {

            $logger->debug(sprintf('Preparing to handle %s', $request));
        }

        /** allow for setup */
        $this->prepareToHandleNewRequest($request);

        if ($logger->isDebugEnabled()) {

            $logger->debug(sprintf('Now handling %s', $request));
        }

        /** handle the request. */
        $rawResponse = $this->handleRequest($request);

        if ($logger->isDebugEnabled()) {

            $logger->debug(sprintf('Assembling response from %s', $request));
        }

        $response = $this->_buildResponse($rawResponse, $request);

        if ($logger->isDebugEnabled()) {

            $logger->debug(sprintf('Tearing down after %s', $request));
        }

        $this->tearDown();

        if ($logger->isDebugEnabled()) {

            $logger->debug(sprintf('Successfully handled %s', $request));
        }

        return $response;
    }

    /**
     * Perform handling of the given request.
     *
     * @param ehough_shortstop_api_HttpRequest $request The HTTP request.
     *
     * @return string The raw response for this request. May be empty or null.
     */
    protected abstract function handleRequest(ehough_shortstop_api_HttpRequest $request);

    /**
     * Get the name of this transport.
     *
     * @return string The name of this transport.
     */
    protected abstract function getTransportName();

    /**
     * Get the response code.
     *
     * @return int the HTTP response code.
     */
    protected abstract function getResponseCode();

    /**
     * @return ehough_epilog_api_ILogger
     */
    protected abstract function getLogger();

    /**
     * Perform optional setup to handle a new HTTP request.
     *
     * @param ehough_shortstop_api_HttpRequest $request The HTTP request to handle.
     *
     * @return void
     */
    protected function prepareToHandleNewRequest(ehough_shortstop_api_HttpRequest $request)
    {
        //override point
    }

    /**
     * Perform optional tear down after handling a request.
     *
     * @return void
     */
    protected function tearDown()
    {
        //override point
    }

    private function _buildResponse($rawResponse, ehough_shortstop_api_HttpRequest $request)
    {
        /* first separate the headers from the body */
        $headersString = $this->_httpMessageParser->getHeadersStringFromRawHttpMessage($rawResponse);

        if (empty($headersString)) {

            throw new ehough_shortstop_api_exception_RuntimeException('Could not parse headers from response');
        }

        /* grab the body (may be empty) */
        $bodyString = $this->_httpMessageParser->getBodyStringFromRawHttpMessage($rawResponse);

        /* make an array from the headers (may be empty) */
        $headers = $this->_httpMessageParser->getArrayOfHeadersFromRawHeaderString($headersString);

        /* create a new response. */
        $response = new ehough_shortstop_api_HttpResponse();

        $this->_assignStatusToResponse($response, $request);
        $this->_assignHeadersToResponse($headers, $response, $request);
        $this->_assignEntityToResponse($bodyString, $response, $request);

        return $response;
    }

    private function _assignStatusToResponse(ehough_shortstop_api_HttpResponse $response, ehough_shortstop_api_HttpRequest $request)
    {
        $code = $this->getResponseCode();

        if ($this->getLogger()->isDebugEnabled()) {

            $this->getLogger()->debug(sprintf('%s returned HTTP %s', $request, $code));
        }

        $response->setStatusCode($code);
    }

    private function _assignHeadersToResponse($headerArray, ehough_shortstop_api_HttpResponse $response, ehough_shortstop_api_HttpRequest $request)
    {
        if (! is_array($headerArray) || empty($headerArray)) {

            throw new Exception(sprintf('No headers in response from %s', $request));
        }

        foreach ($headerArray as $name => $value) {

            if (is_array($value)) {

                $value = implode(', ', $value);
            }

            $response->setHeader($name, $value);
        }

        $logger = $this->getLogger();

        /* do some logging */
        if ($logger->isDebugEnabled()) {

            $headerArray = $response->getAllHeaders();

            $logger->debug(sprintf('Here are the ' . count($headerArray) . ' headers in the response for %s', $request));

            foreach($headerArray as $name => $value) {

                $logger->debug("<!--suppress HtmlPresentationalElement --><tt>$name: $value</tt>");
            }
        }
    }

    private function _assignEntityToResponse($body, ehough_shortstop_api_HttpResponse $response, ehough_shortstop_api_HttpRequest $request)
    {
        if ($this->getLogger()->isDebugEnabled()) {

            $this->getLogger()->debug('Assigning (possibly empty) entity to response');
        }

        $entity = new ehough_shortstop_api_HttpEntity();
        $entity->setContent($body);
        $entity->setContentLength(strlen($body));

        $contentType = $response->getHeaderValue(ehough_shortstop_api_HttpResponse::HTTP_HEADER_CONTENT_TYPE);

        if ($contentType !== null) {

            $entity->setContentType($contentType);
        }

        $response->setEntity($entity);
    }
}