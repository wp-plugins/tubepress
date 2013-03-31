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
 * ExcludeDirectoryFilterIterator filters out directories.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ehough_fimble_impl_iterator_ExcludeDirectoryFilterIterator extends ehough_fimble_impl_iterator_FilterIterator
{
    private $_patterns;

    /**
     * Constructor.
     *
     * @param Iterator $iterator    The Iterator to filter.
     * @param array    $directories An array of directories to exclude.
     */
    public function __construct(Iterator $iterator, array $directories)
    {
        $this->_patterns = array();

        foreach ($directories as $directory) {

            $this->_patterns[] = '#(^|/)' . preg_quote($directory, '#') . '(/|$)#';
        }

        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return Boolean true if the value should be kept, false otherwise
     */
    public function accept()
    {
        $current = $this->current();

        $path = $this->isDir() ? $current->getRelativePathname() : $current->getRelativePath();
            $path = strtr($path, '\\', '/');

        foreach ($this->_patterns as $pattern) {

            if (preg_match($pattern, $path)) {

                return false;
            }
        }

        return true;
    }

    public function current()
    {
        return $this->_toRelativeSplFileInfo(parent::current());
    }

    private function _toRelativeSplFileInfo(SplFileInfo $info)
    {
        if ($info instanceof ehough_fimble_impl_RelativeSplFileInfo) {

            return $info;
        }

        //find the nearest directory iterator
        $iterator = $this->getInnerIterator();

        while (!($iterator instanceof DirectoryIterator)) {

            $iterator = $iterator->getInnerIterator();
        }

        $subPath     = $iterator->getSubPath();
        $subPathName = $iterator->getSubPathname();

        return new ehough_fimble_impl_RelativeSplFileInfo($info->getPathname(), $subPath, $subPathName);
    }
}