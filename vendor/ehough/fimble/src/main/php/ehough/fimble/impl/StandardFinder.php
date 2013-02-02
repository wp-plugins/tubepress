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
class ehough_fimble_impl_StandardFinder implements ehough_fimble_api_Finder
{
    const IGNORE_VCS_FILES = 1;
    const IGNORE_DOT_FILES = 2;

    private $_mode     = 0;
    private $_names    = array();
    private $_notNames = array();
    private $_exclude  = array();
    private $_depths   = array();
    private $_ignore   = 0;
    private $_dirs     = array();

    private static $_vcsPatterns = array(

                                    '.svn',
                                    '_svn',
                                    'CVS',
                                    '_darcs',
                                    '.arch-params',
                                    '.monotone',
                                    '.bzr',
                                    '.git',
                                    '.hg',
                                   );


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_ignore = (self::IGNORE_VCS_FILES | self::IGNORE_DOT_FILES);
    }

    /**
     * Restricts the matching to directories only.
     *
     * @return ehough_fimble_api_Finder The current Finder instance..
     */
    public final function directories()
    {
        $this->_mode = ehough_fimble_impl_iterator_FileTypeIterator::ONLY_DIRECTORIES;

        return $this;
    }

    /**
     * Restricts the matching to files only.
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     */
    public final function files()
    {
        $this->_mode = ehough_fimble_impl_iterator_FileTypeIterator::ONLY_FILES;

        return $this;
    }

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
    public final function depth($level)
    {
        $this->_depths[] = new ehough_fimble_impl_comparator_NumberComparator($level);

        return $this;
    }

    /**
     * Excludes directories.
     *
     * @param string|array $dirs A directory path or an array of directories.
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     */
    public final function exclude($dirs)
    {
        $this->_exclude = array_merge($this->_exclude, (array) $dirs);

        return $this;
    }

    /**
     * Excludes "hidden" directories and files (starting with a dot).
     *
     * @param boolean $ignoreDotFiles Whether to exclude "hidden" files or not.
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     */
    public final function ignoreDotFiles($ignoreDotFiles)
    {
        if ($ignoreDotFiles) {

            $this->_ignore = ($this->_ignore | self::IGNORE_DOT_FILES);

        } else {

            $this->_ignore = ($this->_ignore & ~self::IGNORE_DOT_FILES);
        }

        return $this;

    }

    /**
     * Forces the finder to ignore version control directories.
     *
     * @param boolean $ignoreVCS Whether to exclude VCS files or not.
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     */
    public final function ignoreVCS($ignoreVCS)
    {
        if ($ignoreVCS) {

            $this->_ignore = ($this->_ignore | self::IGNORE_VCS_FILES);

        } else {

            $this->_ignore = ($this->_ignore & ~self::IGNORE_VCS_FILES);
        }

        return $this;
    }

    /**
     * Searches files and directories which match defined rules.
     *
     * @param string|array $dirs A directory path or an array of directories.
     *
     * @return ehough_fimble_api_Finder The current Finder instance.
     *
     * @throws InvalidArgumentException If one of the directory does not exist.
     */
    public final function in($dirs)
    {
        $dirs = (array) $dirs;

        foreach ($dirs as $dir) {

            if (! is_dir($dir)) {

                throw new InvalidArgumentException(sprintf('The "%s" directory does not exist.', $dir));
            }
        }

        $this->_dirs = array_merge($this->_dirs, $dirs);

        return $this;
    }

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
    public final function name($pattern)
    {
        $this->_names[] = $pattern;

        return $this;
    }

    /**
     * Adds rules that files must not match.
     *
     * @param string $pattern A pattern (a regexp, a glob, or a string).
     *
     * @return ehough_fimble_api_Finder The current Finder instance
     */
    public function notName($pattern)
    {
        $this->_notNames[] = $pattern;

        return $this;
    }

    /**
     * Returns an Iterator for the current Finder configuration.
     *
     * This method implements the IteratorAggregate interface.
     *
     * @return Iterator An iterator
     *
     * @throws LogicException If the in() method has not been called.
     */
    public final function getIterator()
    {
        if (0 === count($this->_dirs)) {

            throw new LogicException('You must call the in() method before iterating over a Finder.');
        }

        if (1 === count($this->_dirs)) {

            return $this->_searchInDirectory($this->_dirs[0]);
        }

        $iterator = new AppendIterator();

        foreach ($this->_dirs as $dir) {

            $iterator->append($this->_searchInDirectory($dir));
        }
        return $iterator;
    }

    /**
     * Counts all the results collected by the iterators.
     *
     * @return int The size of this iterator.
     */
    public function count()
    {
        return iterator_count($this->getIterator());
    }

    /**
     * Builds an iterator for the given directory.
     *
     * @param string $dir The directory to work with.
     *
     * @return Iterator The iterator.
     */
    private function _searchInDirectory($dir)
    {
        $rdi      = new ehough_fimble_impl_iterator_RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::SELF_FIRST);

        if ($this->_depths) {

            $iterator = new ehough_fimble_impl_iterator_DepthRangeFilterIterator($iterator, $this->_depths);
        }

        if ($this->_mode) {

            $iterator = new ehough_fimble_impl_iterator_FileTypeIterator($iterator, $this->_mode);
        }

        if (self::IGNORE_VCS_FILES === (self::IGNORE_VCS_FILES & $this->_ignore)) {

            $this->_exclude = array_merge($this->_exclude, self::$_vcsPatterns);
        }

        if (self::IGNORE_DOT_FILES === (self::IGNORE_DOT_FILES & $this->_ignore)) {

            $this->_notNames[] = '/^\..+/';
        }

        if ($this->_exclude) {

            $iterator = new ehough_fimble_impl_iterator_ExcludeDirectoryFilterIterator($iterator, $this->_exclude);
        }

        if ($this->_names || $this->_notNames) {

            $iterator
                = new ehough_fimble_impl_iterator_FileNameFilterIterator($iterator, $this->_names, $this->_notNames);
        }

        return $iterator;
    }
}