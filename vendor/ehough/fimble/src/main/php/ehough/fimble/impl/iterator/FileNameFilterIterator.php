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
 * FilenameFilterIterator filters files by patterns (a regexp, a glob, or a string).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ehough_fimble_impl_iterator_FileNameFilterIterator extends ehough_fimble_impl_iterator_MultiplePcreFilterIterator
{

    /**
     * Filters the iterator values.
     *
     * @return Boolean true if the value should be kept, false otherwise
     */
    public function accept()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $filename = $this->current()->getFilename();

        // should at least not match one rule to exclude
        foreach ($this->_getNoMatchRegexps() as $regex) {

            if (preg_match($regex, $filename)) {

                return false;
            }
        }

        // should at least match one rule
        $match = true;

        if ($this->_getMatchRegexps()) {

            $match = false;

            foreach ($this->_getMatchRegexps() as $regex) {

                if (preg_match($regex, $filename)) {

                    return true;
                }
            }
        }

        return $match;
    }

    /**
     * Converts glob to regexp.
     *
     * PCRE patterns are left unchanged.
     * Glob strings are transformed with Glob::toRegex().
     *
     * @param string $str Pattern: glob or regexp.
     *
     * @return string regexp corresponding to a given glob or regexp
     */
    protected function toRegex($str)
    {
        return self::isRegex($str) ? $str : ehough_fimble_impl_Glob::toRegex($str);
    }
}