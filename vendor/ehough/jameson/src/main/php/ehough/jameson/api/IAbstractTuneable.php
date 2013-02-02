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
 * An item that has basic options functionality.
 *
 * @author Eric Hough <eric@ehough.com>
 *
 */
interface ehough_jameson_api_IAbstractTuneable
{
    /**
     * Set options for the encoder.
     *
     * @param array $options An associative array of option => value.
     *
     * @return void
     */
    function setOptions(array $options);

    /**
     * Retrieve the full set of options.
     *
     * @return array The full set of options.
     */
    function getOptions();

    /**
     * Get the value of a single option.
     *
     * @param string $name The name of the option to retrieve.
     *
     * @return mixed The value of the option, or null if no such option.
     */
    function getOption($name);
}