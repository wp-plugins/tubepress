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
 * This iterator just overrides the rewind method in order to correct a PHP bug.
 *
 * @see https://bugs.php.net/bug.php?id=49104
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class ehough_fimble_impl_iterator_FilterIterator extends FilterIterator
{
    /**
     * This is a workaround for the problem with \FilterIterator leaving inner \FilesystemIterator in wrong state after
     * rewind in some cases.
     *
     * @see FilterIterator::rewind()
     */
    public function rewind()
    {
        $iterator = $this;

        while ($iterator instanceof OuterIterator) {

            if ($iterator->getInnerIterator() instanceof FilesystemIterator) {

                $iterator->getInnerIterator()->next();
                $iterator->getInnerIterator()->rewind();
            }

            $iterator = $iterator->getInnerIterator();
        }

        parent::rewind();
    }
}