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
 * Run this pass before passes that need to know more about the relation of
 * your services.
 *
 * This class will populate the ServiceReferenceGraph with information. You can
 * retrieve the graph in other passes from the compiler.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ehough_iconic_impl_compiler_AnalyzeServiceReferencesPass implements ehough_iconic_api_compiler_IRepeatableCompilerPass
{
    private $graph;
    private $container;
    private $currentId;
    private $currentDefinition;
    private $repeatedPass;
    private $onlyConstructorArguments;

    /**
     * Constructor.
     *
     * @param Boolean $onlyConstructorArguments Sets this Service Reference pass to ignore method calls
     */
    public function __construct($onlyConstructorArguments = false)
    {
        $this->onlyConstructorArguments = (Boolean) $onlyConstructorArguments;
    }

    /**
     * {@inheritDoc}
     */
    public function setRepeatedPass(ehough_iconic_impl_compiler_RepeatedPass $repeatedPass)
    {
        $this->repeatedPass = $repeatedPass;
    }

    /**
     * Processes a ContainerBuilder object to populate the service reference graph.
     *
     * @param ehough_iconic_impl_ContainerBuilder $container
     */
    public function process(ehough_iconic_impl_ContainerBuilder $container)
    {
        $this->container = $container;
        $this->graph     = $container->getCompiler()->getServiceReferenceGraph();
        $this->graph->clear();

        foreach ($container->getDefinitions() as $id => $definition) {

            /** @noinspection PhpUndefinedMethodInspection */
            if ($definition->isSynthetic() || $definition->isAbstract()) {

                continue;
            }

            $this->currentId = $id;
            $this->currentDefinition = $definition;
            /** @noinspection PhpUndefinedMethodInspection */
            $this->processArguments($definition->getArguments());

            if (!$this->onlyConstructorArguments) {

                /** @noinspection PhpUndefinedMethodInspection */
                $this->processArguments($definition->getMethodCalls());

                /** @noinspection PhpUndefinedMethodInspection */
                $this->processArguments($definition->getProperties());

                /** @noinspection PhpUndefinedMethodInspection */
                if ($definition->getConfigurator()) {

                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->processArguments(array($definition->getConfigurator()));
                }
            }
        }
    }

    /**
     * Processes service definitions for arguments to find relationships for the service graph.
     *
     * @param array $arguments An array of Reference or Definition objects relating to service definitions
     */
    private function processArguments(array $arguments)
    {
        foreach ($arguments as $argument) {

            if (is_array($argument)) {

                $this->processArguments($argument);

            } elseif ($argument instanceof ehough_iconic_impl_Reference) {

                /** @noinspection PhpUndefinedMethodInspection */
                $this->graph->connect(
                    $this->currentId,
                    $this->currentDefinition,
                    $this->getDefinitionId((string) $argument),
                    $this->getDefinition((string) $argument),
                    $argument
                );

            } elseif ($argument instanceof ehough_iconic_impl_Definition) {

                $this->processArguments($argument->getArguments());
                $this->processArguments($argument->getMethodCalls());
                $this->processArguments($argument->getProperties());
            }
        }
    }

    /**
     * Returns a service definition given the full name or an alias.
     *
     * @param string $id A full id or alias for a service definition.
     *
     * @return ehough_iconic_impl_Definition|null The definition related to the supplied id
     */
    private function getDefinition($id)
    {
        $id = $this->getDefinitionId($id);

        /** @noinspection PhpUndefinedMethodInspection */
        return null === $id ? null : $this->container->getDefinition($id);
    }

    private function getDefinitionId($id)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        if (!$this->container->hasDefinition($id)) {

            return null;
        }

        return $id;
    }
}