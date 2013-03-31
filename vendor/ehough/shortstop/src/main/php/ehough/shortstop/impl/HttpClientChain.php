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
 * Class for managing HTTP Transports and making HTTP requests.
 */
class ehough_shortstop_impl_HttpClientChain implements ehough_shortstop_api_HttpClient
{
    /**
     * Request chain key.
     */
    const CHAIN_KEY_REQUEST = 'request';

    /**
     * Response chain key.
     */
    const CHAIN_KEY_RESPONSE = 'response';

    /** @var \ehough_epilog_api_ILogger */
    private $_logger;

    /** @var \ehough_chaingang_api_Chain */
    private $_chain;

    /** @var \ehough_shortstop_spi_HttpContentDecoder */
    private $_httpContentDecoder;

    private $_httpTransferDecoder;

    public function __construct(

        ehough_chaingang_api_Chain               $chain,
        ehough_shortstop_spi_HttpContentDecoder  $httpContentDecoder,
        ehough_shortstop_spi_HttpTransferDecoder $httpTransferDecoder)
    {
        $this->_logger              = ehough_epilog_api_LoggerFactory::getLogger('HTTP Client Chain');
        $this->_chain               = $chain;
        $this->_httpContentDecoder  = $httpContentDecoder;
        $this->_httpTransferDecoder = $httpTransferDecoder;
    }

    /**
     * Execute a given HTTP request.
     *
     * @param ehough_shortstop_api_HttpRequest $request The HTTP request.
     *
     * @throws ehough_shortstop_api_exception_RuntimeException If something goes wrong.
     *
     * @return ehough_shortstop_api_HttpResponse The HTTP response.
     */
    public final function execute(ehough_shortstop_api_HttpRequest $request)
    {
        self::_checkRequest($request);
        $this->_setDefaultHeaders($request);

        if ($this->_logger->isDebugEnabled()) {

            $this->_logger->debug(sprintf('Will attempt %s', $request));
        }

        $this->_logRequest($request);

        $context = new ehough_chaingang_impl_StandardContext();
        $context->put(self::CHAIN_KEY_REQUEST, $request);

        $status = $this->_chain->execute($context);

        if ($status === false) {

            throw new ehough_shortstop_api_exception_RuntimeException(sprintf('No HTTP transports could execute %s', $request));
        }

        $response = $context->get(self::CHAIN_KEY_RESPONSE);

        if ($this->_logger->isDebugEnabled()) {

            $this->_logger->debug('Now decoding response (if required)');
        }

        $this->_decode($this->_httpTransferDecoder, $response, 'Transfer');
        $this->_decode($this->_httpContentDecoder, $response, 'Content');

        $this->_logEntityContent($request, $response);

        return $response;
    }

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
    )
    {
        $response = $this->execute($request);

        return $handler->handle($response);
    }

    private function _logEntityContent(ehough_shortstop_api_HttpRequest  $request,
                                              ehough_shortstop_api_HttpResponse $response)
    {
        if ($this->_logger->isDebugEnabled()) {

            $this->_logger->debug(sprintf('The raw result for %s is in the HTML source for this page <span style="display:none">%s</span>',
                $request, htmlspecialchars(var_export($response, true))));
        }
    }

    /**
     * Check that the request has everything set for execution.
     *
     * @param ehough_shortstop_api_HttpRequest $request The request to check.
     *
     * @throws ehough_shortstop_api_exception_LogicException If the request is not ready.
     */
    private static function _checkRequest(ehough_shortstop_api_HttpRequest $request)
    {
        if ($request->getMethod() === null) {

            throw new ehough_shortstop_api_exception_LogicException('Request has no method set');
        }

        if ($request->getUrl() === null) {

            throw new ehough_shortstop_api_exception_LogicException('Request has no URL set');
        }
    }

    /**
     * Sets some default headers on the request.
     *
     * @param ehough_shortstop_api_HttpRequest $request The request to execute
     *
     * @return void
     */
    private function _setDefaultHeaders(ehough_shortstop_api_HttpRequest $request)
    {
        $this->_setDefaultHeadersBasic($request);
        $this->_setDefaultHeadersCompression($request);
        $this->_setDefaultHeadersContent($request);
    }

    private function _setDefaultHeadersContent(ehough_shortstop_api_HttpRequest $request)
    {
        $entity = $request->getEntity();

        if ($entity === null) {

            if ($this->_logger->isDebugEnabled()) {

                $this->_logger->debug('No HTTP entity in request');
            }

            return;
        }

        $contentLength   = $entity->getContentLength();
        $content         = $entity->getContent();
        $type            = $entity->getContentType();

        if ($content !== null && $contentLength !== null && $type !== null) {

            $request->setHeader(ehough_shortstop_api_HttpMessage::HTTP_HEADER_CONTENT_LENGTH, "$contentLength");
            $request->setHeader(ehough_shortstop_api_HttpMessage::HTTP_HEADER_CONTENT_TYPE, $type);

            return;
        }

        if ($contentLength === null && $this->_logger->isDebugEnabled()) {

            $this->_logger->debug('HTTP entity in request, but no content length set. Ignoring this entity!');
        }

        if ($content === null && $this->_logger->isDebugEnabled()) {

            $this->_logger->isDebugEnabled('HTTP entity in request, but no content set. Ignoring this entity!');
        }

        if ($type === null && $this->_logger->isDebugEnabled()) {

            $this->_logger->debug('HTTP entity in request, but no content type set. Ignoring this entity!');
        }
    }

    /**
     * Sets compression headers.
     *
     * @param ehough_shortstop_api_HttpRequest $request The request to modify.
     *
     * @return void
     */
    private function _setDefaultHeadersCompression(ehough_shortstop_api_HttpRequest $request)
    {
        if ($this->_logger->isDebugEnabled()) {

            $this->_logger->debug('Determining if HTTP compression is available...');
        }

        $header = $this->_httpContentDecoder->getAcceptEncodingHeaderValue();

        if ($header !== null) {

            if ($this->_logger->isDebugEnabled()) {

                $this->_logger->debug('HTTP decompression is available. Yeah!');
            }

            $request->setHeader(ehough_shortstop_api_HttpRequest::HTTP_HEADER_ACCEPT_ENCODING, $header);

        } else {

            if ($this->_logger->isDebugEnabled()) {

                $this->_logger->debug('HTTP decompression is NOT available. Boo.');
            }
        }
    }

    /**
     * Sets things like user agent and HTTP version.
     *
     * @param ehough_shortstop_api_HttpRequest $request The request to modify.
     *
     * @return void
     */
    private function _setDefaultHeadersBasic(ehough_shortstop_api_HttpRequest $request)
    {
        $map = array(

            /* set our User-Agent */
            ehough_shortstop_api_HttpRequest::HTTP_HEADER_USER_AGENT   => 'TubePress; http://tubepress.org',

            /* set to HTTP 1.1 */
            ehough_shortstop_api_HttpRequest::HTTP_HEADER_HTTP_VERSION => 'HTTP/1.0',
        );

        foreach ($map as $headerName => $headerValue) {

            /* only set these headers if someone else hasn't already */
            if (! $request->containsHeader($headerName)) {

                $request->setHeader($headerName, $headerValue);
            }
        }
    }

    private function _decode(ehough_shortstop_spi_HttpResponseDecoder $decoder, $response, $name)
    {
        if ($decoder->needsToBeDecoded($response)) {

            if ($this->_logger->isDebugEnabled()) {

                $this->_logger->debug(sprintf('Response is %s-Encoded. Attempting decode.', $name));
            }

            $decoder->decode($response);

            if ($this->_logger->isDebugEnabled()) {

                $this->_logger->debug(sprintf('Successfully decoded %s-Encoded response.', $name));
            }

        } else {

            if ($this->_logger->isDebugEnabled()) {

                $this->_logger->debug(sprintf('Response is not %s-Encoded.', $name));
            }
        }
    }

    private function _logRequest(ehough_shortstop_api_HttpRequest $request)
    {
        if (! $this->_logger->isDebugEnabled()) {

            return;
        }

        $headerArray = $request->getAllHeaders();

       $this->_logger->debug(sprintf('Here are the ' . count($headerArray) . ' headers in the request for %s', $request));

        foreach($headerArray as $name => $value) {

            $this->_logger->debug("<!--suppress HtmlPresentationalElement --><tt>$name: $value</tt>");
        }
    }
}