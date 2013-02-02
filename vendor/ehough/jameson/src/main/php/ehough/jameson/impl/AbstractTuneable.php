<?php
/**
 * Copyright 2012 Eric D. Hough (http://ehough.com)
 *
 * This file is part of jameson (https://github.com/ehough/jameson)
 *
 * jameson is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * jameson is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with jameson.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Base class for handling options.
 *
 */
abstract class ehough_jameson_impl_AbstractTuneable implements ehough_jameson_api_IAbstractTuneable
{
    /** Option map. */
    private $_options = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_options = $this->_getDefaultOptionMap();
    }

    /**
     * Set options for the encoder.
     *
     * @param array $options An associative array of option => value.
     *
     * @return void
     */
    public final function setOptions(array $options)
    {
        foreach ($options as $key => $value) {

            if (array_key_exists($key, $this->_options)) {

                $this->_options[$key] = $value;
            }
        }
    }

    /**
     * Get the value of a single option.
     *
     * @param string $name The name of the option to retrieve.
     *
     * @return mixed The value of the option, or null if no such option.
     */
    public final function getOption($name)
    {
        if (isset($this->_options[$name])) {

            return $this->_options[$name];
        }

        return null;
    }

    /**
     * Retrieve the full set of options.
     *
     * @return array The full set of options.
     */
    public final function getOptions()
    {
        return $this->_options;
    }

    /**
     * Get the map of default options.
     *
     * @return mixed Map of default options.
     */
    protected abstract function _getDefaultOptionMap();
}