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
use Rollerworks\Component\Datagrid\Twig\Node\SearchAndRenderBlockNode;
use Rollerworks\Component\Datagrid\Twig\Renderer\TwigRenderer;

class SearchAndRenderBlockNodeTest extends TestCase
{
    public function testCompileHeader()
    {
        $arguments = new \Twig_Node([
            new \Twig_Node_Expression_Name('cell', 0),
        ]);

        $node = new SearchAndRenderBlockNode('rollerworks_datagrid_column_cell', $arguments, 0);

        $compiler = new \Twig_Compiler(new \Twig_Environment($this->createMock('Twig_LoaderInterface')));

        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->searchAndRenderBlock(%s, \'cell\')',
                $this->getVariableGetter('cell')
             ),
            trim($compiler->compile($node)->getSource())
        );
    }

    public function testCompileHeaderWithVariables()
    {
        $arguments = new \Twig_Node([
            new \Twig_Node_Expression_Name('cell', 0),
            new \Twig_Node_Expression_Array([
                new \Twig_Node_Expression_Constant('foo', 0),
                new \Twig_Node_Expression_Constant('bar', 0),
            ], 0),
        ]);

        $node = new SearchAndRenderBlockNode('rollerworks_datagrid_column_cell', $arguments, 0);

        $compiler = new \Twig_Compiler(new \Twig_Environment($this->createMock('Twig_LoaderInterface')));

        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->searchAndRenderBlock(%s, \'cell\', array("foo" => "bar"))',
                $this->getVariableGetter('cell')
            ),
            trim($compiler->compile($node)->getSource())
        );
    }

    public function testCompileHeaderWithLabel()
    {
        $arguments = new \Twig_Node([
            new \Twig_Node_Expression_Name('header', 0),
            new \Twig_Node_Expression_Constant('my label', 0),
        ]);

        $node = new SearchAndRenderBlockNode('rollerworks_datagrid_column_header', $arguments, 0);

        $compiler = new \Twig_Compiler(new \Twig_Environment($this->createMock('Twig_LoaderInterface')));

        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->searchAndRenderBlock(%s, \'header\', array("label" => "my label"))',
                $this->getVariableGetter('header')
            ),
            trim($compiler->compile($node)->getSource())
        );
    }

    public function testCompileHeaderWithNullLabel()
    {
        $arguments = new \Twig_Node([
            new \Twig_Node_Expression_Name('header', 0),
            new \Twig_Node_Expression_Constant(null, 0),
        ]);

        $node = new SearchAndRenderBlockNode('rollerworks_datagrid_column_header', $arguments, 0);

        $compiler = new \Twig_Compiler(new \Twig_Environment($this->createMock('Twig_LoaderInterface')));

        // "label" => null must not be included in the output!
        // Otherwise the default label is overwritten with null.
        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->searchAndRenderBlock(%s, \'header\')',
                $this->getVariableGetter('header')
            ),
            trim($compiler->compile($node)->getSource())
        );
    }

    public function testCompileHeaderWithEmptyStringLabel()
    {
        $arguments = new \Twig_Node([
            new \Twig_Node_Expression_Name('header', 0),
            new \Twig_Node_Expression_Constant('', 0),
        ]);

        $node = new SearchAndRenderBlockNode('rollerworks_datagrid_column_header', $arguments, 0);

        $compiler = new \Twig_Compiler(new \Twig_Environment($this->createMock('Twig_LoaderInterface')));

        // "label" => null must not be included in the output!
        // Otherwise the default label is overwritten with null.
        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->searchAndRenderBlock(%s, \'header\')',
                $this->getVariableGetter('header')
            ),
            trim($compiler->compile($node)->getSource())
        );
    }

    public function testCompileHeaderWithDefaultLabel()
    {
        $arguments = new \Twig_Node([
            new \Twig_Node_Expression_Name('header', 0),
        ]);

        $node = new SearchAndRenderBlockNode('rollerworks_datagrid_column_header', $arguments, 0);

        $compiler = new \Twig_Compiler(new \Twig_Environment($this->createMock('Twig_LoaderInterface')));

        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->searchAndRenderBlock(%s, \'header\')',
                $this->getVariableGetter('header')
            ),
            trim($compiler->compile($node)->getSource())
        );
    }

    public function testCompileHeaderWithAttributes()
    {
        $arguments = new \Twig_Node([
            new \Twig_Node_Expression_Name('header', 0),
            new \Twig_Node_Expression_Constant(null, 0),
            new \Twig_Node_Expression_Array([
                new \Twig_Node_Expression_Constant('foo', 0),
                new \Twig_Node_Expression_Constant('bar', 0),
            ], 0),
        ]);

        $node = new SearchAndRenderBlockNode('rollerworks_datagrid_column_header', $arguments, 0);

        $compiler = new \Twig_Compiler(new \Twig_Environment($this->createMock('Twig_LoaderInterface')));

        // "label" => null must not be included in the output!
        // Otherwise the default label is overwritten with null.
        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->searchAndRenderBlock(%s, \'header\', array("foo" => "bar"))',
                $this->getVariableGetter('header')
            ),
            trim($compiler->compile($node)->getSource())
        );
    }

    public function testCompileHeaderWithLabelAndAttributes()
    {
        $arguments = new \Twig_Node([
            new \Twig_Node_Expression_Name('header', 0),
            new \Twig_Node_Expression_Constant('value in argument', 0),
            new \Twig_Node_Expression_Array([
                new \Twig_Node_Expression_Constant('foo', 0),
                new \Twig_Node_Expression_Constant('bar', 0),
                new \Twig_Node_Expression_Constant('label', 0),
                new \Twig_Node_Expression_Constant('value in attributes', 0),
            ], 0),
        ]);

        $node = new SearchAndRenderBlockNode('rollerworks_datagrid_column_header', $arguments, 0);

        $compiler = new \Twig_Compiler(new \Twig_Environment($this->createMock('Twig_LoaderInterface')));

        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->searchAndRenderBlock(%s, \'header\', array("foo" => "bar", "label" => "value in argument"))',
                $this->getVariableGetter('header')
            ),
            trim($compiler->compile($node)->getSource())
        );
    }

    public function testCompileHeaderWithLabelThatEvaluatesToNull()
    {
        $arguments = new \Twig_Node([
            new \Twig_Node_Expression_Name('header', 0),
            new \Twig_Node_Expression_Conditional(
                // if
                new \Twig_Node_Expression_Constant(true, 0),
                // then
                new \Twig_Node_Expression_Constant(null, 0),
                // else
                new \Twig_Node_Expression_Constant(null, 0),
                0
            ),
        ]);

        $node = new SearchAndRenderBlockNode('rollerworks_datagrid_column_header', $arguments, 0);

        $compiler = new \Twig_Compiler(new \Twig_Environment($this->createMock('Twig_LoaderInterface')));

        // "label" => null must not be included in the output!
        // Otherwise the default label is overwritten with null.
        // https://github.com/symfony/symfony/issues/5029
        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->searchAndRenderBlock(%s, \'header\', (twig_test_empty($_label_ = ((true) ? (null) : (null))) ? [] : ["label" => $_label_]))',
                $this->getVariableGetter('header')
            ),
            trim($compiler->compile($node)->getSource())
        );
    }

    public function testCompileHeaderWithLabelThatEvaluatesToNullAndAttributes()
    {
        $arguments = new \Twig_Node([
            new \Twig_Node_Expression_Name('header', 0),
            new \Twig_Node_Expression_Conditional(
                // if
                new \Twig_Node_Expression_Constant(true, 0),
                // then
                new \Twig_Node_Expression_Constant(null, 0),
                // else
                new \Twig_Node_Expression_Constant(null, 0),
                0
            ),
            new \Twig_Node_Expression_Array([
                new \Twig_Node_Expression_Constant('foo', 0),
                new \Twig_Node_Expression_Constant('bar', 0),
                new \Twig_Node_Expression_Constant('label', 0),
                new \Twig_Node_Expression_Constant('value in attributes', 0),
            ], 0),
        ]);

        $node = new SearchAndRenderBlockNode('rollerworks_datagrid_column_header', $arguments, 0);

        $compiler = new \Twig_Compiler(new \Twig_Environment($this->createMock('Twig_LoaderInterface')));

        // "label" => null must not be included in the output!
        // Otherwise the default label is overwritten with null.
        self::assertEquals(
            sprintf(
                '$this->env->getRuntime(\''.TwigRenderer::class.'\')->searchAndRenderBlock(%s, \'header\', array("foo" => "bar", "label" => "value in attributes") + (twig_test_empty($_label_ = ((true) ? (null) : (null))) ? [] : ["label" => $_label_]))',
                $this->getVariableGetter('header')
            ),
            trim($compiler->compile($node)->getSource())
        );
    }

    protected function getVariableGetter($name)
    {
        return sprintf('($context["%s"] ?? null)', $name, $name);
    }
}
