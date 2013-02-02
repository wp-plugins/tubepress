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
 * MultiplePcreFilterIterator filters files using patterns (regexps, globs or strings).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class ehough_fimble_impl_iterator_MultiplePcreFilterIterator extends ehough_fimble_impl_iterator_FilterIterator
{
    private $_matchRegexps;
    private $_noMatchRegexps;

    /**
     * Constructor.
     *
     * @param Iterator $iterator        The Iterator to filter.
     * @param array    $matchPatterns   An array of patterns that need to match.
     * @param array    $noMatchPatterns An array of patterns that need to not match.
     */
    public function __construct(Iterator $iterator, array $matchPatterns, array $noMatchPatterns)
    {
        $this->_matchRegexps = array();

        foreach ($matchPatterns as $pattern) {

            $this->_matchRegexps[] = $this->toRegex($pattern);
        }

        $this->_noMatchRegexps = array();

        foreach ($noMatchPatterns as $pattern) {

            $this->_noMatchRegexps[] = $this->toRegex($pattern);
        }

        parent::__construct($iterator);
    }

    protected final function _getMatchRegexps()
    {
        return $this->_matchRegexps;
    }

    protected final function _getNoMatchRegexps()
    {
        return $this->_noMatchRegexps;
    }

    /**
     * Checks whether the string is a regex.
     *
     * @param string $str The candidate string.
     *
     * @return Boolean Whether the given string is a regex
     */
    public static function isRegex($str)
    {
        if (preg_match('/^(.{3,}?)[imsxuADU]*$/', $str, $m)) {

            $start = substr($m[1], 0, 1);
            $end   = substr($m[1], -1);

            if ($start === $end) {

                return ! preg_match('/[*?[:alnum:] \\\\]/', $start);
            }

            if ($start === '{' && $end === '}') {

                return true;
            }
        }

        return false;
    }

    /**
     * Converts string into regexp.
     *
     * @param string $str Pattern.
     *
     * @return string Regexp corresponding to a given string.
     */
    abstract protected function toRegex($str);
}