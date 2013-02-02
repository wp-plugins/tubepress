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
 * Glob matches globbing patterns against text.
 *
 *   if match_glob("foo.*", "foo.bar") echo "matched\n";
 *
 * // prints foo.bar and foo.baz
 * $regex = glob_to_regex("foo.*");
 * for (array('foo.bar', 'foo.baz', 'foo', 'bar') as $t)
 * {
 *   if (/$regex/) echo "matched: $car\n";
 * }
 *
 * Glob implements glob(3) style matching that can be used to match
 * against text, rather than fetching names from a filesystem.
 *
 * Based on the Perl Text::Glob module.
 *
 * @author Fabien Potencier <fabien@symfony.com> PHP port
 * @author     Richard Clamp <richardc@unixbeard.net> Perl version
 * @copyright  2004-2005 Fabien Potencier <fabien@symfony.com>
 * @copyright  2002 Richard Clamp <richardc@unixbeard.net>
 */
final class ehough_fimble_impl_Glob
{
    /**
     * Returns a regexp which is the equivalent of the glob pattern.
     *
     * @param string  $glob                The glob pattern.
     * @param boolean $strictLeadingDot    Be strict about leading dots.
     * @param boolean $strictWildcardSlash Be strict about wildcard slashes.
     *
     * @return string regex The regexp
     */
    public static function toRegex($glob, $strictLeadingDot = true, $strictWildcardSlash = true)
    {
        $firstByte = true;
        $escaping  = false;
        $inCurlies = 0;
        $regex     = '';
        $sizeGlob  = strlen($glob);

        for ($i = 0; $i < $sizeGlob; $i++) {

            $car = $glob[$i];

            if ($firstByte) {

                if ($strictLeadingDot && '.' !== $car) {

                    $regex .= '(?=[^\.])';
                }

                $firstByte = false;
            }

            if ('/' === $car) {

                $firstByte = true;
            }

            if ('.' === $car || '(' === $car || ')' === $car || '|' === $car
                || '+' === $car || '^' === $car || '$' === $car
            ) {

                $regex .= "\\$car";

            } else if ('*' === $car) {

                $regex .= $escaping ? '\\*' : ($strictWildcardSlash ? '[^/]*' : '.*');

            } else if ('?' === $car) {

                $regex .= $escaping ? '\\?' : ($strictWildcardSlash ? '[^/]' : '.');

            } else if ('{' === $car) {

                $regex .= $escaping ? '\\{' : '(';

                if (!$escaping) {

                    ++$inCurlies;
                }

            } else if ('}' === $car && $inCurlies) {

                $regex .= $escaping ? '}' : ')';

                if (!$escaping) {

                    --$inCurlies;
                }

            } else if (',' === $car && $inCurlies) {

                $regex .= $escaping ? ',' : '|';

            } else if ('\\' === $car) {

                if ($escaping) {

                    $regex   .= '\\\\';
                    $escaping = false;

                } else {

                    $escaping = true;
                }

                continue;

            } else {

                $regex .= $car;
            }

            $escaping = false;
        }

        return '#^' . $regex . '$#';
    }
}