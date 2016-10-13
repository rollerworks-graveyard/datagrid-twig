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

namespace Rollerworks\Component\Datagrid\Twig\Tests\Extension;

use Rollerworks\Component\Datagrid\Extension\Core\Type\ActionType;
use Rollerworks\Component\Datagrid\Extension\Core\Type\TextType;
use Rollerworks\Component\Datagrid\Test\DatagridIntegrationTestCase;
use Rollerworks\Component\Datagrid\Twig\Extension\DatagridExtension;
use Rollerworks\Component\Datagrid\Twig\Renderer\TwigRenderer;
use Rollerworks\Component\Datagrid\Twig\Renderer\TwigRendererEngine;

class DatagridExtensionTest extends DatagridIntegrationTestCase
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /** @var TwigRenderer */
    private $renderer;

    protected function setUp()
    {
        parent::setUp();

        $loader = new \Twig_Loader_Filesystem(
            [
                __DIR__.'/../../Resources/theme', // datagrid base theme
                __DIR__.'/../Resources/views', // templates used in tests
            ]
        );

        $environment = new \Twig_Environment($loader, ['debug' => true, 'strict_variables' => true]);
        $environment->addGlobal('global_var', 'global_value');

        $environment->addExtension(new DatagridExtension());

        $rendererEngine = new TwigRendererEngine($environment, ['datagrid.html.twig']);
        $this->renderer = new TwigRenderer($rendererEngine);

        $loader = $this->prophesize(\Twig_RuntimeLoaderInterface::class);
        $loader->load(TwigRenderer::class)->willReturn($this->renderer);

        $environment->addRuntimeLoader($loader->reveal());

        $this->twig = $environment;
    }

    private function createDatagrid($name)
    {
        $datagrid = $this->factory->createDatagridBuilder();
        $datagrid->add('title', TextType::class, ['label' => 'Title']);
        $datagrid->add('actions', ActionType::class, ['url' => '/delete']);

        return $datagrid->getDatagrid($name);
    }

    public function testAttributesRendering()
    {
        $extension = new DatagridExtension();

        self::assertEquals('', $extension->datagridAttributes([]));
        self::assertEquals(' id="1"', $extension->datagridAttributes(['id' => 1]));
        self::assertEquals(' id="1" foo="bar"', $extension->datagridAttributes(['id' => 1, 'foo' => 'bar']));
    }

    public function testRenderEmptyDatagridWidget()
    {
        $datagrid = $this->createDatagrid('grid');
        $datagrid->setData([]);

        $datagridWithTheme = $this->createDatagrid('grid_with_theme');
        $datagridWithTheme->setData([]);

        $datagridWithThemeView = $datagridWithTheme->createView();

        $html = $this->twig->render(
            'datagrid/datagrid_widget_test.html.twig',
            [
                'datagrid' => $datagrid->createView(),
                'datagrid_with_theme' => $datagridWithThemeView,
            ]
        );

        $this->assertHtmlEquals('datagrid_widget_empty.html', $html);
    }

    public function testRenderDatagridWidgetWithData()
    {
        $datagrid = $this->createDatagrid('grid');
        $datagrid->setData(
            [
                ['title' => 'This is value 1'],
            ]
        );

        $datagridWithTheme = $this->createDatagrid('grid_with_theme');
        $datagridWithTheme->setData(
            [
                ['title' => 'This is value 2'],
            ]
        );

        $html = $this->twig->render(
            'datagrid/datagrid_widget_test.html.twig',
            [
                'datagrid' => $datagrid->createView(),
                'datagrid_with_theme' => $datagridWithTheme->createView(),
            ]
        );

        $this->assertHtmlEquals('datagrid_widget_result.html', $html);
    }

    public function testRenderColumnHeaderWidget()
    {
        $datagrid = $this->createDatagrid('grid');
        $datagrid->setData([['title' => 'This is value 1']]);

        $datagridWithTheme = $this->createDatagrid('grid_with_header_theme');
        $datagridWithTheme->setData([['title' => 'This is value 2']]);

        $datagridView = $datagrid->createView();
        $datagridWithThemeView = $datagridWithTheme->createView();

        $html = $this->twig->render(
            'datagrid/header_widget_test.html.twig',
            [
                'grid_with_header_theme' => $datagridWithThemeView,
                'header' => $datagridView->getColumn('title'),
                'header_with_theme' => $datagridWithThemeView->getColumn('title'),
            ]
        );

        $this->assertHtmlEquals('datagrid_header_widget_result.html', $html);
    }

    public function testRenderCellWidget()
    {
        $datagrid = $this->createDatagrid('grid');
        $datagrid->setData([['title' => 'This is value 1']]);

        $datagridWithTheme = $this->createDatagrid('grid_with_header_theme');
        $datagridWithTheme->setData([['title' => 'This is value 2']]);

        $datagridView = $datagrid->createView();
        $datagridWithThemeView = $datagridWithTheme->createView();

        $html = $this->twig->render(
            'datagrid/cell_widget_test.html.twig',
            [
                'grid_with_header_theme' => $datagridWithThemeView,
                'cell' => $datagridView->rows[0]->cells['title'],
                'cell_with_theme' => $datagridWithThemeView->rows[0]->cells['title'],
            ]
        );

        $this->assertHtmlEquals('datagrid_cell_widget_result.html', $html);
    }

    public function testRenderCompoundCellWidget()
    {
        $datagrid = $this->factory->createDatagridBuilder();
        $datagrid->add('title', TextType::class, ['label' => 'Title']);
        $datagrid->createCompound('actions', ['label' => 'Actions'])
            ->add('modify', ActionType::class, [
                'content' => 'Action modify',
                'uri_scheme' => 'entity/{id}/modify',
                'data_provider' => function ($data) {
                    return ['id' => $data['id']];
                },
            ])
            ->end()
        ;

        $datagrid = $datagrid->getDatagrid('datagrid');
        $datagrid->setData(
            [
                ['id' => 1, 'title' => 'This is value 1'],
            ]
        );

        $datagridWithTheme = $this->factory->createDatagridBuilder();
        $datagridWithTheme->add('title', TextType::class, ['label' => 'Title']);
        $datagridWithTheme->createCompound('actions', ['label' => 'Actions'])
            ->add('modify', ActionType::class, [
                'content' => 'Modify',
                'uri_scheme' => 'entity/{id}/modify2',
                'data_provider' => function ($data) {
                    return ['id' => $data['id']];
                },
            ])
            ->add('view', ActionType::class, [
                'content' => 'View',
                'uri_scheme' => 'entity/{id}',
                'data_provider' => function ($data) {
                    return ['id' => $data['id']];
                },
            ])
            ->end()
        ;

        $datagridWithTheme = $datagridWithTheme->getDatagrid('datagrid_with_header_theme');
        $datagridWithTheme->setData(
            [
                ['id' => 2, 'title' => 'This is value 2'],
            ]
        );

        $datagridView = $datagrid->createView();
        $datagridWithThemeView = $datagridWithTheme->createView();

        $cellView = $datagridView->rows[0]->cells['actions'];
        $cellWithThemeView = $datagridWithThemeView->rows[0]->cells['actions'];

        $html = $this->twig->render(
            'datagrid/cell_widget_test.html.twig',
            [
                'grid_with_header_theme' => $datagridWithThemeView,
                'cell' => $cellView,
                'cell_with_theme' => $cellWithThemeView,
            ]
        );

        $this->assertHtmlEquals('compound_column_cell_widget_result.html', $html);
    }

    private function assertHtmlEquals($expected, $outputHtml)
    {
        $expected = __DIR__.'/../Resources/views/expected/datagrid/'.$expected;

        self::assertFileExists($expected);
        self::assertSame(
            $this->normalizeWhitespace(file_get_contents($expected)),
            $this->normalizeWhitespace($outputHtml)
        );
    }

    private function normalizeWhitespace($value)
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        return $value;
    }
}
