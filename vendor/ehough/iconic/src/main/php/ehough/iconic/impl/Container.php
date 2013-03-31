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
 * Container is a dependency injection container.
 *
 * It gives access to object instances (services).
 *
 * Services and parameters are simple key/pair stores.
 *
 * Parameter and service keys are case insensitive.
 *
 * A service id can contain lowercased letters, digits, underscores, and dots.
 * Underscores are used to separate words, and dots to group services
 * under namespaces:
 *
 * <ul>
 *   <li>request</li>
 *   <li>mysql_session_storage</li>
 *   <li>symfony.mysql_session_storage</li>
 * </ul>
 *
 * A service can also be defined by creating a method named
 * getXXXService(), where XXX is the camelized version of the id:
 *
 * <ul>
 *   <li>request -> getRequestService()</li>
 *   <li>mysql_session_storage -> getMysqlSessionStorageService()</li>
 *   <li>symfony.mysql_session_storage -> getSymfony_MysqlSessionStorageService()</li>
 * </ul>
 *
 * The container can have three possible behaviors when a service does not exist:
 *
 *  * EXCEPTION_ON_INVALID_REFERENCE: Throws an exception (the default)
 *  * NULL_ON_INVALID_REFERENCE:      Returns null
 *  * IGNORE_ON_INVALID_REFERENCE:    Ignores the wrapping command asking for the reference
 *                                    (for instance, ignore a setter if the service does not exist)
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ehough_iconic_impl_Container implements ehough_iconic_api_IContainer
{
    private $_parameterBag;
    private $_services;
    private $_loading = array();

    /**
     * Constructor.
     *
     * @param ehough_iconic_api_parameterbag_IParameterBag $parameterBag A ParameterBagInterface instance
     */
    public function __construct(ehough_iconic_api_parameterbag_IParameterBag $parameterBag = null)
    {
        $this->_parameterBag = null === $parameterBag ? new ehough_iconic_impl_parameterbag_ParameterBag() : $parameterBag;

        $this->_services = array();

        $this->set('service_container', $this);
    }

    /**
     * Compiles the container.
     *
     * This method does two things:
     *
     *  * Parameter values are resolved;
     *  * The parameter bag is frozen.
     */
    public final function compile()
    {
        $this->_onBeforeCompile();

        $this->_parameterBag->resolve();

        $this->_parameterBag = new ehough_iconic_impl_parameterbag_FrozenParameterBag($this->_parameterBag->all());
    }

    /**
     * Returns true if the container parameter bag are frozen.
     *
     * @return boolean True if the container parameter bag are frozen, false otherwise
     */
    public final function isFrozen()
    {
        return $this->_parameterBag instanceof ehough_iconic_impl_parameterbag_FrozenParameterBag;
    }

    /**
     * Gets the service container parameter bag.
     *
     * @return ehough_iconic_api_parameterbag_IParameterBag A ParameterBagInterface instance
     */
    public final function getParameterBag()
    {
        return $this->_parameterBag;
    }

    /**
     * Gets a parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed  The parameter value
     *
     * @throws ehough_iconic_api_exception_InvalidArgumentException if the parameter is not defined
     */
    public final function getParameter($name)
    {
        return $this->_parameterBag->get($name);
    }

    /**
     * Checks if a parameter exists.
     *
     * @param string $name The parameter name
     *
     * @return Boolean The presence of parameter in container
     */
    public final function hasParameter($name)
    {
        return $this->_parameterBag->has($name);
    }

    /**
     * Sets a parameter.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     */
    public final function setParameter($name, $value)
    {
        $this->_parameterBag->set($name, $value);
    }

    /**
     * Sets a service.
     *
     * @param string $id      The service identifier
     * @param object $service The service instance
     * @param string $scope   The scope of the service
     *
     * @throws ehough_iconic_api_exception_InvalidArgumentException
     */
    public final function set($id, $service, $scope = self::SCOPE_CONTAINER)
    {
        $this->_onBeforeSet($id, $service, $scope);

        if (self::SCOPE_PROTOTYPE === $scope) {

            throw new ehough_iconic_api_exception_InvalidArgumentException('You cannot set services of scope "prototype".');
        }

        $this->_services[strtolower($id)] = $service;
    }

    /**
     * Returns true if the given service is defined.
     *
     * @param string $id The service identifier
     *
     * @return Boolean true if the service is defined, false otherwise
     */
    public final function has($id)
    {
        $id = strtolower($id);

        if ($this->_childHas($id)) {

            return true;
        }

        return isset($this->_services[$id]) || method_exists($this, 'get'.strtr($id, array('_' => '', '.' => '_')).'Service');
    }

    /**
     * Gets a service.
     *
     * If a service is both defined through a set() method and
     * with a set*Service() method, the former has always precedence.
     *
     * @param string  $id              The service identifier
     * @param integer $invalidBehavior The behavior when the service does not exist
     *
     * @return object The associated service
     *
     * @throws ehough_iconic_api_exception_ServiceCircularReferenceException if the service is not defined
     * @throws ehough_iconic_api_exception_ServiceNotFoundException
     * @throws Exception
     */
    public final function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        try {

            return $this->_doGet($id, $invalidBehavior);

        } catch (ehough_iconic_api_exception_InvalidArgumentException $e) {

            return $this->_onGetCausedInvalidArgumentException($e, $id, $invalidBehavior);
        }
    }

    private function _doget($id, $invalidBehavior)
    {
        $id = strtolower($id);

        if (isset($this->_services[$id])) {

            return $this->_services[$id];
        }

        if (isset($this->_loading[$id])) {

            throw new ehough_iconic_api_exception_ServiceCircularReferenceException($id, array_keys($this->_loading));
        }

        if (method_exists($this, $method = 'get'.strtr($id, array('_' => '', '.' => '_')).'Service')) {

            $this->_loading[$id] = true;

            try {

                $service = $this->$method();

            } catch (Exception $e) {

                unset($this->_loading[$id]);

                throw $e;
            }

            unset($this->_loading[$id]);

            return $service;
        }

        if (self::EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior) {

            throw new ehough_iconic_api_exception_ServiceNotFoundException($id);
        }

        return null;
    }

    /**
     * Gets all service ids.
     *
     * @return array An array of all defined service ids
     */
    public final function getServiceIds()
    {
        $ids     = array();
        $r       = new ReflectionClass($this);
        $methods = $r->getMethods();

        foreach ($methods as $method) {

            if (preg_match('/^get(.+)Service$/', $method->name, $match)) {

                $ids[] = self::underscore($match[1]);
            }
        }

        return array_unique(
            array_merge($this->_childServiceIds(), $ids, array_keys($this->_services))
        );
    }

    /**
     * Camelizes a string.
     *
     * @param string $id A string to camelize
     *
     * @return string The camelized string
     */
    public static final function camelize($id)
    {
        return preg_replace_callback('/(^|_|\.)+(.)/', array('ehough_iconic_impl_Container', '_callbackCamelize'), $id);
    }

    public static function _callbackCamelize($string)
    {
        return ('.' === $string[1] ? '_' : '') . strtoupper($string[2]);
    }

    /**
     * A string to underscore.
     *
     * @param string $id The string to underscore
     *
     * @return string The underscored string
     */
    public static final function underscore($id)
    {
        return strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), strtr($id, '_', '.')));
    }

    protected function _isServiceLoading($id)
    {
        return isset($this->_loading[$id]);
    }

    protected function _markServiceAsLoading($id)
    {
        $this->_loading[$id] = true;
    }

    protected function _markServiceAsDoneLoading($id)
    {
        unset($this->_loading[$id]);
    }

    protected function _addService($id, $instance)
    {
        $this->_services[$id] = $instance;
    }

    protected function _onBeforeSet($id, $service, $scope)
    {
        //override point
    }

    protected function _onBeforeCompile()
    {
        //override point
    }

    protected function _childHas(/** @noinspection PhpUnusedParameterInspection */ $id)
    {
        //override point
        return false;
    }

    protected function _onGetCausedInvalidArgumentException(ehough_iconic_api_exception_InvalidArgumentException $e,
        /** @noinspection PhpUnusedParameterInspection */ $id,
        /** @noinspection PhpUnusedParameterInspection */ $invalidBehavior)
    {
        //override point
        throw $e;
    }

    protected function _childServiceIds()
    {
        //override point
        return array();
    }
}
