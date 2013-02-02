<?php
/**
 * Copyright 2012 Eric D. Hough (http://ehough.com)
 *
 * This file is part of tickertape (https://github.com/ehough/tickertape)
 *
 * tickertape is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * tickertape is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with tickertape.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/*
 * Original author...
 *
 * Copyright (c) 2004-2012 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class ehough_tickertape_impl_GenericEvent
    extends ehough_tickertape_api_Event implements ArrayAccess
{
    /**
     * Observer pattern subject.
     *
     * @var mixed usually object or callable
     */
    private $_subject;

    /**
     * Array of arguments.
     *
     * @var array
     */
    private $_arguments;

    /**
     * Encapsulate an event with $subject, $args, and $data.
     *
     * @param mixed $subject   The subject of the event, usually an object.
     * @param array $arguments Arguments to store in the event.
     */
    public function __construct($subject = null, array $arguments = array())
    {
        $this->_subject   = $subject;
        $this->_arguments = $arguments;
    }

    /**
     * Getter for subject property.
     *
     * @return mixed $subject The observer subject.
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * Get argument by key.
     *
     * @param string $key Key.
     *
     * @throws InvalidArgumentException If key is not found.
     *
     * @return mixed Contents of array key.
     */
    public function getArgument($key)
    {
        if ($this->hasArgument($key)) {

            return $this->_arguments[$key];
        }

        throw new InvalidArgumentException(sprintf('%s not found in %s', $key, $this->getName()));
    }

    /**
     * Add argument to event.
     *
     * @param string $key   Argument name.
     * @param mixed  $value Value.
     *
     * @return ehough_tickertape_impl_GenericEvent
     */
    public function setArgument($key, $value)
    {
        $this->_arguments[$key] = $value;

        return $this;
    }

    /**
     * Getter for all arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Set args property.
     *
     * @param array $args Arguments.
     *
     * @return ehough_tickertape_impl_GenericEvent
     */
    public function setArguments(array $args = array())
    {
        $this->_arguments = $args;

        return $this;
    }

    /**
     * Has argument.
     *
     * @param string $key Key of arguments array.
     *
     * @return boolean
     */
    public function hasArgument($key)
    {
        return array_key_exists($key, $this->_arguments);
    }

    /**
     * ArrayAccess for argument getter.
     *
     * @param string $key Array key.
     *
     * @throws InvalidArgumentException If key does not exist in $this->args.
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->getArgument($key);
    }

    /**
     * ArrayAccess for argument setter.
     *
     * @param string $key   Array key to set.
     * @param mixed  $value Value.
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->setArgument($key, $value);
    }

    /**
     * ArrayAccess for unset argument.
     *
     * @param string $key Array key.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        if ($this->hasArgument($key)) {

            unset($this->_arguments[$key]);
        }
    }

    /**
     * ArrayAccess has argument.
     *
     * @param string $key Array key.
     *
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->hasArgument($key);
    }
}