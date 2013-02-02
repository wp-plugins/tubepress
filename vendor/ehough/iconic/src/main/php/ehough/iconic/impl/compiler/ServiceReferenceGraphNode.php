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
 * Represents a node in your service graph.
 *
 * Value is typically a definition, or an alias.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ehough_iconic_impl_compiler_ServiceReferenceGraphNode
{
    private $_id;
    private $_inEdges;
    private $_outEdges;
    private $_value;

    /**
     * Constructor.
     *
     * @param string $id    The node identifier
     * @param mixed  $value The node value
     */
    public function __construct($id, $value)
    {
        $this->_id       = $id;
        $this->_value    = $value;
        $this->_inEdges  = array();
        $this->_outEdges = array();
    }

    /**
     * Adds an in edge to this node.
     *
     * @param ehough_iconic_impl_compiler_ServiceReferenceGraphEdge $edge
     */
    public function addInEdge(ehough_iconic_impl_compiler_ServiceReferenceGraphEdge $edge)
    {
        $this->_inEdges[] = $edge;
    }

    /**
     * Adds an out edge to this node.
     *
     * @param ehough_iconic_impl_compiler_ServiceReferenceGraphEdge $edge
     */
    public function addOutEdge(ehough_iconic_impl_compiler_ServiceReferenceGraphEdge $edge)
    {
        $this->_outEdges[] = $edge;
    }

    /**
     * Checks if the value of this node is a Definition.
     *
     * @return Boolean True if the value is a Definition instance
     */
    public function isDefinition()
    {
        return $this->_value instanceof ehough_iconic_impl_Definition;
    }

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns the in edges.
     *
     * @return array The in ServiceReferenceGraphEdge array
     */
    public function getInEdges()
    {
        return $this->_inEdges;
    }

    /**
     * Returns the out edges.
     *
     * @return array The out ServiceReferenceGraphEdge array
     */
    public function getOutEdges()
    {
        return $this->_outEdges;
    }

    /**
     * Returns the value of this Node
     *
     * @return mixed The value
     */
    public function getValue()
    {
        return $this->_value;
    }
}