<?php
/**
 * Copyright 2012 Eric D. Hough (http://ehough.com)
 *
 * This file is part of stash (https://github.com/ehough/stash)
 *
 * stash is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * stash is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with stash.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class ehough_stash_impl_PearCacheLiteCache implements ehough_stash_api_Cache
{
    /**
     * How many seconds until a cache entry is considered to be stale.
     */
    const OPTION_LIFETIME_IN_SECONDS = 'lifetimeInSeconds';

    /**
     * How often to clean the cache. 1/x.
     */
    const OPTION_CLEANING_FACTOR = 'cleaningFactor';

    /**
     * Absolute path of cache directory.
     */
    const OPTION_DIRECTORY_PATH = 'directoryPath';

    const HASH_DIR_LEVEL = 1;
    const HASH_DIR_UMASK = 0700;

    /** Flag to indicate if the cache has been initialized. */
    private static $_isInitialized = false;

    /** Flag to indicate if the cache is ready to work. */
    private static $_isAliveAndReady = false;

    /** Options. */
    private $_options = array(

                         self::OPTION_LIFETIME_IN_SECONDS => 3600,
                         self::OPTION_CLEANING_FACTOR     => 20,
                         self::OPTION_DIRECTORY_PATH      => null,
                        );

    /** Cached location of the cache directory. Hehe. */
    private $_cacheDir;

    /** Filesystem service. */
    private $_serviceFilesystem;

    /**
     * Constructor.
     *
     * @param ehough_fimble_api_Filesystem $filesystemService Filesystem service.
     * @param array                        $options           Options array.
     */
    public function __construct(ehough_fimble_api_Filesystem $filesystemService, array $options = array())
    {
        $this->_options           = array_merge($this->_options, $options);
        $this->_serviceFilesystem = $filesystemService;
    }

    /**
     * Get a value from the cache.
     *
     * @param string $key The key of the data to retrieve.
     *
     * @return string The data at the given key, or false if not there.
     */
    public final function get($key)
    {
        if (! $this->_isAliveAndReady()) {

            return false;
        }

        $toReturn              = false;
        $lifetimeInSeconds     = $this->_options[self::OPTION_LIFETIME_IN_SECONDS];
        $refreshTimeInUnixTime = self::_calculateRefreshTimeInUnixTime($lifetimeInSeconds);
        $file                  = $this->_calculateFileNameWithPath($key);

        clearstatcache();

        /* If refresh time is disabled. */
        if ($refreshTimeInUnixTime === null) {

            if (is_file($file)) {

                $toReturn = $this->_read($file, $lifetimeInSeconds);
            }

        } else {

            /* If the file has been modified  */
            if (is_file($file) && @filemtime($file) > $refreshTimeInUnixTime) {

                $toReturn = $this->_read($file, $lifetimeInSeconds);
            }
        }

        return $toReturn;
    }

    /**
     * Save the given data with the given key.
     *
     * @param string $key  The key at which to save the data.
     * @param string $data The data to save at the key.
     *
     * @return boolean True if the data was saved correctly, false otherwise.
     */
    public final function save($key, $data)
    {
        if (! $this->_isAliveAndReady()) {

            return false;
        }

        if (! is_string($data)) {

            return false;
        }

        $lifetimeInSeconds = $this->_options[self::OPTION_LIFETIME_IN_SECONDS];
        $cleaningFactor    = $this->_options[self::OPTION_CLEANING_FACTOR];
        $file              = self::_calculateFileNameWithPath($key);

        if ($cleaningFactor > 0) {

            $rand = rand(1, $cleaningFactor);

            if ($rand === 1) {

                $this->cleanCacheDirectory();
            }
        }

        $res = $this->_writeAndControl($key, $data, $lifetimeInSeconds, $file);

        if (is_bool($res)) {

            if ($res === true) {

                return true;
            }

            // if $res if false, we need to invalidate the cache
            @touch($file, (time() - (2 * abs($lifetimeInSeconds))));

            return false;
        }

        return $res;
    }

    /**
     * Cleans the cache directory. This really shouldn't be called outside of testing.
     *
     * @return void
     */
    public final function cleanCacheDirectory()
    {
        $this->_cleanDir($this->_cacheDir);
    }

    /**
     * Determines if the cache has been initialized and functional.
     *
     * @return bool True if the cache is functional, false otherwise.
     */
    private function _isAliveAndReady()
    {
        if (! self::$_isInitialized) {

            self::$_isInitialized = true;

            $this->_cacheDir = $this->_calculateCacheDir();

            self::$_isAliveAndReady = $this->_cacheDir !== null;
        }

        return self::$_isAliveAndReady;
    }

    /**
     * Remove a file (silencing errors).
     *
     * @param string $file Absolute path to file to delete.
     *
     * @return boolean True if the file was deleted normally, false otherwise.
     */
    private function _unlink($file)
    {
        if (@unlink($file) === false) {

            return false;
        }

        return true;
    }

    /**
     * Recursive function for cleaning cache directory.
     *
     * @param string $dir The directory to clean.
     *
     * @return boolean True if no problems, false otherwise.
     */
    private function _cleanDir($dir)
    {
        $motif = 'cache_';
        $dh    = @opendir($dir);

        if ($dh === false) {

            return false;
        }

        $result = true;

        while ($file = readdir($dh)) {

            if ($file == '.' || $file == '..') {

                continue;
            }

            if (substr($file, 0, 6) != 'cache_') {

                continue;
            }

            $fileTwo = $this->_cacheDir . $file;

            if (is_file($fileTwo)) {

                if (strpos($fileTwo, $motif) !== false) {

                    $wasUnlinked = $this->_unlink($fileTwo);

                    if ($result === true) {

                        $result = $wasUnlinked;
                    }
                }
            }

            if (is_dir($fileTwo) && self::HASH_DIR_LEVEL > 0) {

                $directoryCleaned = $this->_cleanDir($fileTwo . DIRECTORY_SEPARATOR);

                if ($result === true) {

                    $result = $directoryCleaned;
                }
            }
        }

        return $result;
    }

    /**
     * Calculate the hashed file name for the given key.
     *
     * @param string $id The key to identify.
     *
     * @return string The hashed file name for the given key.
     */
    private static function _getFileNameWithoutPath($id)
    {
        return 'cache_' . md5($id);
    }

    /**
     * Calculate the hashed file name for the given key.
     *
     * @param string $id The key to identify.
     *
     * @return string The hashed file name for the given key.
     */
    private function _calculateFileNameWithPath($id)
    {
        $suffix = self::_getFileNameWithoutPath($id);
        $root   = $this->_cacheDir;

        if (self::HASH_DIR_LEVEL > 0) {

            $hash = md5($suffix);

            for ($i = 0; $i < self::HASH_DIR_LEVEL; $i++) {

                $root = $root . 'cache_' . substr($hash, 0, ($i + 1)) . DIRECTORY_SEPARATOR;
            }
        }

        return $root . $suffix;
    }

    /**
     * Read the contents of a file.
     *
     * @param string  $file The file to read.
     * @param integer $life The lifetime of the file.
     *
     * @return bool|string The contents of the file, or false if it could not be read.
     */
    private function _read($file, $life)
    {
        $fp = @fopen($file, 'rb');

        @flock($fp, LOCK_SH);

        if (! $fp) {

            return false;
        }

        clearstatcache();

        $length      = @filesize($file);
        $hashControl = @fread($fp, 32);
        $length      = ($length - 32);

        if ($length) {

            $data = @fread($fp, $length);

        } else {

            $data = '';
        }

        @flock($fp, LOCK_UN);

        @fclose($fp);

        $hashData = self::_hash($data);

        if ($hashData != $hashControl) {

            if ($life !== null) {

                @touch($file, (time() - (2 * abs($life))));

            } else {

                @unlink($file);
            }

            return false;
        }

        return $data;
    }

    /**
     * Writes the data under "key".
     *
     * @param string $key  The key.
     * @param string $data The data to write.
     *
     * @return bool True if the data was written successfully, false otherwise.
     */
    private function _write($key, $data)
    {
        $file     = $this->_calculateFileNameWithPath($key);
        $cacheDir = $this->_cacheDir;

        if (self::HASH_DIR_LEVEL > 0) {

            $hash = md5($file);
            $root = $cacheDir;

            for ($i = 0; $i < self::HASH_DIR_LEVEL; $i++) {

                $root = $root . 'cache_' . substr($hash, 0, ($i + 1)) . DIRECTORY_SEPARATOR;

                if (@is_dir($root) === false) {

                    @mkdir($root, self::HASH_DIR_UMASK, true);
                }
            }
        }

        $dir = dirname($file);

        if (@is_dir($dir) === false) {

            @mkdir($dir, self::HASH_DIR_UMASK, true);
        }

        $fp = @fopen($file, 'wb');

        if ($fp === false) {

            return false;
        }

        @flock($fp, LOCK_EX);
        @fwrite($fp, self::_hash($data), 32);
        @fwrite($fp, $data);
        @flock($fp, LOCK_UN);
        @fclose($fp);

        return true;
    }

    /**
     * Write and control.
     *
     * @param string  $key               The key.
     * @param string  $data              The data.
     * @param integer $lifetimeInSeconds Cache item lifetime in seconds.
     * @param string  $file              The filename.
     *
     * @return bool True if the data was written correctly, false otherwise.
     */
    private function _writeAndControl($key, $data, $lifetimeInSeconds, $file)
    {
        $this->_write($key, $data);

        $dataRead = $this->_read($file, $lifetimeInSeconds);

        if ($dataRead === false) {

            return false;
        }

        return $dataRead == $data;
    }

    /**
     * Calculates the cache directory.
     *
     * @return string The absolute path of the cache directory, or null if we couldn't find
     *                anything to write to.
     */
    private function _calculateCacheDir()
    {
        $context  = tubepress_impl_patterns_sl_ServiceLocator::getExecutionContext();
        $cacheDir = $context->get(tubepress_api_const_options_names_Cache::CACHE_DIR);

        if ($cacheDir != '') {

            return $cacheDir;
        }

        $tempDir = $this->_serviceFilesystem->getSystemTempDirectory();

        if (! is_dir($tempDir) || ! is_writable($tempDir)) {

            return null;
        }

        return $tempDir . '/tubepress_cache/';
    }

    /**
     * Compute the refresh time.
     *
     * @param integer $life The current cache lifetime (in seconds).
     *
     * @return int The Unix time when a cache item must be refreshed.
     */
    private static function _calculateRefreshTimeInUnixTime($life)
    {
        if ($life === 0) {

            return null;

        } else {

            return (time() - $life);
        }
    }

    /**
     * Calculates a quick hash of the data.
     *
     * @param mixed $data The data to hash.
     *
     * @return string A quick hash of the data.
     */
    private static function _hash($data)
    {
        return sprintf('% 32d', crc32($data));
    }
}