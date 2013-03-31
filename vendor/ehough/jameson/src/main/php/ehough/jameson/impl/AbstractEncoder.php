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
 * Original author...
 *
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc.
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 *
 */
/**
 * JSON encoder. This class is based heavily on Zend_Json's encoder.
 *
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc.
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class ehough_jameson_impl_AbstractEncoder
    extends ehough_jameson_impl_AbstractTuneable implements ehough_jameson_api_IEncoder
{
    /** Whether or not to check for possible object recursion when encoding. */
    const OPTION_CYCLE_CHECK_ENABLED = 'cycleCheckEnabled';

    /** Whether or not to silence recursion errors. */
    const OPTION_SILENCE_CYCLICAL_ERRORS = 'silenceCyclicalErrors';

    /**
     * Try to use PHP's native encoder when possible.
     */
    const OPTION_USE_NATIVE_ENCODER = 'useNativeEncoder';

    /**
     * Get the map of default options.
     *
     * @return mixed Map of default options.
     */
    protected final function _getDefaultOptionMap()
    {
        return array(

                self::OPTION_CYCLE_CHECK_ENABLED     => true,
                self::OPTION_SILENCE_CYCLICAL_ERRORS => false,
                self::OPTION_USE_NATIVE_ENCODER      => true,
               );
    }
}