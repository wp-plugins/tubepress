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
 * Decodes messages that are Transfer-Encoding: chunked
 */
class ehough_shortstop_impl_transferencoding_ChunkedTransferDecoder implements ehough_chaingang_api_Command
{
    /** @var ehough_epilog_api_ILogger */
    private $_logger;

    public function __construct()
    {
        $this->_logger = ehough_epilog_api_LoggerFactory::getLogger('Chunked-Transfer Decoder');
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
     * @return boolean True if the processing of this Context has been completed, or false if the
     *                 processing of this Context should be delegated to a subsequent Command
     *                 in an enclosing Chain.
     */
    function execute(ehough_chaingang_api_Context $context)
    {
        $response = $context->get(ehough_shortstop_impl_HttpTransferDecoderChain::CHAIN_KEY_RAW_RESPONSE);
        $encoding = $response->getHeaderValue(ehough_shortstop_api_HttpResponse::HTTP_HEADER_TRANSFER_ENCODING);

        if (strcasecmp($encoding, 'chunked') !== 0) {

            if ($this->_logger->isDebugEnabled()) {

                $this->_logger->debug('Response is not encoded with Chunked-Transfer');
            }

            return false;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $decoded = self::_decode($response->getEntity()->getContent());

        $context->put(ehough_shortstop_impl_HttpTransferDecoderChain::CHAIN_KEY_DECODED_RESPONSE, $decoded);

        return true;
    }

    private static function _decode($body)
    {
        /* http://tools.ietf.org/html/rfc2616#section-19.4.6 */

        /* first grab the initial chunk length */
        $chunkLengthPregMatchResult = preg_match('/^\s*([0-9a-fA-F]+)(?:(?!\r\n).)*\r\n/sm', $body, $chunkLengthMatches);

        if ($chunkLengthPregMatchResult === false || count($chunkLengthMatches) !== 2) {

            throw new ehough_shortstop_api_exception_InvalidArgumentException('Data does not appear to be chunked (missing initial chunk length)');
        }

        /* set initial values */
        $currentOffsetIntoBody = strlen($chunkLengthMatches[0]);
        $currentChunkLength    = hexdec($chunkLengthMatches[1]);
        $decoded               = '';
        $bodyLength            = strlen($body);

        while ($currentChunkLength > 0) {

            /* read in the first chunk data */
            $decoded .= substr($body, $currentOffsetIntoBody, $currentChunkLength);

            /* increment the offset to what we just read */
            $currentOffsetIntoBody += $currentChunkLength;

            /* whoa nelly, we've hit the end of the road. */
            if ($currentOffsetIntoBody >= $bodyLength) {

                return $decoded;
            }

            /* grab the next chunk length */
            $chunkLengthPregMatchResult = preg_match('/\r\n\s*([0-9a-fA-F]+)(?:(?!\r\n).)*\r\n/sm', $body, $chunkLengthMatches, null, $currentOffsetIntoBody);

            if ($chunkLengthPregMatchResult === false || count($chunkLengthMatches) !== 2) {

                return $decoded;
            }

            /* increment the offset to start of next data */
            $currentOffsetIntoBody += strlen($chunkLengthMatches[0]);

            /* set up how much data we want to read */
            $currentChunkLength = hexdec($chunkLengthMatches[1]);
        }

        return $decoded;
    }
}
