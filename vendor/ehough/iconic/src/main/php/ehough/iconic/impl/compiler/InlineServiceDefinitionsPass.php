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
 * Inline service definitions where this is possible.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ehough_iconic_impl_compiler_InlineServiceDefinitionsPass implements ehough_iconic_api_compiler_IRepeatableCompilerPass
{
    private $repeatedPass;
    private $graph;
    private $compiler;
    private $currentId;

    /**
     * {@inheritDoc}
     */
    public function setRepeatedPass(ehough_iconic_impl_compiler_RepeatedPass $repeatedPass)
    {
        $this->repeatedPass = $repeatedPass;
    }

    /**
     * Processes the ContainerBuilder for inline service definitions.
     *
     * @param ehough_iconic_impl_ContainerBuilder $container
     */
    public function process(ehough_iconic_impl_ContainerBuilder $container)
    {
        $this->compiler = $container->getCompiler();

        $this->graph = $this->compiler->getServiceReferenceGraph();

        foreach ($container->getDefinitions() as $id => $definition) {
            $this->currentId = $id;

            /** @noinspection PhpUndefinedMethodInspection */
            $definition->setArguments(
                $this->inlineArguments($container, $definition->getArguments())
            );

            /** @noinspection PhpUndefinedMethodInspection */
            $definition->setMethodCalls(
                $this->inlineArguments($container, $definition->getMethodCalls())
            );

            /** @noinspection PhpUndefinedMethodInspection */
            $definition->setProperties(
                $this->inlineArguments($container, $definition->getProperties())
            );
        }
    }

    /**
     * Processes inline arguments.
     *
     * @param ehough_iconic_impl_ContainerBuilder $container The ContainerBuilder
     * @param array                               $arguments An array of arguments
     *
     * @return array
     */
    private function inlineArguments(ehough_iconic_impl_ContainerBuilder $container, array $arguments)
    {
        foreach ($arguments as $k => $argument) {

            if (is_array($argument)) {

                $arguments[$k] = $this->inlineArguments($container, $argument);

            } elseif ($argument instanceof ehough_iconic_impl_Reference) {

                if (!$container->hasDefinition($id = (string) $argument)) {

                    continue;
                }

                if ($this->isInlineableDefinition($container, $id, $definition = $container->getDefinition($id))) {

                    if (ehough_iconic_api_IContainer::SCOPE_PROTOTYPE !== $definition->getScope()) {

                        $arguments[$k] = $definition;

                    } else {

                        $arguments[$k] = clone $definition;
                    }
                }

            } elseif ($argument instanceof ehough_iconic_impl_Definition) {

                $argument->setArguments($this->inlineArguments($container, $argument->getArguments()));
                $argument->setMethodCalls($this->inlineArguments($container, $argument->getMethodCalls()));
                $argument->setProperties($this->inlineArguments($container, $argument->getProperties()));
            }
        }

        return $arguments;
    }

    /**
     * Checks if the definition is inlineable.
     *
     * @param ehough_iconic_impl_ContainerBuilder $container
     * @param string           $id
     * @param ehough_iconic_impl_Definition       $definition
     *
     * @return Boolean If the definition is inlineable
     */
    private function isInlineableDefinition(ehough_iconic_impl_ContainerBuilder $container, $id, ehough_iconic_impl_Definition $definition)
    {
        if (ehough_iconic_api_IContainer::SCOPE_PROTOTYPE === $definition->getScope()) {

            return true;
        }

        if ($definition->isPublic()) {

            return false;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if (!$this->graph->hasNode($id)) {

            return true;
        }

        $ids = array();

        /** @noinspection PhpUndefinedMethodInspection */
        foreach ($this->graph->getNode($id)->getInEdges() as $edge) {

            /** @noinspection PhpUndefinedMethodInspection */
            $ids[] = $edge->getSourceNode()->getId();
        }

        if (count(array_unique($ids)) > 1) {

            return false;
        }

        return $container->getDefinition(reset($ids))->getScope() === $definition->getScope();
    }
}