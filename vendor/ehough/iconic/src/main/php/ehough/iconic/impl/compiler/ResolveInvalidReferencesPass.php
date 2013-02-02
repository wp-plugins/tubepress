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
 * Emulates the invalid behavior if the reference is not found within the
 * container.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ehough_iconic_impl_compiler_ResolveInvalidReferencesPass implements ehough_iconic_api_compiler_ICompilerPass
{
    /**
     * @var ehough_iconic_impl_ContainerBuilder
     */
    private $container;

    /**
     * Process the ContainerBuilder to resolve invalid references.
     *
     * @param ehough_iconic_impl_ContainerBuilder $container
     */
    public function process(ehough_iconic_impl_ContainerBuilder $container)
    {
        $this->container = $container;

        foreach ($container->getDefinitions() as $definition) {

            /** @noinspection PhpUndefinedMethodInspection */
            if ($definition->isSynthetic() || $definition->isAbstract()) {

                continue;
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $definition->setArguments(

                $this->processArguments($definition->getArguments())
            );

            $calls = array();

            /** @noinspection PhpUndefinedMethodInspection */
            foreach ($definition->getMethodCalls() as $call) {

                try {

                    $calls[] = array($call[0], $this->processArguments($call[1], true));

                } catch (RuntimeException $ignore) {

                    // this call is simply removed
                }
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $definition->setMethodCalls($calls);

            $properties = array();

            /** @noinspection PhpUndefinedMethodInspection */
            foreach ($definition->getProperties() as $name => $value) {

                try {

                    $value             = $this->processArguments(array($value), true);
                    $properties[$name] = reset($value);

                } catch (RuntimeException $ignore) {
                    // ignore property
                }
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $definition->setProperties($properties);
        }
    }

    /**
     * Processes arguments to determine invalid references.
     *
     * @param array   $arguments    An array of Reference objects
     * @param Boolean $inMethodCall
     *
     * @return array
     *
     * @throws ehough_iconic_api_exception_RuntimeException When the config is invalid
     */
    private function processArguments(array $arguments, $inMethodCall = false)
    {
        foreach ($arguments as $k => $argument) {

            if (is_array($argument)) {

                $arguments[$k] = $this->processArguments($argument, $inMethodCall);

            } elseif ($argument instanceof ehough_iconic_impl_Reference) {

                $id = (string) $argument;

                $invalidBehavior = $argument->getInvalidBehavior();
                $exists = $this->container->has($id);

                // resolve invalid behavior
                if ($exists && ehough_iconic_impl_Container::EXCEPTION_ON_INVALID_REFERENCE !== $invalidBehavior) {

                    $arguments[$k] = new ehough_iconic_impl_Reference($id);

                } elseif (!$exists && ehough_iconic_impl_Container::NULL_ON_INVALID_REFERENCE === $invalidBehavior) {

                    $arguments[$k] = null;

                } elseif (!$exists && ehough_iconic_impl_Container::IGNORE_ON_INVALID_REFERENCE === $invalidBehavior) {

                    if ($inMethodCall) {

                        throw new ehough_iconic_api_exception_RuntimeException('Method shouldn\'t be called.');
                    }

                    $arguments[$k] = null;
                }
            }
        }

        return $arguments;
    }
}