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
 * "Finder" based heavily on Symfony's Finder component.
 *
 * https://github.com/symfony/Finder/blob/master/Finder.php
 */

/**
 * Finder allows to build rules to find files and directories.
 *
 * It is a thin wrapper around several specialized iterator classes.
 *
 * All rules may be invoked several times.
 *
 * All methods return the current Finder object to allow easy chaining:
 *
 * $finder = Finder::create()->files()->name('*.php')->in(dirname(__FILE__));
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface ehough_fimble_api_Finder extends IteratorAggregate, Countable
{
    /**
     * Restricts the matching to directories only.
     *
     * @return ehough_fimble_api_Finder The current Finder instance..
     */
    function directories();

    /**
     * Restricts the matching to files only.
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     */
    function files();

    /**
     * Adds tests for the directory depth.
     *
     * Usage:
     *
     *   $finder->depth('> 1') // the Finder will start matching at level 1.
     *   $finder->depth('< 3') // the Finder will descend at most 3 levels of directories below the starting point.
     *
     * @param integer $level The depth level expression.
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     */
    function depth($level);

    /**
     * Excludes directories.
     *
     * @param string|array $dirs A directory path or an array of directories.
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     */
    function exclude($dirs);

    /**
     * Excludes "hidden" directories and files (starting with a dot).
     *
     * @param boolean $ignoreDotFiles Whether to exclude "hidden" files or not.
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     */
    function ignoreDotFiles($ignoreDotFiles);

    /**
     * Forces the finder to ignore version control directories.
     *
     * @param boolean $ignoreVCS Whether to exclude VCS files or not.
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     */
    function ignoreVCS($ignoreVCS);

    /**
     * Searches files and directories which match defined rules.
     *
     * @param string|array $dirs A directory path or an array of directories.
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     *
     * @throws InvalidArgumentException If one of the directory does not exist.
     */
    function in($dirs);

    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * $finder->name('*.php')
     * $finder->name('/\.php$/') // same as above
     * $finder->name('test.php')
     *
     * @param string $pattern A pattern (a regexp, a glob, or a string).
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     */
    function name($pattern);

    /**
     * Adds rules that files must not match.
     *
     * @param string $pattern A pattern (a regexp, a glob, or a string).
     *
     * @return ehough_fimble_api_Finder The current Finder instance
     */
    function notName($pattern);
}