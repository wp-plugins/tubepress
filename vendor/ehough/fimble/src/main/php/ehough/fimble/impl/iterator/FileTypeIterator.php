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
 * FileTypeFilterIterator only keeps files, directories, or both.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ehough_fimble_impl_iterator_FileTypeIterator extends ehough_fimble_impl_iterator_FilterIterator
{
    const ONLY_FILES       = 1;
    const ONLY_DIRECTORIES = 2;

    private $_mode;

    /**
     * Constructor.
     *
     * @param Iterator $iterator The Iterator to filter.
     * @param integer  $mode     The mode (self::ONLY_FILES or self::ONLY_DIRECTORIES).
     */
    public function __construct(Iterator $iterator, $mode)
    {
        $this->_mode = $mode;

        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return Boolean true if the value should be kept, false otherwise
     */
    public function accept()
    {
        $filePath = $this->current();

        if (self::ONLY_DIRECTORIES === (self::ONLY_DIRECTORIES & $this->_mode) && $filePath->isFile()) {

            return false;

        }

        if (self::ONLY_FILES === (self::ONLY_FILES & $this->_mode) && $filePath->isDir()) {

            return false;
        }

        return true;
    }
}