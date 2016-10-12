<?php

declare(strict_types=1);

/*
 * This file is part of the RollerworksDatagrid package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Datagrid\Twig\Renderer;

use Rollerworks\Component\Datagrid\BaseView;
use Rollerworks\Component\Datagrid\Column\HeaderView;
use Rollerworks\Component\Datagrid\DatagridRowView;
use Rollerworks\Component\Datagrid\DatagridView;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractRendererEngine
{
    /**
     * The variable in {@link View} used as cache key.
     */
    const CACHE_KEY_VAR = 'unique_block_prefix';

    /**
     * @var array
     */
    protected $defaultThemes;

    /**
     * @var array
     */
    protected $themes = [];

    /**
     * @var array
     */
    protected $resources = [];

    /**
     * @var array
     */
    private $resourceHierarchyLevels = [];

    /**
     * Creates a new renderer engine.
     *
     * @param array $defaultThemes The default themes. The type of these
     *                             themes is open to the implementation
     */
    public function __construct(array $defaultThemes = [])
    {
        $this->defaultThemes = $defaultThemes;
    }

    /**
     * {@inheritdoc}
     */
    public function setTheme(BaseView $view, $themes)
    {
        $cacheKey = $view->vars[self::CACHE_KEY_VAR];

        // Do not cast, as casting turns objects into arrays of properties
        $this->themes[$cacheKey] = is_array($themes) ? $themes : [$themes];

        // Unset instead of resetting to an empty array, in order to allow
        // implementations (like TwigRendererEngine) to check whether $cacheKey
        // is set at all.
        unset($this->resources[$cacheKey], $this->resourceHierarchyLevels[$cacheKey]);
    }

    /**
     * @param BaseView $view
     * @param $blockName
     *
     * @return mixed
     */
    public function getResourceForBlockName(BaseView $view, string $blockName)
    {
        $cacheKey = $view->vars[self::CACHE_KEY_VAR];

        if (!isset($this->resources[$cacheKey][$blockName])) {
            $this->loadResourceForBlockName($cacheKey, $view, $blockName);
        }

        return $this->resources[$cacheKey][$blockName];
    }

    /**
     * @param BaseView $view
     * @param array    $blockNameHierarchy
     * @param          $hierarchyLevel
     *
     * @return mixed
     */
    public function getResourceForBlockNameHierarchy(BaseView $view, array $blockNameHierarchy, int $hierarchyLevel)
    {
        $cacheKey = $view->vars[self::CACHE_KEY_VAR];
        $blockName = $blockNameHierarchy[$hierarchyLevel];

        if (!isset($this->resources[$cacheKey][$blockName])) {
            $this->loadResourceForBlockNameHierarchy($cacheKey, $view, $blockNameHierarchy, $hierarchyLevel);
        }

        return $this->resources[$cacheKey][$blockName];
    }

    public function getResourceHierarchyLevel(BaseView $view, array $blockNameHierarchy, int $hierarchyLevel)
    {
        $cacheKey = $view->vars[self::CACHE_KEY_VAR];
        $blockName = $blockNameHierarchy[$hierarchyLevel];

        if (!isset($this->resources[$cacheKey][$blockName])) {
            $this->loadResourceForBlockNameHierarchy($cacheKey, $view, $blockNameHierarchy, $hierarchyLevel);
        }

        // If $block was previously rendered loaded with loadTemplateForBlock(), the template
        // is cached but the hierarchy level is not. In this case, we know that the  block
        // exists at this very hierarchy level, so we can just set it.
        if (!isset($this->resourceHierarchyLevels[$cacheKey][$blockName])) {
            $this->resourceHierarchyLevels[$cacheKey][$blockName] = $hierarchyLevel;
        }

        return $this->resourceHierarchyLevels[$cacheKey][$blockName];
    }

    /**
     * Loads the cache with the resource for a given block name.
     *
     * @see getResourceForBlock()
     *
     * @param string   $cacheKey  The cache key of the form view
     * @param BaseView $view
     * @param string   $blockName The name of the block to load
     *
     * @return bool True if the resource could be loaded, false otherwise
     */
    abstract protected function loadResourceForBlockName(string $cacheKey, BaseView $view, string $blockName): bool;

    /**
     * @param DatagridView|HeaderView|DatagridRowView $view
     * @param mixed                                   $resource
     * @param string                                  $blockName
     * @param array                                   $variables
     *
     * @return string
     */
    abstract public function renderBlock(BaseView $view, $resource, string $blockName, array $variables = []): string;

    /**
     * Loads the cache with the resource for a specific level of a block hierarchy.
     *
     * @see getResourceForBlockHierarchy()
     *
     * @param string   $cacheKey           The cache key used for storing the
     *                                     resource
     * @param BaseView $view
     * @param array    $blockNameHierarchy The block hierarchy, with the most
     *                                     specific block name at the end
     * @param int      $hierarchyLevel     The level in the block hierarchy that
     *                                     should be loaded
     *
     * @return bool True if the resource could be loaded, false otherwise
     */
    private function loadResourceForBlockNameHierarchy(
        string $cacheKey,
        BaseView $view,
        array $blockNameHierarchy,
        int $hierarchyLevel
    ): bool {
        $blockName = $blockNameHierarchy[$hierarchyLevel];

        // Try to find a template for that block
        if ($this->loadResourceForBlockName($cacheKey, $view, $blockName)) {
            // If loadTemplateForBlock() returns true, it was able to populate the
            // cache. The only missing thing is to set the hierarchy level at which
            // the template was found.
            $this->resourceHierarchyLevels[$cacheKey][$blockName] = $hierarchyLevel;

            return true;
        }

        if ($hierarchyLevel > 0) {
            $parentLevel = $hierarchyLevel - 1;
            $parentBlockName = $blockNameHierarchy[$parentLevel];

            // The next two if statements contain slightly duplicated code. This is by intention
            // and tries to avoid execution of unnecessary checks in order to increase performance.

            if (isset($this->resources[$cacheKey][$parentBlockName])) {
                // It may happen that the parent block is already loaded, but its level is not.
                // In this case, the parent block must have been loaded by loadResourceForBlock(),
                // which does not check the hierarchy of the block. Subsequently the block must have
                // been found directly on the parent level.
                if (!isset($this->resourceHierarchyLevels[$cacheKey][$parentBlockName])) {
                    $this->resourceHierarchyLevels[$cacheKey][$parentBlockName] = $parentLevel;
                }

                // Cache the shortcuts for further accesses
                $this->resources[$cacheKey][$blockName] = $this->resources[$cacheKey][$parentBlockName];
                $this->resourceHierarchyLevels[$cacheKey][$blockName] = $this->resourceHierarchyLevels[$cacheKey][$parentBlockName];

                return true;
            }

            if ($this->loadResourceForBlockNameHierarchy($cacheKey, $view, $blockNameHierarchy, $parentLevel)) {
                // Cache the shortcuts for further accesses
                $this->resources[$cacheKey][$blockName] = $this->resources[$cacheKey][$parentBlockName];
                $this->resourceHierarchyLevels[$cacheKey][$blockName] = $this->resourceHierarchyLevels[$cacheKey][$parentBlockName];

                return true;
            }
        }

        // Cache the result for further accesses
        $this->resources[$cacheKey][$blockName] = false;
        $this->resourceHierarchyLevels[$cacheKey][$blockName] = false;

        return false;
    }
}
