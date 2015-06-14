<?php

/*
 * This file is part of the RollerworksDatagrid package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Datagrid\Twig\Node;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Norbert Orzechowicz <norbert@fsi.pl>
 */
class DatagridThemeNode extends \Twig_Node
{
    public function __construct(
        \Twig_NodeInterface $datagrid,
        \Twig_NodeInterface $theme,
        \Twig_Node_Expression_Array $vars,
        $lineno,
        $tag = null
    ) {
        parent::__construct(['datagrid' => $datagrid, 'theme' => $theme, 'vars' => $vars], [], $lineno, $tag);
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
            ->write('$this->env->getExtension(\'rollerworks_datagrid\')->setTheme(')
            ->subcompile($this->getNode('datagrid'))
            ->raw(', ')
            ->subcompile($this->getNode('theme'))
            ->raw(', ')
            ->subcompile($this->getNode('vars'))
            ->raw(");\n")
        ;
    }
}
