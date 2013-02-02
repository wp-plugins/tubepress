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
 * This class is used to remove circular dependencies between individual passes.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ehough_iconic_impl_compiler_Compiler
{
    private $_passConfig;
    private $_serviceReferenceGraph;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_passConfig            = new ehough_iconic_impl_compiler_PassConfig();
        $this->_serviceReferenceGraph = new ehough_iconic_impl_compiler_ServiceReferenceGraph();
    }

    /**
     * Returns the ehough_iconic_impl_compiler_PassConfig.
     *
     * @return ehough_iconic_impl_compiler_PassConfig The PassConfig instance
     */
    public function getPassConfig()
    {
        return $this->_passConfig;
    }

    /**
     * Returns the ServiceReferenceGraph.
     *
     * @return ehough_iconic_impl_compiler_ServiceReferenceGraph The ServiceReferenceGraph instance
     */
    public function getServiceReferenceGraph()
    {
        return $this->_serviceReferenceGraph;
    }

    /**
     * Adds a pass to the PassConfig.
     *
     * @param ehough_iconic_api_compiler_ICompilerPass $pass A compiler pass
     * @param string                                   $type The type of the pass
     *
     * @return void
     */
    public function addPass(ehough_iconic_api_compiler_ICompilerPass $pass, $type = ehough_iconic_impl_compiler_PassConfig::TYPE_BEFORE_OPTIMIZATION)
    {
        $this->_passConfig->addPass($pass, $type);
    }

    /**
     * Run the Compiler and process all Passes.
     *
     * @param ehough_iconic_impl_ContainerBuilder $container
     *
     * @api
     */
    public function compile(ehough_iconic_impl_ContainerBuilder $container)
    {
        foreach ($this->_passConfig->getPasses() as $pass) {

            /** @noinspection PhpUndefinedMethodInspection */
            $pass->process($container);
        }
    }
}