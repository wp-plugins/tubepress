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
 * DepthRangeFilterIterator limits the directory depth.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ehough_fimble_impl_iterator_DepthRangeFilterIterator extends ehough_fimble_impl_iterator_FilterIterator
{
    private $_minDepth = 0;

    /**
     * Constructor.
     *
     * @param RecursiveIteratorIterator $iterator    The Iterator to filter.
     * @param array                     $comparators An array of NumberComparator instances.
     */
    public function __construct(RecursiveIteratorIterator $iterator, array $comparators)
    {
        $minDepth = 0;
        $maxDepth = INF;

        foreach ($comparators as $comparator) {

            switch ($comparator->getOperator()) {

                case '>':

                    $minDepth = $comparator->getTarget() + 1;
                    break;

                case '>=':

                    $minDepth = $comparator->getTarget();
                    break;

                case '<':

                    $maxDepth = $comparator->getTarget() - 1;
                    break;

                case '<=':

                    $maxDepth = $comparator->getTarget();
                    break;

                default:

                    $minDepth = $maxDepth = $comparator->getTarget();
            }
        }

        $this->minDepth = $minDepth;

        $iterator->setMaxDepth(INF === $maxDepth ? (-1) : $maxDepth);

        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return Boolean true if the value should be kept, false otherwise
     */
    public function accept()
    {
        return $this->getInnerIterator()->getDepth() >= $this->minDepth;
    }
}