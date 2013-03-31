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
 * Extremely simple FinderFactory.
 */
class ehough_fimble_impl_StandardFinderFactory implements ehough_fimble_api_FinderFactory
{

    /**
     * Builds a new finder.
     *
     * @return ehough_fimble_api_Finder The new finder.
     */
    public final function createFinder()
    {
        return new ehough_fimble_impl_StandardFinder();
    }
}