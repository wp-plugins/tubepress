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
 * Comparator.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ehough_fimble_impl_comparator_Comparator
{
    private $_target;

    private $_operator = '==';

    /**
     * Gets the target value.
     *
     * @return string The target value.
     */
    public function getTarget()
    {
        return $this->_target;
    }

    /**
     * Sets the target value.
     *
     * @param string $target The target value.
     *
     * @return void
     */
    public function setTarget($target)
    {
        $this->_target = $target;
    }

    /**
     * Gets the comparison operator.
     *
     * @return string The operator.
     */
    public function getOperator()
    {
        return $this->_operator;
    }

    /**
     * Sets the comparison operator.
     *
     * @param string $operator A valid operator.
     *
     * @return void
     *
     * @throws InvalidArgumentException If an invalid operator is supplied.
     */
    public function setOperator($operator)
    {
        if (! $operator) {

            $operator = '==';
        }

        if (! in_array($operator, array('>', '<', '>=', '<=', '==', '!='))) {

            throw new InvalidArgumentException(sprintf('Invalid operator "%s".', $operator));
        }

        $this->_operator = $operator;
    }

    /**
     * Tests against the target.
     *
     * @param mixed $test A test value.
     *
     * @return integer The comparison result.
     */
    public function test($test)
    {
        switch ($this->_operator) {

            case '>':

                return $test > $this->_target;

            case '>=':
                return $test >= $this->_target;

            case '<':

                return $test < $this->_target;

            case '<=':

                return $test <= $this->_target;

            case '!=':

                return $test != $this->_target;
        }

        return $test == $this->_target;
    }
}