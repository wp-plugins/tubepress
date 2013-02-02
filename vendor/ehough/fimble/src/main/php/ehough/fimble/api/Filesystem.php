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
 * Original author...
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface to the filesystem.
 */
interface ehough_fimble_api_Filesystem
{
    /**
     * Get the absolute path of a temporary directory, preferably the system directory.
     *
     * @return string The absolute path of a temporary directory, preferably the system directory.
     */
    function getSystemTempDirectory();

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
    function copyFileAllowOverwrite($originFile, $targetFile);

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
    function copyFilePreventOverwrite($originFile, $targetFile);

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
    function mkdirAllowParentCreation($dirs, $mode);

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
    function mkdirPreventParentCreation($dirs, $mode);

    /**
     * Checks the existence of files or directories.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a
     *                                         \Traversable instance to check.
     *
     * @return Boolean true if the file exists, false otherwise.
     */
    function exists($files);

    /**
     * Sets access and modification time of file.
     *
     * @param string|array|\Traversable $files      A filename, an array of files, or a
     *                                              \Traversable instance to create.
     * @param int                       $time       The touch time, or null to use current time.
     * @param int                       $accessTime The access time, or null to use $time param.
     *
     * @return void
     *
     * @throws ehough_fimble_api_exception_IOException When touch fails.
     */
    function touch($files, $time, $accessTime);

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
    function remove($files);

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
    function chmodRecursive($files, $mode, $umask);

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
    function chmodNonRecursive($files, $mode, $umask);

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
    function chownRecursive($files, $user);

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
    function chownNonRecursive($files, $user);

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
    function chgrpRecursive($files, $group);

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
    function chgrpNonRecursive($files, $group);

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
    function rename($origin, $target);

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
    function symlink($originDir, $targetDir);

    /**
     * Given an existing path, convert it to a path relative to a given starting path.
     *
     * @param string $endPath   Absolute path of target.
     * @param string $startPath Absolute path where traversal begins.
     *
     * @return string Path of target relative to starting path.
     */
    function makePathRelative($endPath, $startPath);

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
    function mirrorDirectoryPreventFileOverwrite($originDir, $targetDir);

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
    function mirrorDirectoryAllowFileOverwrite($originDir, $targetDir);

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path.
     *
     * @return Boolean True if the file path is absolute, false otherwise.
     */
    function isAbsolutePath($file);
}