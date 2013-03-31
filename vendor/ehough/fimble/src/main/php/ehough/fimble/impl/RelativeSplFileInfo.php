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
 * Extends SplFileInfo to support relative paths
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ehough_fimble_impl_RelativeSplFileInfo extends SplFileInfo
{
    private $_relativePath;

    private $_relativePathname;

    /**
     * Constructor.
     *
     * @param string $file             The file name.
     * @param string $relativePath     The relative path.
     * @param string $relativePathname The relative path name.
     */
    public function __construct($file, $relativePath, $relativePathname)
    {
        parent::__construct($file);

        $this->_relativePath     = $relativePath;
        $this->_relativePathname = $relativePathname;
    }

    /**
     * Returns the relative path.
     *
     * @return string the relative path
     */
    public function getRelativePath()
    {
        return $this->_relativePath;
    }

    /**
     * Returns the relative path name.
     *
     * @return string the relative path name
     */
    public function getRelativePathname()
    {
        return $this->_relativePathname;
    }
}