<?php
/**
 * Copyright 2006, 2007, 2008, 2009 Eric D. Hough (http://ehough.com)
 * 
 * This file is part of TubePress (http://tubepress.org)
 * 
 * TubePress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * TubePress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with TubePress.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * General purpose cache for TubePress
 */
class org_tubepress_cache_SimpleCacheService implements org_tubepress_cache_CacheService
{
    private $_cache;
    
    /**
     * Simple constructor
     *
     */
    public function __construct()
    {
        /* 
         * thanks to shickm for this...
         * http://code.google.com/p/tubepress/issues/detail?id=27
        */
        if (!function_exists("sys_get_temp_dir")) {
            
            // Based on http://www.phpit.net/
            // article/creating-zip-tar-archives-dynamically-php/2/
            function sys_get_temp_dir()
            {
                // Try to get from environment variable
                if ( !empty($_ENV['TMP']) )
                {
                    return realpath( $_ENV['TMP'] );
                }
                else if ( !empty($_ENV['TMPDIR']) )
                {
                    return realpath( $_ENV['TMPDIR'] );
                }
                else if ( !empty($_ENV['TEMP']) )
                {
                    return realpath( $_ENV['TEMP'] );
                }
            
                // Detect by creating a temporary file
                else
                {
                    // Try to use system's temporary directory
                    // as random name shouldn't exist
                    $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
                    if ( $temp_file )
                    {
                        $temp_dir = realpath( dirname($temp_file) );
                        unlink( $temp_file );
                        return $temp_dir;
                    }
                    else
                    {
                        return FALSE;
                    }
                }
            }
        }
        
        $this->_cache = new net_php_pear_Cache_Lite(array("cacheDir" => sys_get_temp_dir()));
    }
    
    /**
     * @see org_tubepress_cache_CacheService::get($key)
     */
    public function get($key)
    {
        return $this->_cache->get($key);
    }
    
    /**
     * @see org_tubepress_cache_CacheService::has($key)
     */
    public function has($key)
    {
        return $this->_cache->get($key) !== false;
    }
    
    /**
     * @see org_tubepress_cache_CacheService::save($key, $data)
     */
    public function save($key, $data)
    {
        if (!is_string($data)) {
            throw new Exception("Cache can only save string data");
        }
        $this->_cache->save($data, $key);
    }
    
    
}
