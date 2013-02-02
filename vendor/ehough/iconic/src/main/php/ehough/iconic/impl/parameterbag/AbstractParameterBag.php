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
 * Holds parameters.
 *
 * @author Eric Hough <eric@ehough.com>
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class ehough_iconic_impl_parameterbag_AbstractParameterBag implements ehough_iconic_api_parameterbag_IParameterBag
{
    private $_parameters = array();

    private $_isResolved = false;

    private $_resolvingStack = array();

    private $_valueStack = array();

    /**
     * Gets the service container parameters.
     *
     * @return array An array of parameters
     */
    public function all()
    {
        return $this->_parameters;
    }

    /**
     * Gets a service container parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed  The parameter value
     *
     * @throws ehough_iconic_api_exception_ParameterNotFoundException if the parameter is not defined
     */
    public function get($name)
    {
        $name = strtolower($name);

        if (! array_key_exists($name, $this->_parameters)) {

            throw new ehough_iconic_api_exception_ParameterNotFoundException($name);
        }

        return $this->_parameters[$name];
    }

    /**
     * Returns true if a parameter name is defined.
     *
     * @param string $name The parameter name
     *
     * @return Boolean true if the parameter name is defined, false otherwise
     */
    public function has($name)
    {
        return array_key_exists(strtolower($name), $this->_parameters);
    }

    /**
     * Replaces parameter placeholders (%name%) by their values for all parameters.
     */
    public function resolve()
    {
        if ($this->_isResolved) {

            return;
        }

        $parameters = array();

        foreach ($this->_parameters as $key => $value) {

            try {

                $value            = $this->resolveValue($value);
                $parameters[$key] = $this->unescapeValue($value);

            } catch (ehough_iconic_api_exception_ParameterNotFoundException $e) {

                $e->setSourceKey($key);

                throw $e;
            }
        }

        $this->_parameters = $parameters;
        $this->_isResolved = true;
    }

    /**
     * Replaces parameter placeholders (%name%) by their values.
     *
     * @param mixed $value     A value
     * @param array $resolving Params that are currently resolving.
     *
     * @return mixed The resolved value
     *
     * @throws ehough_iconic_api_exception_ParameterNotFoundException if a placeholder references a parameter that does not exist
     * @throws ehough_iconic_api_exception_ParameterCircularReferenceException if a circular reference if detected
     * @throws RuntimeException when a given parameter has a type problem.
     */
    public function resolveValue($value, array $resolving = array())
    {
        if (is_array($value)) {

            $args = array();

            foreach ($value as $k => $v) {

                $args[$this->resolveValue($k, $resolving)] = $this->resolveValue($v, $resolving);
            }

            return $args;
        }

        if (! is_string($value)) {

            return $value;
        }

        return $this->_resolveString($value, $resolving);
    }

    public function _resolveString($value, array $resolving = array())
    {
        // we do this to deal with non string values (Boolean, integer, ...)
        // as the preg_replace_callback throw an exception when trying
        // a non-string in a parameter value
        if (preg_match('/^%([^%\s]+)%$/', $value, $match)) {

            $key = strtolower($match[1]);

            if (isset($resolving[$key])) {

                throw new ehough_iconic_api_exception_ParameterCircularReferenceException(array_keys($resolving));
            }

            $resolving[$key] = true;

            return $this->_isResolved ? $this->get($key) : $this->resolveValue($this->get($key), $resolving);
        }

        $this->_pushState($resolving, $value);

        $returnValue = preg_replace_callback('/%%|%([^%\s]+)%/', array($this, '_resolveStringCallback'), $value);

        $this->_popState();

        return $returnValue;
    }

    /**
     * Escape parameter placeholders %
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function escapeValue($value)
    {
        if (is_string($value)) {

            return str_replace('%', '%%', $value);
        }

        if (is_array($value)) {

            $result = array();

            foreach ($value as $k => $v) {

                $result[$k] = $this->escapeValue($v);
            }

            return $result;
        }

        return $value;
    }

    /**
     * Unescape parameter placeholders %
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function unescapeValue($value)
    {
        if (is_string($value)) {

            return str_replace('%%', '%', $value);
        }

        if (is_array($value)) {

            $result = array();

            foreach ($value as $k => $v) {

                $result[$k] = $this->unescapeValue($v);
            }

            return $result;
        }

        return $value;
    }

    protected function _setParameters($params)
    {
        $this->_parameters = $params;
    }

    protected function _setSingleParam($key, $value)
    {
        $this->_parameters[strtolower($key)] = $value;
    }


    protected function _setResolved($resolved)
    {
        $this->_isResolved = $resolved;
    }

    private function _resolveStringCallback($matches)
    {
        // skip %%
        if (! isset($matches[1])) {

            return '%%';
        }

        $key = strtolower($matches[1]);

        $resolving = $this->_getSavedResolving();
        $value     = $this->_getSavedValue();

        if (isset($resolving[$key])) {

            throw new ehough_iconic_api_exception_ParameterCircularReferenceException(array_keys($resolving));
        }

        $resolved = $this->get($key);

        if (! is_string($resolved) && ! is_numeric($resolved)) {

            throw new RuntimeException(sprintf('A string value must be composed of strings and/or numbers, but found parameter "%s" of type %s inside string value "%s".', $key, gettype($resolved), $value));
        }

        $resolved        = (string) $resolved;
        $resolving[$key] = true;

        if ($this->_isResolved) {

            return $resolved;
        }

        return $this->_resolveString($resolved, $resolving);
    }

    private function _getSavedResolving()
    {
        $vals = array_values($this->_resolvingStack);

        return end($vals);
    }

    private function _getSavedValue()
    {
        $vals = array_values($this->_valueStack);

        return end($vals);
    }

    private function _pushState($resolving, $value)
    {
        array_push($this->_resolvingStack, $resolving);
        array_push($this->_valueStack, $value);
    }

    private function _popState()
    {
        array_pop($this->_resolvingStack);
        array_pop($this->_valueStack);
    }
}