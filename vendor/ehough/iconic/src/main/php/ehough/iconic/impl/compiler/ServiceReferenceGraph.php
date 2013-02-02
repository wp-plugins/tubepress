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
 * This is a directed graph of your services.
 *
 * This information can be used by your compiler passes instead of collecting
 * it themselves which improves performance quite a lot.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ehough_iconic_impl_compiler_ServiceReferenceGraph
{
    private $_nodes;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_nodes = array();
    }

    /**
     * Checks if the graph has a specific node.
     *
     * @param string $id Id to check
     *
     * @return Boolean
     */
    public function hasNode($id)
    {
        return isset($this->_nodes[$id]);
    }

    /**
     * Gets a node by identifier.
     *
     * @param string $id The id to retrieve
     *
     * @return ehough_iconic_impl_compiler_ServiceReferenceGraphNode The node matching the supplied identifier
     *
     * @throws InvalidArgumentException if no node matches the supplied identifier
     */
    public function getNode($id)
    {
        if (!isset($this->_nodes[$id])) {

            throw new InvalidArgumentException(sprintf('There is no node with id "%s".', $id));
        }

        return $this->_nodes[$id];
    }

    /**
     * Returns all nodes.
     *
     * @return array An array of all ServiceReferenceGraphNode objects
     */
    public function getNodes()
    {
        return $this->_nodes;
    }

    /**
     * Clears all nodes.
     */
    public function clear()
    {
        $this->_nodes = array();
    }

    /**
     * Connects 2 nodes together in the Graph.
     *
     * @param string $sourceId
     * @param string $sourceValue
     * @param string $destId
     * @param string $destValue
     * @param string $reference
     */
    public function connect($sourceId, $sourceValue, $destId, $destValue = null, $reference = null)
    {
        $sourceNode = $this->createNode($sourceId, $sourceValue);
        $destNode   = $this->createNode($destId, $destValue);
        $edge       = new ehough_iconic_impl_compiler_ServiceReferenceGraphEdge($sourceNode, $destNode, $reference);

        $sourceNode->addOutEdge($edge);
        $destNode->addInEdge($edge);
    }

    /**
     * Creates a graph node.
     *
     * @param string $id
     * @param string $value
     *
     * @return ehough_iconic_impl_compiler_ServiceReferenceGraphNode
     */
    private function createNode($id, $value)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        if (isset($this->_nodes[$id]) && $this->_nodes[$id]->getValue() === $value) {

            return $this->_nodes[$id];
        }

        return $this->_nodes[$id] = new ehough_iconic_impl_compiler_ServiceReferenceGraphNode($id, $value);
    }
}