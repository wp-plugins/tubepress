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
 * Compiler Pass Configuration
 *
 * This class has a default configuration embedded.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ehough_iconic_impl_compiler_PassConfig
{
    const TYPE_AFTER_REMOVING      = 'afterRemoving';
    const TYPE_BEFORE_OPTIMIZATION = 'beforeOptimization';
    const TYPE_BEFORE_REMOVING     = 'beforeRemoving';
    const TYPE_OPTIMIZE            = 'optimization';
    const TYPE_REMOVE              = 'removing';

    private $mergePass;
    private $afterRemovingPasses;
    private $beforeOptimizationPasses;
    private $beforeRemovingPasses;
    private $optimizationPasses;
    private $removingPasses;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->mergePass = new ehough_iconic_impl_compiler_MergeExtensionPass();

        $this->afterRemovingPasses = array();
        $this->beforeOptimizationPasses = array();
        $this->beforeRemovingPasses = array();

        $this->optimizationPasses = array(

            new ehough_iconic_impl_compiler_ResolveParameterPlaceHoldersPass(),
            new ehough_iconic_impl_compiler_CheckDefinitionValidityPass(),
            new ehough_iconic_impl_compiler_ResolveInvalidReferencesPass(),
            new ehough_iconic_impl_compiler_AnalyzeServiceReferencesPass(true),
            new ehough_iconic_impl_compiler_CheckCircularReferencesPass(),
        );

        $this->removingPasses = array(

            new ehough_iconic_impl_compiler_RemoveAbstractDefinitionsPass(),
            new ehough_iconic_impl_compiler_RepeatedPass(array(
                new ehough_iconic_impl_compiler_AnalyzeServiceReferencesPass(),
                new ehough_iconic_impl_compiler_InlineServiceDefinitionsPass(),
                new ehough_iconic_impl_compiler_AnalyzeServiceReferencesPass(),
                new ehough_iconic_impl_compiler_RemoveUnusedDefinitionsPass(),
            )),
            new ehough_iconic_impl_compiler_CheckExceptionOnInvalidReferenceBehaviorPass(),
        );
    }

    /**
     * Returns all passes in order to be processed.
     *
     * @return array An array of all passes to process
     *
     * @api
     */
    public function getPasses()
    {
        return array_merge(

            array($this->mergePass),
            $this->beforeOptimizationPasses,
            $this->optimizationPasses,
            $this->beforeRemovingPasses,
            $this->removingPasses,
            $this->afterRemovingPasses
        );
    }

    /**
     * Adds a pass.
     *
     * @param ehough_iconic_api_compiler_ICompilerPass $pass A Compiler pass
     * @param string                                   $type The pass type
     *
     * @throws ehough_iconic_api_exception_InvalidArgumentException when a pass type doesn't exist
     *
     * @return void
     */
    public function addPass(ehough_iconic_api_compiler_ICompilerPass $pass, $type = self::TYPE_BEFORE_OPTIMIZATION)
    {
        $property = $type.'Passes';

        if (!isset($this->$property)) {

            throw new ehough_iconic_api_exception_InvalidArgumentException(sprintf('Invalid type "%s".', $type));
        }

        $passes   = &$this->$property;
        $passes[] = $pass;
    }

    /**
     * Gets all passes for the AfterRemoving pass.
     *
     * @return array An array of passes
     *
     * @api
     */
    public function getAfterRemovingPasses()
    {
        return $this->afterRemovingPasses;
    }

    /**
     * Gets all passes for the BeforeOptimization pass.
     *
     * @return array An array of passes
     *
     * @api
     */
    public function getBeforeOptimizationPasses()
    {
        return $this->beforeOptimizationPasses;
    }

    /**
     * Gets all passes for the BeforeRemoving pass.
     *
     * @return array An array of passes
     *
     * @api
     */
    public function getBeforeRemovingPasses()
    {
        return $this->beforeRemovingPasses;
    }

    /**
     * Gets all passes for the Optimization pass.
     *
     * @return array An array of passes
     *
     * @api
     */
    public function getOptimizationPasses()
    {
        return $this->optimizationPasses;
    }

    /**
     * Gets all passes for the Removing pass.
     *
     * @return array An array of passes
     *
     * @api
     */
    public function getRemovingPasses()
    {
        return $this->removingPasses;
    }

    /**
     * Gets all passes for the Merge pass.
     *
     * @return array An array of passes
     *
     * @api
     */
    public function getMergePass()
    {
        return $this->mergePass;
    }

    /**
     * Sets the Merge Pass.
     *
     * @param ehough_iconic_api_compiler_ICompilerPass $pass The merge pass
     *
     * @api
     */
    public function setMergePass(ehough_iconic_api_compiler_ICompilerPass $pass)
    {
        $this->mergePass = $pass;
    }

    /**
     * Sets the AfterRemoving passes.
     *
     * @param array $passes An array of passes
     *
     * @api
     */
    public function setAfterRemovingPasses(array $passes)
    {
        $this->afterRemovingPasses = $passes;
    }

    /**
     * Sets the BeforeOptimization passes.
     *
     * @param array $passes An array of passes
     *
     * @api
     */
    public function setBeforeOptimizationPasses(array $passes)
    {
        $this->beforeOptimizationPasses = $passes;
    }

    /**
     * Sets the BeforeRemoving passes.
     *
     * @param array $passes An array of passes
     *
     * @api
     */
    public function setBeforeRemovingPasses(array $passes)
    {
        $this->beforeRemovingPasses = $passes;
    }

    /**
     * Sets the Optimization passes.
     *
     * @param array $passes An array of passes
     *
     * @api
     */
    public function setOptimizationPasses(array $passes)
    {
        $this->optimizationPasses = $passes;
    }

    /**
     * Sets the Removing passes.
     *
     * @param array $passes An array of passes
     *
     * @api
     */
    public function setRemovingPasses(array $passes)
    {
        $this->removingPasses = $passes;
    }
}