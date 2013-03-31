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
 * Deflates data according to RFC 1950.
 */
class ehough_shortstop_impl_contentencoding_NativeDeflateRfc1950Decompressor extends ehough_shortstop_impl_contentencoding_AbstractDecompressorCommand
{
    private $_logger;

    /**
     * Get the uncompressed version of the given data.
     *
     * @param string $compressed The compressed data.
     *
     * @throws ehough_shortstop_api_exception_RuntimeException If no native gzuncompress.
     *
     * @return string The uncompressed data.
     */
    protected function getUncompressed($compressed)
    {
        $decompressed = @gzuncompress($compressed);

        if ($decompressed === false) {

            throw new ehough_shortstop_api_exception_RuntimeException('Could not decompress data with gzuncompress()');
        }

        return $decompressed;
    }

    /**
     * Get the "friendly" name for logging purposes.
     *
     * @return string The "friendly" name of this compression.
     */
    protected function getDecompressionName()
    {
        return 'RFC 1950';
    }

    /**
     * Determines if this compression is available on the host system.
     *
     * @return boolean True if compression is available on the host system, false otherwise.
     */
    protected function isAvailiable()
    {
        return function_exists('gzuncompress');
    }

    /**
     * Get the Content-Encoding header value that this command can handle.
     *
     * @return string The Content-Encoding header value that this command can handle.
     */
    protected function getExpectedContentEncodingHeaderValue()
    {
        return 'deflate';
    }

    /**
     * @return ehough_epilog_api_ILogger
     */
    protected function getLogger()
    {
        if (! isset($this->_logger)) {

            $this->_logger = ehough_epilog_api_LoggerFactory::getLogger('gzuncompress Decompressor');
        }

        return $this->_logger;
    }
}