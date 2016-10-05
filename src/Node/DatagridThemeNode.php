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

namespace Rollerworks\Component\Datagrid\Twig\Node;

use Rollerworks\Component\Datagrid\Twig\Renderer\TwigRenderer;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class DatagridThemeNode extends \Twig_Node
{
    public function __construct(
        \Twig_Node $view,
        \Twig_Node $theme,
        $lineno,
        $tag = null
    ) {
        parent::__construct(['view' => $view, 'resources' => $theme], [], $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$this->env->getRuntime(\''.TwigRenderer::class.'\')->setTheme(')
            ->subcompile($this->getNode('view'))
            ->raw(', ')
            ->subcompile($this->getNode('resources'))
            ->raw(");\n")
        ;
    }
}
