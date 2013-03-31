<?php
/**
 * Copyright 2012 Eric D. Hough (http://ehough.com)
 *
 * This file is part of iconic (https://github.com/ehough/iconic)
 *
 * iconic is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iconic is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with iconic.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/*
 * Original author:
 *
 * (c) Fabien Potencier <fabien@symfony.com>
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
 * Definition represents a service definition.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ehough_iconic_impl_Definition
{
    private $_class;
    private $_file;
    private $_factoryClass;
    private $_factoryMethod;
    private $_factoryService;
    private $_scope;
    private $_properties;
    private $_calls;
    private $_configurator;
    private $_public;
    private $_synthetic;
    private $_abstract;
    private $_arguments;
    private $_tags;

    /**
     * Constructor.
     *
     * @param string $class     The service class
     * @param array  $arguments An array of arguments to pass to the service constructor
     *
     * @api
     */
    public function __construct($class = null, array $arguments = array())
    {
        $this->_class      = $class;
        $this->_arguments  = $arguments;
        $this->_calls      = array();
        $this->_scope      = ehough_iconic_api_IContainer::SCOPE_CONTAINER;
        $this->_public     = true;
        $this->_synthetic  = false;
        $this->_abstract   = false;
        $this->_properties = array();
        $this->_tags       = array();
    }

    /**
     * Sets the name of the class that acts as a factory using the factory method,
     * which will be invoked statically.
     *
     * @param string $factoryClass The factory class name
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function setFactoryClass($factoryClass)
    {
        $this->_factoryClass = $factoryClass;

        return $this;
    }

    /**
     * Gets the factory class.
     *
     * @return string The factory class name
     *
     * @api
     */
    public function getFactoryClass()
    {
        return $this->_factoryClass;
    }

    /**
     * Sets the factory method able to create an instance of this class.
     *
     * @param string $factoryMethod The factory method name
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function setFactoryMethod($factoryMethod)
    {
        $this->_factoryMethod = $factoryMethod;

        return $this;
    }

    /**
     * Gets the factory method.
     *
     * @return string The factory method name
     */
    public function getFactoryMethod()
    {
        return $this->_factoryMethod;
    }

    /**
     * Sets the name of the service that acts as a factory using the factory method.
     *
     * @param string $factoryService The factory service id
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function setFactoryService($factoryService)
    {
        $this->_factoryService = $factoryService;

        return $this;
    }

    /**
     * Gets the factory service id.
     *
     * @return string The factory service id
     */
    public function getFactoryService()
    {
        return $this->_factoryService;
    }

    /**
     * Sets the service class.
     *
     * @param string $class The service class
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function setClass($class)
    {
        $this->_class = $class;

        return $this;
    }

    /**
     * Gets the service class.
     *
     * @return string The service class
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * Sets the arguments to pass to the service constructor/factory method.
     *
     * @param array $arguments An array of arguments
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function setArguments(array $arguments)
    {
        $this->_arguments = $arguments;

        return $this;
    }

    public function setProperties(array $properties)
    {
        $this->_properties = $properties;

        return $this;
    }

    public function getProperties()
    {
        return $this->_properties;
    }

    public function setProperty($name, $value)
    {
        $this->_properties[$name] = $value;

        return $this;
    }

    /**
     * Adds an argument to pass to the service constructor/factory method.
     *
     * @param mixed $argument An argument
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function addArgument($argument)
    {
        $this->_arguments[] = $argument;

        return $this;
    }

    /**
     * Sets a specific argument
     *
     * @param integer $index
     * @param mixed   $argument
     *
     * @return ehough_iconic_impl_Definition The current instance
     *
     * @throws ehough_iconic_api_exception_OutOfBoundsException
     */
    public function replaceArgument($index, $argument)
    {
        if ($index < 0 || $index > count($this->_arguments) - 1) {

            throw new ehough_iconic_api_exception_OutOfBoundsException(sprintf('The index "%d" is not in the range [0, %d].', $index, count($this->_arguments) - 1));
        }

        $this->_arguments[$index] = $argument;

        return $this;
    }

    /**
     * Gets the arguments to pass to the service constructor/factory method.
     *
     * @return array The array of arguments
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Gets an argument to pass to the service constructor/factory method.
     *
     * @param integer $index
     *
     * @return mixed The argument value
     *
     * @throws ehough_iconic_api_exception_OutOfBoundsException
     */
    public function getArgument($index)
    {
        if ($index < 0 || $index > count($this->_arguments) - 1) {

            throw new ehough_iconic_api_exception_OutOfBoundsException(sprintf('The index "%d" is not in the range [0, %d].', $index, count($this->_arguments) - 1));
        }

        return $this->_arguments[$index];
    }

    /**
     * Sets the methods to call after service initialization.
     *
     * @param array $calls An array of method calls
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function setMethodCalls(array $calls = array())
    {
        $this->_calls = array();

        foreach ($calls as $call) {

            $this->addMethodCall($call[0], $call[1]);
        }

        return $this;
    }

    /**
     * Adds a method to call after service initialization.
     *
     * @param string $method    The method name to call
     * @param array  $arguments An array of arguments to pass to the method call
     *
     * @return ehough_iconic_impl_Definition The current instance
     *
     * @throws ehough_iconic_api_exception_InvalidArgumentException on empty $method param
     *
     * @api
     */
    public function addMethodCall($method, array $arguments = array())
    {
        if (empty($method)) {

            throw new ehough_iconic_api_exception_InvalidArgumentException(sprintf('Method name cannot be empty.'));
        }

        $this->_calls[] = array($method, $arguments);

        return $this;
    }

    /**
     * Removes a method to call after service initialization.
     *
     * @param string $method The method name to remove
     *
     * @return ehough_iconic_impl_Definition The current instance
     *
     * @api
     */
    public function removeMethodCall($method)
    {
        foreach ($this->_calls as $i => $call) {

            if ($call[0] === $method) {

                unset($this->_calls[$i]);
                break;
            }
        }

        return $this;
    }

    /**
     * Check if the current definition has a given method to call after service initialization.
     *
     * @param string $method The method name to search for
     *
     * @return Boolean
     */
    public function hasMethodCall($method)
    {
        foreach ($this->_calls as $call) {

            if ($call[0] === $method) {

                return true;
            }
        }

        return false;
    }

    /**
     * Gets the methods to call after service initialization.
     *
     * @return  array An array of method calls
     */
    public function getMethodCalls()
    {
        return $this->_calls;
    }

    /**
     * Sets tags for this definition
     *
     * @param array $tags
     *
     * @return ehough_iconic_impl_Definition the current instance
     */
    public function setTags(array $tags)
    {
        $this->_tags = $tags;

        return $this;
    }

    /**
     * Returns all tags.
     *
     * @return array An array of tags
     */
    public function getTags()
    {
        return $this->_tags;
    }

    /**
     * Gets a tag by name.
     *
     * @param string $name The tag name
     *
     * @return array An array of attributes
     */
    public function getTag($name)
    {
        return isset($this->_tags[$name]) ? $this->_tags[$name] : array();
    }

    /**
     * Adds a tag for this definition.
     *
     * @param string $name       The tag name
     * @param array  $attributes An array of attributes
     *
     * @return ehough_iconic_impl_Definition The current instance
     *
     * @api
     */
    public function addTag($name, array $attributes = array())
    {
        $this->_tags[$name][] = $attributes;

        return $this;
    }

    /**
     * Whether this definition has a tag with the given name
     *
     * @param string $name
     *
     * @return Boolean
     */
    public function hasTag($name)
    {
        return isset($this->_tags[$name]);
    }

    /**
     * Clears all tags for a given name.
     *
     * @param string $name The tag name
     *
     * @return ehough_iconic_impl_Definition
     */
    public function clearTag($name)
    {
        if (isset($this->_tags[$name])) {

            unset($this->_tags[$name]);
        }

        return $this;
    }

    /**
     * Clears the tags for this definition.
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function clearTags()
    {
        $this->_tags = array();

        return $this;
    }

    /**
     * Sets a file to require before creating the service.
     *
     * @param string $file A full pathname to include
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function setFile($file)
    {
        $this->_file = $file;

        return $this;
    }

    /**
     * Gets the file to require before creating the service.
     *
     * @return string The full pathname to include
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * Sets the scope of the service
     *
     * @param string $scope Whether the service must be shared or not
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function setScope($scope)
    {
        $this->_scope = $scope;

        return $this;
    }

    /**
     * Returns the scope of the service
     *
     * @return string
     */
    public function getScope()
    {
        return $this->_scope;
    }

    /**
     * Sets the visibility of this service.
     *
     * @param Boolean $boolean
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function setPublic($boolean)
    {
        $this->_public = (Boolean) $boolean;

        return $this;
    }

    /**
     * Whether this service is public facing
     *
     * @return Boolean
     */
    public function isPublic()
    {
        return $this->_public;
    }

    /**
     * Sets whether this definition is synthetic, that is not constructed by the
     * container, but dynamically injected.
     *
     * @param Boolean $boolean
     *
     * @return ehough_iconic_impl_Definition the current instance
     */
    public function setSynthetic($boolean)
    {
        $this->_synthetic = (Boolean) $boolean;

        return $this;
    }

    /**
     * Whether this definition is synthetic, that is not constructed by the
     * container, but dynamically injected.
     *
     * @return Boolean
     */
    public function isSynthetic()
    {
        return $this->_synthetic;
    }

    /**
     * Whether this definition is abstract, that means it merely serves as a
     * template for other definitions.
     *
     * @param Boolean $boolean
     *
     * @return ehough_iconic_impl_Definition the current instance
     */
    public function setAbstract($boolean)
    {
        $this->_abstract = (Boolean) $boolean;

        return $this;
    }

    /**
     * Whether this definition is abstract, that means it merely serves as a
     * template for other definitions.
     *
     * @return Boolean
     */
    public function isAbstract()
    {
        return $this->_abstract;
    }

    /**
     * Sets a configurator to call after the service is fully initialized.
     *
     * @param mixed $callable A PHP callable
     *
     * @return ehough_iconic_impl_Definition The current instance
     */
    public function setConfigurator($callable)
    {
        $this->_configurator = $callable;

        return $this;
    }

    /**
     * Gets the configurator to call after the service is fully initialized.
     *
     * @return mixed The PHP callable to call
     */
    public function getConfigurator()
    {
        return $this->_configurator;
    }
}