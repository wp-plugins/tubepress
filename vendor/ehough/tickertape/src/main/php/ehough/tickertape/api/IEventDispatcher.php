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
 * The EventDispatcherInterface is the central point of Symfony's event listener system.
 * Listeners are registered on the manager and events are dispatched through the
 * manager.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface ehough_tickertape_api_IEventDispatcher
{
    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string                      $eventName The name of the event to dispatch. The name of
     *                                               the event is the name of the method that is
     *                                               invoked on listeners.
     * @param ehough_tickertape_api_Event $event     The event to pass to the event handlers/listeners.
     *
     * @return ehough_tickertape_api_Event The event after it has reached all subscribers.
     */
    function dispatch($eventName, ehough_tickertape_api_Event $event);

    /**
     * Dispatches an empty event to all registered listeners.
     *
     * @param string $eventName The name of the event to dispatch. The name of
     *                          the event is the name of the method that is
     *                          invoked on listeners.
     *
     * @return ehough_tickertape_api_Event The event after it has reached all subscribers.
     */
    function dispatchWithoutEventInstance($eventName);

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string   $eventName The event to listen on.
     * @param callable $listener  The listener.
     *
     * @return void
     */
    function addListener($eventName, $listener);

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string   $eventName The event to listen on.
     * @param callable $listener  The listener.
     * @param integer  $priority  The higher this value, the earlier an event
     *                            listener will be triggered in the chain (defaults to 0).
     *
     * @return void
     */
    function addListenerWithPriority($eventName, $listener, $priority);

    /**
     * Removes an event listener from the specified events.
     *
     * @param string|array $eventName The event(s) to remove a listener from.
     * @param callable     $listener  The listener to remove.
     *
     * @return void
     */
    function removeListener($eventName, $listener);

    /**
     * Gets the listeners of a specific event.
     *
     * @param string $eventName The name of the event.
     *
     * @return array The event listeners for the specified event (may be empty).
     */
    function getListeners($eventName);

    /**
     * Gets a map of event names to listeners.
     *
     * @return array The event listeners (may be empty).
     */
    function getAllListeners();

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $eventName The name of the event.
     *
     * @return boolean true if the specified event has any listeners, false otherwise.
     */
    function hasListeners($eventName);
}