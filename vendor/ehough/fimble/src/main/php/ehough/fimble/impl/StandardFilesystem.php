<?php
/**
 * Copyright 2012 Eric D. Hough (http://ehough.com)
 *
 * This file is part of fimble (https://github.com/ehough/fimble)
 *
 * fimble is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * fimble is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with fimble.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Interface to the filesystem.
 */
class ehough_fimble_impl_StandardFilesystem implements ehough_fimble_api_Filesystem
{
    /**
     * Get the absolute path of a temporary directory, preferably the system directory.
     *
     * @return string The absolute path of a temporary directory, preferably the system directory.
     */
    public final function getSystemTempDirectory()
    {
        if (function_exists('sys_get_temp_dir')) {

            return realpath(sys_get_temp_dir());
        }

        return $this->getSimulatedSystemTempDirectory();
    }

    /**
     * Creates a directory recursively.
     *
     * @param string|array|\Traversable $dirs The directory path.
     * @param int                       $mode The new directory mode, or null for 0777.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException On any directory creation failure.
     */
    public final function mkdirAllowParentCreation($dirs, $mode)
    {
        $this->_mkdir($dirs, $mode, true);
    }

    /**
     * Creates a directory non-recursively.
     *
     * @param string|array|\Traversable $dirs The directory path.
     * @param int                       $mode The new directory mode, or null for 0777.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException On any directory creation failure.
     */
    public final function mkdirPreventParentCreation($dirs, $mode)
    {
        $this->_mkdir($dirs, $mode, false);
    }

    /**
     * Creates a directory recursively.
     *
     * @param string|array|\Traversable $dirs                The directory path.
     * @param integer                   $mode                The directory mode.
     * @param boolean                   $allowParentCreation Allow parent directories to be created.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException On any directory creation failure.
     */
    private function _mkdir($dirs, $mode, $allowParentCreation)
    {
        if ($mode === null) {

            $mode = 0777;
        }

        foreach ($this->toIterator($dirs) as $dir) {

            if (is_dir($dir)) {

                continue;
            }

            if (true !== @mkdir($dir, $mode, $allowParentCreation)) {

                throw new ehough_fimble_api_exception_IOException(sprintf('Failed to create %s', $dir));
            }
        }
    }

    /**
     * Checks the existence of files or directories.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a
     *                                         \Traversable instance to check.
     *
     * @return Boolean true if the file exists, false otherwise.
     */
    public final function exists($files)
    {
        foreach ($this->toIterator($files) as $file) {

            if (! file_exists($file)) {

                return false;
            }
        }

        return true;
    }

    /**
     * Sets access and modification time of file.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a
     *                                         \Traversable instance to create.
     * @param integer                   $time  The touch time as a unix timestamp.
     * @param integer                   $atime The access time as a unix timestamp.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When touch fails.
     */
    public final function touch($files, $time, $atime)
    {
        if ($time === null) {

            $time = time();
        }

        if ($atime === null) {

            $atime = $time;
        }

        foreach ($this->toIterator($files) as $file) {

            if (true !== @touch($file, $time, $atime)) {

                throw new ehough_fimble_api_exception_IOException(sprintf('Failed to touch %s', $file));
            }
        }
    }

    /**
     * Removes files or directories.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a
     *                                         \Traversable instance to remove.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When removal fails.
     */
    public final function remove($files)
    {
        $files = iterator_to_array($this->toIterator($files));
        $files = array_reverse($files);

        foreach ($files as $file) {

            if (! file_exists($file) && ! is_link($file)) {

                continue;
            }

            if (is_dir($file) && ! is_link($file)) {

                $di = new ehough_fimble_impl_iterator_SkipDotsRecursiveDirectoryIterator($file);

                $this->remove($di);

                if (true !== @rmdir($file)) {

                    throw new ehough_fimble_api_exception_IOException(sprintf('Failed to remove directory %s', $file));
                }

            } else {

                // https://bugs.php.net/bug.php?id=52176
                if (defined('PHP_WINDOWS_VERSION_MAJOR') && is_dir($file)) {

                    if (true !== @rmdir($file)) {

                        throw new ehough_fimble_api_exception_IOException(sprintf('Failed to remove file %s', $file));
                    }

                } else {

                    if (true !== @unlink($file)) {

                        throw new ehough_fimble_api_exception_IOException(sprintf('Failed to remove file %s', $file));
                    }
                }
            }
        }
    }

    /**
     * Change mode for an array of files or directories, recursively.
     *
     * @param string|array|\Traversable $files A filename, an array of files,
     *                                         or a \Traversable instance to change mode.
     * @param integer                   $mode  The new mode (octal).
     * @param integer                   $umask The mode mask (octal), or null for 0000.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When the change fail.
     */
    public final function chmodRecursive($files, $mode, $umask)
    {
        $this->_chmod($files, $mode, $umask, true);
    }

    /**
     * Change mode for an array of files or directories, non-recursively.
     *
     * @param string|array|\Traversable $files A filename, an array of files,
     *                                         or a \Traversable instance to change mode.
     * @param integer                   $mode  The new mode (octal).
     * @param integer                   $umask The mode mask (octal), or null for 0000.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When the change fail.
     */
    public final function chmodNonRecursive($files, $mode, $umask)
    {
        $this->_chmod($files, $mode, $umask, false);
    }

    /**
     * Change mode for an array of files or directories.
     *
     * @param string|array|\Traversable $files     A filename, an array of files,
     *                                             or a \Traversable instance to change mode.
     * @param integer                   $mode      The new mode (octal).
     * @param integer                   $umask     The mode mask (octal).
     * @param boolean                   $recursive Whether change the mod recursively or not.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When the change fail.
     */
    private function _chmod($files, $mode, $umask, $recursive)
    {
        if ($umask === null) {

            $umask = 0000;
        }

        foreach ($this->toIterator($files) as $file) {

            if ($recursive && is_dir($file) && !is_link($file)) {

                $di = new ehough_fimble_impl_iterator_SkipDotsRecursiveDirectoryIterator($file);

                $this->_chmod($di, $mode, $umask, true);
            }

            if (@chmod($file, ($mode & ~$umask)) !== true) {

                throw new ehough_fimble_api_exception_IOException(sprintf('Failed to chmod file %s', $file));
            }
        }
    }

    /**
     * Change the owner of an array of files or directories, recursively.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a
     *                                         \Traversable instance to change owner.
     * @param string                    $user  The new owner user name.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When the change fail.
     */
    public final function chownRecursive($files, $user)
    {
        $this->_chown($files, $user, true);
    }

    /**
     * Change the owner of an array of files or directories, non-recursively.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a
     *                                         \Traversable instance to change owner.
     * @param string                    $user  The new owner user name.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When the change fail.
     */
    public final function chownNonRecursive($files, $user)
    {
        $this->_chown($files, $user, false);
    }
    /**
     * Change the owner of an array of files or directories.
     *
     * @param string|array|\Traversable $files     A filename, an array of files, or a
     *                                             \Traversable instance to change owner.
     * @param string                    $user      The new owner user name.
     * @param boolean                   $recursive Whether change the owner recursively or not.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When the change fail.
     */
    private function _chown($files, $user, $recursive)
    {
        foreach ($this->toIterator($files) as $file) {

            if ($recursive && is_dir($file) && !is_link($file)) {

                $di = new ehough_fimble_impl_iterator_SkipDotsRecursiveDirectoryIterator($file);

                $this->chownRecursive($di, $user);
            }

            if (is_link($file) && function_exists('lchown')) {

                if (true !== @lchown($file, $user)) {

                    throw new ehough_fimble_api_exception_IOException(sprintf('Failed to chown file %s', $file));
                }

            } else {

                if (true !== @chown($file, $user)) {

                    throw new ehough_fimble_api_exception_IOException(sprintf('Failed to chown file %s', $file));
                }
            }
        }
    }

    /**
     * Change the group of an array of files or directories, recursively.
     *
     * @param string|array|\Traversable $files A filename, an array of files,
     *                                         or a \Traversable instance to change group.
     * @param string                    $group The group name.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When the change fail.
     */
    public final function chgrpRecursive($files, $group)
    {
        $this->_chgrp($files, $group, true);
    }

    /**
     * Change the group of an array of files or directories, non-recursively.
     *
     * @param string|array|\Traversable $files A filename, an array of files,
     *                                         or a \Traversable instance to change group.
     * @param string                    $group The group name.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When the change fail.
     */
    public final function chgrpNonRecursive($files, $group)
    {
        $this->_chgrp($files, $group, false);
    }

    /**
     * Change the group of an array of files or directories.
     *
     * @param string|array|\Traversable $files     A filename, an array of files,
     *                                             or a \Traversable instance to change group.
     * @param string                    $group     The group name.
     * @param boolean                   $recursive Whether change the group recursively or not.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When the change fail.
     */
    private function _chgrp($files, $group, $recursive)
    {
        foreach ($this->toIterator($files) as $file) {

            if ($recursive && is_dir($file) && !is_link($file)) {

                $di = new ehough_fimble_impl_iterator_SkipDotsRecursiveDirectoryIterator($file);

                $this->chgrpRecursive($di, $group);
            }

            if (is_link($file) && function_exists('lchgrp')) {

                if (true !== @lchgrp($file, $group)) {

                    throw new ehough_fimble_api_exception_IOException(sprintf('Failed to chgrp file %s', $file));
                }

            } else {

                if (true !== @chgrp($file, $group)) {

                    throw new ehough_fimble_api_exception_IOException(sprintf('Failed to chgrp file %s', $file));
                }
            }
        }
    }

    /**
     * Renames a file.
     *
     * @param string $origin The origin filename.
     * @param string $target The new filename.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When target file already exists.
     * @throws ehough_fimble_api_exception_IOException When origin cannot be renamed.
     */
    public final function rename($origin, $target)
    {
        // we check that target does not exist
        if (is_readable($target)) {

            throw new ehough_fimble_api_exception_IOException(sprintf('Cannot rename because the target "%s" already exists.', $target));
        }

        if (true !== @rename($origin, $target)) {

            throw new ehough_fimble_api_exception_IOException(sprintf('Cannot rename "%s" to "%s".', $origin, $target));
        }
    }

    /**
     * Creates a symbolic link or copy a directory.
     *
     * @param string $originDir The origin directory path.
     * @param string $targetDir The symbolic link name.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When symlink fails.
     */
    public final function symlink($originDir, $targetDir)
    {
        $this->mkdirAllowParentCreation(dirname($targetDir), null);

        $ok = false;

        if (is_link($targetDir)) {

            if (readlink($targetDir) != $originDir) {

                $this->remove($targetDir);

            } else {

                $ok = true;
            }
        }

        if (!$ok) {

            if (true !== @symlink($originDir, $targetDir)) {

                throw new
                    ehough_fimble_api_exception_IOException(
                        sprintf('Failed to create symbolic link from %s to %s', $originDir, $targetDir)
                    );
            }
        }
    }

    /**
     * Given an existing path, convert it to a path relative to a given starting path.
     *
     * @param string $endPath   Absolute path of target.
     * @param string $startPath Absolute path where traversal begins.
     *
     * @return string Path of target relative to starting path.
     */
    public final function makePathRelative($endPath, $startPath)
    {
        // Normalize separators on windows
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {

            $endPath   = strtr($endPath, '\\', '/');
            $startPath = strtr($startPath, '\\', '/');
        }

        // Find for which character the the common path stops
        $offset = 0;

        while (isset($startPath[$offset])
            && isset($endPath[$offset]) && $startPath[$offset] === $endPath[$offset]) {

            $offset++;
        }

        // Determine how deep the start path is relative to the common path (ie, "web/bundles" = 2 levels)
        $diffPath = trim(substr($startPath, $offset), '/');
        $depth    = strlen($diffPath) > 0 ? (substr_count($diffPath, '/') + 1) : 0;

        // Repeated "../" for each level need to reach the common path
        $traverser = str_repeat('../', $depth);

        // Construct $endPath from traversing to the common path, then to the remaining $endPath
        return $traverser . substr($endPath, $offset);
    }

    /**
     * Mirrors a directory to another, preventing files from being overwritten.
     *
     * @param string $originDir The origin directory.
     * @param string $targetDir The target directory.
     *
     * @throws ehough_fimble_api_exception_IOException If the mirroring fails.
     *
     * @return void
     */
    public final function mirrorDirectoryPreventFileOverwrite($originDir, $targetDir)
    {
        $this->_mirrorDirectory($originDir, $targetDir, false);
    }

    /**
     * Mirrors a directory to another, allowing files to be overwritten.
     *
     * @param string $originDir The origin directory.
     * @param string $targetDir The target directory.
     *
     * @throws ehough_fimble_api_exception_IOException If the mirroring fails.
     *
     * @return void
     */
    public final function mirrorDirectoryAllowFileOverwrite($originDir, $targetDir)
    {
        $this->_mirrorDirectory($originDir, $targetDir, true);
    }

    /**
     * Mirrors a directory to another.
     *
     * @param string  $originDir     The origin directory.
     * @param string  $targetDir     The target directory.
     * @param boolean $override      Whether to write over existing files or not.
     *
     * @throws ehough_fimble_api_exception_IOException If the mirroring fails.
     *
     * @return void
     */
    private function _mirrorDirectory($originDir, $targetDir, $override)
    {
        $rdi      = new ehough_fimble_impl_iterator_SkipDotsRecursiveDirectoryIterator($originDir);
        $iterator = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::SELF_FIRST);

        $targetDir = rtrim($targetDir, '/\\');
        $originDir = rtrim($originDir, '/\\');

        foreach ($iterator as $file) {

            /** @noinspection PhpUndefinedMethodInspection */
            $target = str_replace($originDir, $targetDir, $file->getPathname());

            if (is_dir($file)) {

                $this->mkdirAllowParentCreation($target, null);

            } else if (is_link($file)) {

                $this->symlink($file, $target);

            } else if (is_file($file)) {

                if ($override) {

                    $this->copyFileAllowOverwrite($file, $target);

                } else {

                    $this->copyFilePreventOverwrite($file, $target);
                }

            } else {

                throw new ehough_fimble_api_exception_IOException(sprintf('Unable to guess "%s" file type.', $file));
            }
        }
    }

    /**
     * Copies a file.
     *
     * This method will always overwrite the destination if it exists.
     *
     * @param string  $originFile The original filename.
     * @param string  $targetFile The target filename.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException If copy fails.
     */
    public final function copyFileAllowOverwrite($originFile, $targetFile)
    {
        $this->_copyFile($originFile, $targetFile, true);
    }

    /**
     * Copies a file.
     *
     * This method does not perform the copy if the destination exists.
     *
     * @param string  $originFile The original filename.
     * @param string  $targetFile The target filename.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException If copy fails.
     */
    public final function copyFilePreventOverwrite($originFile, $targetFile)
    {
        $this->_copyFile($originFile, $targetFile, false);
    }

    /**
     * Copies a file.
     *
     * This method only copies the file if the origin file is newer than the target file.
     *
     * @param string  $originFile     The original filename.
     * @param string  $targetFile     The target filename.
     * @param boolean $allowOverwrite Whether to allow overwrites.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException If copy fails.
     */
    private function _copyFile($originFile, $targetFile, $allowOverwrite)
    {
        $this->mkdirAllowParentCreation(dirname($targetFile), null);

        if (! is_file($targetFile) || $allowOverwrite) {

            if (@copy($originFile, $targetFile) !== true) {

                throw new ehough_fimble_api_exception_IOException(
                    sprintf('Failed to copy %s to %s', $originFile, $targetFile)
                );
            }
        }
    }

    /**
     * This function should not be used externally, outside of testing.
     *
     * @return string The absolute path of a temporary directory, preferably the system directory.
     */
    public final function getSimulatedSystemTempDirectory()
    {
        $fromEnv = $this->_getFromEnvPaths(
            array(
             'TMP',
             'TEMP',
             'TMPDIR',
            )
        );

        if ($fromEnv !== null) {

            return $fromEnv;
        }

        $tempfile = tempnam(
            md5(
                uniqid(
                    rand(),
                    true
                )
            ),
            ''
        );

        if (is_file($tempfile)) {

            $tempdir = realpath(dirname($tempfile));

            unlink($tempfile);

            return realpath($tempdir);
        }

        return false;
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path.
     *
     * @return Boolean True if the file path is absolute, false otherwise.
     */
    public final function isAbsolutePath($file)
    {
        if (strspn($file, '/\\', 0, 1)
            || (strlen($file) > 3 && ctype_alpha($file[0])
            && substr($file, 1, 1) === ':'
            && (strspn($file, '/\\', 2, 1)))
            || (strpos($file, ':') !== false && strpos($file, ':') < 2 && null !== parse_url($file))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Try to fetch a temp path from environment variables.
     *
     * @param array $envKeys The environment variable names to check.
     *
     * @return null|string The first path found, null if none found.
     */
    private function _getFromEnvPaths(array $envKeys)
    {
        foreach ($envKeys as $key) {

            $value = getenv($key);

            if (! empty($value)) {

                return realpath($value);
            }
        }

        return null;
    }

    /**
     * To iterator.
     *
     * @param mixed $files The files to convert.
     *
     * @return Traversable The iterator.
     */
    private function toIterator($files)
    {
        if (! ($files instanceof Traversable)) {

            $files = new ArrayObject(is_array($files) ? $files : array($files));
        }

        return $files;
    }
}