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

namespace Rollerworks\Component\Datagrid\Twig\Tests\Node;

use PHPUnit\Framework\TestCase;
use Rollerworks\Component\Datagrid\Twig\Node\DatagridThemeNode;
use Rollerworks\Component\Datagrid\Twig\Renderer\TwigRenderer;

final class DatagridThemeTest extends TestCase
{
    public function testConstructor()
    {
        $datagrid = new \Twig_Node_Expression_Name('view', 0);
        $resources = new \Twig_Node([
            new \Twig_Node_Expression_Constant('tpl1', 0),
            new \Twig_Node_Expression_Constant('tpl2', 0),
        ]);

        $node = new DatagridThemeNode($datagrid, $resources, 0);

        self::assertEquals($datagrid, $node->getNode('view'));
        self::assertEquals($resources, $node->getNode('resources'));
    }

    public function testCompile()
    {
        $datagrid = new \Twig_Node_Expression_Name('view', 0);
        $resources = new \Twig_Node_Expression_Array([
            new \Twig_Node_Expression_Constant(0, 0),
            new \Twig_Node_Expression_Constant('tpl1', 0),
            new \Twig_Node_Expression_Constant(1, 0),
            new \Twig_Node_Expression_Constant('tpl2', 0),
        ], 0);

        $node = new DatagridThemeNode($datagrid, $resources, 0);

        $compiler = new \Twig_Compiler(new \Twig_Environment($this->createMock('Twig_LoaderInterface')));

        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->setTheme(%s, array(0 => "tpl1", 1 => "tpl2"));',
                $this->getVariableGetter('view')
             ),
            trim($compiler->compile($node)->getSource())
        );

        $resources = new \Twig_Node_Expression_Constant('tpl1', 0);

        $node = new DatagridThemeNode($datagrid, $resources, 0);

        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->setTheme(%s, "tpl1");',
                $this->getVariableGetter('view')
             ),
            trim($compiler->compile($node)->getSource())
        );
    }

    private function getVariableGetter($name)
    {
        return sprintf('($context["%s"] ?? null)', $name, $name);
    }
}
