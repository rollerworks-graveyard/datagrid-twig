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
use Rollerworks\Component\Datagrid\Column\CellView;
use Rollerworks\Component\Datagrid\Column\HeaderView;
use Rollerworks\Component\Datagrid\DatagridView;
use Rollerworks\Component\Datagrid\Exception\LogicException;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DatagridRenderer
{
    const CACHE_KEY_VAR = 'cache_key';

    /**
     * @var array
     */
    private $blockNameHierarchyMap = [];

    /**
     * @var array
     */
    private $hierarchyLevelMap = [];

    /**
     * @var array
     */
    private $variableStack = [];

    /**
     * @var AbstractRendererEngine
     */
    protected $engine;

    /**
     * Constructor.
     *
     * @param AbstractRendererEngine $engine
     */
    public function __construct(AbstractRendererEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * {@inheritdoc}
     */
    public function setTheme(BaseView $view, $themes)
    {
        $this->engine->setTheme($view, $themes);
    }

    /**
     * @param BaseView $view
     * @param string   $blockNameSuffix
     * @param array    $variables
     *
     * @return string
     */
    public function searchAndRenderBlock(BaseView $view, string $blockNameSuffix, array $variables = [])
    {
        $viewCacheKey = $view->vars[self::CACHE_KEY_VAR];
        $viewAndSuffixCacheKey = $viewCacheKey.$blockNameSuffix;

        // In templates, we have to deal with two kinds of block hierarchies:
        //
        //   +---------+          +---------+
        //   | Theme B | -------> | Theme A |
        //   +---------+          +---------+
        //
        //   column_cell -------> column_cell
        //       ^
        //       |
        //   text_cell -----> text_cell
        //
        // The first kind of hierarchy is the theme hierarchy. This allows to
        // override the block "text_cell" from Theme A in the extending
        // Theme B. This kind of inheritance needs to be supported by the
        // template engine and, for example, offers "parent()" or similar
        // functions to fall back from the custom to the parent implementation.
        //
        // The second kind of hierarchy is the column type hierarchy. This allows
        // to implement a custom "text_cell" block (no matter in which theme),
        // or to fallback to the block of the parent type, which would be
        // "column_cell" in this example (again, no matter in which theme).
        // If the designers wants to explicitly fallback to "column_cell" in the
        // custom "text_cell", for example because they only wants to wrap
        // a <div> around the original implementation, they can simply call the
        // widget() function again to render the block for the parent type.
        //
        // The second kind is implemented in the following blocks.
        if (!isset($this->blockNameHierarchyMap[$viewAndSuffixCacheKey])) {
            // INITIAL CALL
            // Calculate the hierarchy of template blocks and start on
            // the bottom level of the hierarchy (= "_<id>_<section>" block)
            $blockNameHierarchy = [];

            foreach ($view->vars['block_prefixes'] as $blockNamePrefix) {
                $blockNameHierarchy[] = $blockNamePrefix.'_'.$blockNameSuffix;
            }

            $hierarchyLevel = count($blockNameHierarchy) - 1;
            $hierarchyInit = true;
        } else {
            // RECURSIVE CALL
            // If a block recursively calls searchAndRenderBlock() again, resume rendering
            // using the parent type in the hierarchy.
            $blockNameHierarchy = $this->blockNameHierarchyMap[$viewAndSuffixCacheKey];
            $hierarchyLevel = $this->hierarchyLevelMap[$viewAndSuffixCacheKey] - 1;

            $hierarchyInit = false;
        }

        // The variables are cached globally for a view (instead of for the
        // current suffix)
        if (!isset($this->variableStack[$viewCacheKey])) {
            $this->variableStack[$viewCacheKey] = [];

            // The default variable scope contains all view variables, merged with
            // the variables passed explicitly to the helper
            $scopeVariables = $view->vars;

            $varInit = true;
        } else {
            // Reuse the current scope and merge it with the explicitly passed variables
            $scopeVariables = end($this->variableStack[$viewCacheKey]);

            $varInit = false;
        }

        // Load the resource where this block can be found
        $resource = $this->engine->getResourceForBlockNameHierarchy($view, $blockNameHierarchy, $hierarchyLevel);

        // Update the current hierarchy level to the one at which the resource was
        // found. For example, if looking for "text_cell", but only a resource
        // is found for its parent "column_cell", then the level is updated here
        // to the parent level.
        $hierarchyLevel = $this->engine->getResourceHierarchyLevel($view, $blockNameHierarchy, $hierarchyLevel);

        // The actually existing block name in $resource
        $blockName = $blockNameHierarchy[$hierarchyLevel];

        // Escape if no resource exists for this block
        if (!$resource) {
            if (count($blockNameHierarchy) !== count(array_unique($blockNameHierarchy))) {
                throw new LogicException(
                    sprintf(
                        'Unable to render the %s because the block names array contains duplicates: "%s".',
                        $blockNameSuffix,
                        implode('", "', array_reverse($blockNameHierarchy))
                    )
                );
            }

            throw new LogicException(
                sprintf(
                    'Unable to render the %s as none of the following blocks exist: "%s".',
                    $blockNameSuffix,
                    implode('", "', array_reverse($blockNameHierarchy))
                )
            );
        }

        // Merge the passed with the existing attributes
        if (isset($variables['attr'], $scopeVariables['attr'])) {
            $variables['attr'] = array_replace($scopeVariables['attr'], $variables['attr']);
        }

        // Merge the passed with the exist *label* attributes
        if (isset($variables['label_attr'], $scopeVariables['label_attr'])) {
            $variables['label_attr'] = array_replace($scopeVariables['label_attr'], $variables['label_attr']);
        }

        // Merge the passed with the exist *header* attributes
        if (isset($variables['header_attr'], $scopeVariables['header_attr'])) {
            $variables['header_attr'] = array_replace($scopeVariables['header_attr'], $variables['header_attr']);
        }

        // Merge the passed with the exist *cell* attributes
        if (isset($variables['cell_attr'], $scopeVariables['cell_attr'])) {
            $variables['cell_attr'] = array_replace($scopeVariables['cell_attr'], $variables['cell_attr']);
        }

        // Do not use array_replace_recursive(), otherwise array variables
        // cannot be overwritten
        $variables = array_replace($scopeVariables, $variables);

        // In order to make recursive calls possible, we need to store the block hierarchy,
        // the current level of the hierarchy and the variables so that this method can
        // resume rendering one level higher of the hierarchy when it is called recursively.
        //
        // We need to store these values in maps (associative arrays) because within a
        // call to widget() another call to widget() can be made, but for a different view
        // object. These nested calls should not override each other.
        $this->blockNameHierarchyMap[$viewAndSuffixCacheKey] = $blockNameHierarchy;
        $this->hierarchyLevelMap[$viewAndSuffixCacheKey] = $hierarchyLevel;

        // We also need to store the variables for the view so that we can render other
        // blocks for the same view using the same variables as in the outer block.
        $this->variableStack[$viewCacheKey][] = $variables;

        // Don't optimize these to a protected method, this method will be called at least
        // 5000 times for a grid with 500 rows and it's headers!
        $vars = [
            'vars' => $variables,
            'name' => $view->name ?? null,
        ];

        if ($view instanceof DatagridView) {
            $vars['columns'] = $view->columns;
            $vars['rows'] = $view->getIterator();
        } elseif ($view instanceof HeaderView) {
            $vars['label'] = $variables['label'] ?? $view->label;
            $vars['datagrid'] = $view->datagrid;
        } elseif ($view instanceof CellView) {
            $vars['datagrid'] = $view->datagrid;
            $vars['column'] = $view->column;
            $vars['use_raw'] = $view->useRaw;
            $vars['value'] = $view->value;
            $vars['source'] = $view->source;
        }

        // Do the rendering
        $html = $this->engine->renderBlock($view, $resource, $blockName, $vars);

        // Clear the stack
        array_pop($this->variableStack[$viewCacheKey]);

        // Clear the caches if they were filled for the first time within
        // this function call
        if ($hierarchyInit) {
            unset($this->blockNameHierarchyMap[$viewAndSuffixCacheKey], $this->hierarchyLevelMap[$viewAndSuffixCacheKey]);
        }

        if ($varInit) {
            unset($this->variableStack[$viewCacheKey]);
        }

        return $html;
    }
}
