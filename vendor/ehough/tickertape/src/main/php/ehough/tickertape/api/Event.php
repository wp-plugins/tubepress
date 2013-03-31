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

/**
 * Event is the base class for classes containing event data.
 *
 * This class contains no event data. It is used by events that do not pass
 * state information to an event handler when an event is raised.
 *
 * You can call the method stopPropagation() to abort the execution of
 * further listeners in your event listener.
 *
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Bernhard Schussek <bschussek@gmail.com>
 */
class ehough_tickertape_api_Event
{
    /**
     * @var boolean Whether no further event listeners should be triggered
     */
    private $_propagationStopped = false;

    /**
     * @var ehough_tickertape_api_IEventDispatcher Dispatcher that dispatched this event
     */
    private $_dispatcher;

    /**
     * @var string This event's name.
     */
    private $_name;

    /**
     * Returns whether further event listeners should be triggered.
     *
     * @return boolean Whether propagation was already stopped for this event.
     */
    public final function isPropagationStopped()
    {
        return $this->_propagationStopped;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * stopPropagation().
     *
     * @return void
     */
    public final function stopPropagation()
    {
        $this->_propagationStopped = true;
    }

    /**
     * Stores the ehough_tickertape_api_IEventDispatcher that dispatches this Event.
     *
     * @param ehough_tickertape_api_IEventDispatcher $dispatcher The dispatcher that dispatches this event.
     *
     * @return void
     */
    public final function setDispatcher(ehough_tickertape_api_IEventDispatcher $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
    }

    /**
     * Returns the ehough_tickertape_api_IEventDispatcher that dispatches this Event.
     *
     * @return ehough_tickertape_api_IEventDispatcher The dispatcher that dispatches this event.
     */
    public final function getDispatcher()
    {
        return $this->_dispatcher;
    }

    /**
     * Gets the event's name.
     *
     * @return string The event's name.
     */
    public final function getName()
    {
        return $this->_name;
    }

    /**
     * Sets the event's name property.
     *
     * @param string $name The event name.
     *
     * @return void
     */
    public final function setName($name)
    {
        $this->_name = $name;
    }
}