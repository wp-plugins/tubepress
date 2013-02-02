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
 * Provides an interface for iterating recursively over filesystem directories.
 *
 * Manually skips '.' and '..' directories, since no existing method is
 * available in PHP 5.2.
 *
 * @todo Depreciate in favor of RecursiveDirectoryIterator::SKIP_DOTS once PHP
 *   5.3 or later is required.
 *
 * http://drupal.org/node/935036#comment-3766704
 */
class ehough_fimble_impl_iterator_SkipDotsRecursiveDirectoryIterator extends RecursiveDirectoryIterator
{
    /**
     * Constructs a SkipDotsRecursiveDirectoryIterator
     *
     * @param $path
     *   The path of the directory to be iterated over.
     */
    function __construct($path)
    {
        parent::__construct($path);
    }

    function next()
    {
        parent::next();

        while ($this->isDot()) {

            parent::next();
        }
    }
}