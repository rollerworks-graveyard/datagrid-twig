<?php

/*
 * This file is part of the RollerworksDatagrid package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Datagrid\Tests\Twig\Extension;

use Rollerworks\Component\Datagrid\Test\DatagridIntegrationTestCase;
use Rollerworks\Component\Datagrid\Twig\Extension\DatagridExtension;

class DatagridExtensionTest extends DatagridIntegrationTestCase
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var DatagridExtension
     */
    private $extension;

    protected function setUp()
    {
        parent::setUp();

        $loader = new \Twig_Loader_Filesystem(
            [
                __DIR__.'/../../Resources/theme', // datagrid base theme
                __DIR__.'/../Resources/views', // templates used in tests
            ]
        );

        $cacheDir = sys_get_temp_dir().'/twig'.microtime(false);

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir);
        }

        $twig = new \Twig_Environment($loader, ['debug' => true, 'cache' => $cacheDir]);
        $twig->addGlobal('global_var', 'global_value');

        $this->twig = $twig;
        $this->extension = new DatagridExtension('datagrid.html.twig');

        $this->twig->addExtension($this->extension);
        $this->twig->initRuntime();
    }

    public function testRenderEmptyDatagridWidget()
    {
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->addColumn($this->factory->createColumn('title', 'text', $datagrid, ['label' => 'Title', 'field_mapping' => ['title']]));
        $datagrid->setData([]);

        $datagridWithTheme = $this->factory->createDatagrid('grid_with_theme');
        $datagridWithTheme->addColumn($this->factory->createColumn('title', 'text', $datagridWithTheme, ['label' => 'Title', 'field_mapping' => ['title']]));
        $datagridWithTheme->setData([]);

        $html = $this->twig->render(
            'datagrid/datagrid_widget_test.html.twig',
            [
                'datagrid' => $datagrid->createView(),
                'datagrid_with_theme' => $datagridWithTheme->createView(),
            ]
        );

        $this->assertHtmlEquals('datagrid_widget_empty.html', $html);
    }

    public function testRenderDatagridWidgetWithData()
    {
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->addColumn($this->factory->createColumn('title', 'text', $datagrid, ['label' => 'Title', 'field_mapping' => ['[title]']]));
        $datagrid->setData(
            [
                ['title' => 'This is value 1'],
            ]
        );

        $datagridWithTheme = $this->factory->createDatagrid('grid_with_theme');
        $datagridWithTheme->addColumn($this->factory->createColumn('title', 'text', $datagridWithTheme, ['label' => 'Title', 'field_mapping' => ['[title]']]));
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
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->addColumn($this->factory->createColumn('title', 'text', $datagrid, ['label' => 'Title', 'field_mapping' => ['[title]']]));
        $datagrid->setData([['title' => 'This is value 1']]);

        $datagridWithTheme = $this->factory->createDatagrid('grid_with_header_theme');
        $datagridWithTheme->addColumn($this->factory->createColumn('title', 'text', $datagridWithTheme, ['label' => 'Title', 'field_mapping' => ['[title]']]));
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
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->addColumn($this->factory->createColumn('title', 'text', $datagrid, ['label' => 'Title', 'field_mapping' => ['[title]']]));
        $datagrid->setData([['title' => 'This is value 1']]);

        $datagridWithTheme = $this->factory->createDatagrid('grid_with_header_theme');
        $datagridWithTheme->addColumn($this->factory->createColumn('title', 'text', $datagridWithTheme, ['label' => 'Title', 'field_mapping' => ['[title]']]));
        $datagridWithTheme->setData([['title' => 'This is value 2']]);

        $datagridView = $datagrid->createView();
        $datagridWithThemeView = $datagridWithTheme->createView();

        $html = $this->twig->render(
            'datagrid/cell_widget_test.html.twig',
            [
                'grid_with_header_theme' => $datagridWithThemeView,
                'cell' => $datagridView[0]['title'],
                'cell_with_theme' => $datagridWithThemeView[0]['title'],
            ]
        );

        $this->assertHtmlEquals('datagrid_cell_widget_result.html', $html);
    }

    public function testRenderCompoundCellWidget()
    {
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->addColumn($this->factory->createColumn('title', 'text', $datagrid, ['label' => 'Title', 'field_mapping' => ['title' => '[title]']]));
        $datagrid->addColumn(
            $this->factory->createColumn(
                'actions',
                'compound_column',
                $datagrid,
                [
                    'label' => 'Actions',
                    'columns' => [
                        'modify' => $this->factory->createColumn(
                            'action_modify',
                            'action',
                            $datagrid,
                            [
                                'label' => 'Modify',
                                'field_mapping' => ['id' => '[id]'],
                                'uri_scheme' => 'entity/{id}/modify',
                            ]
                        )
                    ],
                ]
            )
        );

        $datagrid->setData(
            [
                ['id' => 1, 'title' => 'This is value 1'],
            ]
        );

        $datagridWithTheme = $this->factory->createDatagrid('grid_with_header_theme');
        $datagridWithTheme->addColumn($this->factory->createColumn('title', 'text', $datagridWithTheme, ['label' => 'Title', 'field_mapping' => ['[title]']]));
        $datagridWithTheme->addColumn(
            $this->factory->createColumn(
                'actions',
                'compound_column',
                $datagrid,
                [
                    'label' => 'Actions',
                    'columns' => [
                        'modify' => $this->factory->createColumn(
                            'action_modify',
                            'action',
                            $datagridWithTheme,
                            [
                                'content' => 'Modify',
                                'field_mapping' => ['id' => '[id]'],
                                'uri_scheme' => 'entity/{id}/',
                            ]
                        ),
                        'view' => $this->factory->createColumn(
                            'action_view',
                            'action',
                            $datagridWithTheme,
                            [
                                'content' => 'View',
                                'field_mapping' => ['id' => '[id]'],
                                'uri_scheme' => 'entity/{id}/',
                            ]
                        )
                    ],
                ]
            )
        );

        $datagridWithTheme->setData(
            [
                ['id' => 2, 'title' => 'This is value 2'],
            ]
        );

        $datagridView = $datagrid->createView();
        $datagridWithThemeView = $datagridWithTheme->createView();

        $cellView = $datagridView[0]['actions'];
        $cellWithThemeView = $datagridWithThemeView[0]['actions'];

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

    public function testDatagridRenderBlock()
    {
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->addColumn($this->factory->createColumn('title', 'text', $datagrid, ['label' => 'Title', 'field_mapping' => ['[title]']]));
        $datagrid->setData([['title' => 'This is value 1']]);

        $view = $datagrid->createView();

        $template = $this->prophesize('\Twig_Template');
        $template->getParent([])->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_grid')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid')->willReturn(true)->shouldBeCalled();
        $template->displayBlock(
            'datagrid',
            [
                'datagrid' => $view,
                'vars' => [],
                'global_var' => 'global_value',
            ]
        )->willReturn(true);

        $this->extension->setBaseTheme($template->reveal());
        $this->extension->datagrid($view);
    }

    public function testDatagridMultipleTemplates()
    {
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->addColumn($this->factory->createColumn('title', 'text', $datagrid, ['label' => 'Title', 'field_mapping' => ['[title]']]));
        $datagrid->setData([['title' => 'This is value 1']]);

        $view = $datagrid->createView();

        $template1 = $this->prophesize('\Twig_Template');
        $template1->getParent([])->willReturn(false)->shouldBeCalled();
        $template1->hasBlock('datagrid_grid')->willReturn(false)->shouldBeCalled();
        $template1->hasBlock('datagrid')->willReturn(true)->shouldBeCalled();
        $template1->displayBlock(
            'datagrid',
            [
                'datagrid' => $view,
                'vars' => [],
                'global_var' => 'global_value',
            ]
        )->willReturn(true);

        $template2 = $this->prophesize('\Twig_Template');
        $template2->getParent([])->willReturn(false)->shouldBeCalled();
        $template2->hasBlock('datagrid_grid')->willReturn(false)->shouldBeCalled();
        $template2->hasBlock('datagrid')->willReturn(false)->shouldBeCalled();
        $template2->displayBlock(
            'datagrid',
            [
                'datagrid' => $view,
                'vars' => [],
                'global_var' => 'global_value',
            ]
        )->willReturn(true);

        $this->extension->setBaseTheme([$template1->reveal(), $template2->reveal()]);
        $this->extension->datagrid($view);
    }

    public function testDatagridRenderBlockFromParent()
    {
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->addColumn($this->factory->createColumn('title', 'text', $datagrid, ['label' => 'Title', 'field_mapping' => ['[title]']]));
        $datagrid->setData([['title' => 'This is value 1']]);

        $view = $datagrid->createView();

        $parent = $this->prophesize('\Twig_Template');
        $parent->getParent([])->willReturn(false)->shouldBeCalled();
        $parent->hasBlock('datagrid_grid')->willReturn(false)->shouldBeCalled();
        $parent->hasBlock('datagrid')->willReturn(true)->shouldBeCalled();

        $template = $this->prophesize('\Twig_Template');
        $template->getParent([])->willReturn($parent);
        $template->hasBlock('datagrid_grid')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid')->willReturn(false)->shouldBeCalled();

        // call the display block on this template (not the parent),
        // Twig will call the parent block itself.
        $template->displayBlock(
            'datagrid',
            [
                'datagrid' => $view,
                'vars' => [],
                'global_var' => 'global_value',
            ]
        )->willReturn(true);

        $this->extension->setBaseTheme($template->reveal());
        $this->extension->datagrid($view);
    }

    public function testDatagridHeaderRenderBlock()
    {
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->setData([['title' => 'This is value 1']]);

        $view = $datagrid->createView();

        $template = $this->prophesize('\Twig_Template');
        $template->getParent([])->willReturn(false);
        $template->hasBlock('datagrid_grid_header')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_header')->willReturn(true)->shouldBeCalled();
        $template->displayBlock(
            'datagrid_header',
            [
                'headers' => [],
                'vars' => [],
                'global_var' => 'global_value',
            ]
        )->willReturn(true);

        $this->extension->setBaseTheme($template->reveal());
        $this->extension->datagridHeader($view);
    }

    public function testDatagridColumnHeaderRenderBlock()
    {
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->addColumn($this->factory->createColumn('title', 'text', $datagrid, ['label' => 'Title', 'field_mapping' => ['[title]']]));
        $datagrid->setData([['title' => 'This is value 1']]);

        $view = $datagrid->createView();
        $headerView = $view->getColumn('title');

        $template = $this->prophesize('\Twig_Template');
        $template->getParent([])->willReturn(false);
        $template->hasBlock('datagrid_grid_column_name_title_header')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_grid_column_type_text_header')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_column_name_title_header')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_column_type_text_header')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_grid_column_header')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_column_header')->willReturn(true)->shouldBeCalled();
        $template->displayBlock(
            'datagrid_column_header',
            [
                'header' => $headerView,
                'translation_domain' => null,
                'vars' => [],
                'global_var' => 'global_value',
            ]
        )->willReturn(true);

        $this->extension->setBaseTheme($template->reveal());
        $this->extension->datagridColumnHeader($headerView);
    }

    public function testDatagridRowsetRenderBlock()
    {
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->addColumn($this->factory->createColumn('title', 'text', $datagrid, ['label' => 'Title', 'field_mapping' => ['[title]']]));
        $datagrid->setData([['title' => 'This is value 1']]);

        $view = $datagrid->createView();

        $template = $this->prophesize('\Twig_Template');
        $template->getParent([])->willReturn(false);
        $template->hasBlock('datagrid_grid_rowset')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_rowset')->willReturn(true)->shouldBeCalled();
        $template->displayBlock(
            'datagrid_rowset',
            [
                'datagrid' => $view,
                'vars' => [],
                'global_var' => 'global_value',
            ]
        )->willReturn(true);

        $this->extension->setBaseTheme($template->reveal());
        $this->extension->datagridRowset($view);
    }

    public function testDatagridColumnCellRenderBlock()
    {
        $datagrid = $this->factory->createDatagrid('grid');
        $datagrid->addColumn($this->factory->createColumn('title', 'text', $datagrid, ['label' => 'Title', 'field_mapping' => ['[title]']]));
        $datagrid->setData([['title' => 'This is value 1']]);

        $view = $datagrid->createView();
        $cellView = $view[0]['title'];

        $template = $this->prophesize('\Twig_Template');
        $template->getParent([])->willReturn(false);
        $template->hasBlock('datagrid_grid_column_name_title_cell')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_grid_column_type_text_cell')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_column_name_title_cell')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_column_type_text_cell')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_grid_column_cell')->willReturn(false)->shouldBeCalled();
        $template->hasBlock('datagrid_column_cell')->willReturn(true)->shouldBeCalled();
        $template->displayBlock(
            'datagrid_column_cell',
            [
                'cell' => $cellView,
                'row_index' => 0,
                'datagrid_name' => 'grid',
                'translation_domain' => null,
                'vars' => ['row' => 0],
                'global_var' => 'global_value',
            ]
        )->willReturn(true);

        $this->extension->setBaseTheme($template->reveal());
        $this->extension->datagridColumnCell($cellView);
    }

    private function assertHtmlEquals($expected, $outputHtml)
    {
        $expected = __DIR__.'/../Resources/views/expected/datagrid/'.$expected;

        $this->assertFileExists($expected);
        $this->assertSame(
            trim(str_replace(["\r\n", "\r"], "\n", file_get_contents($expected))),
            trim(str_replace(["\r\n", "\r"], "\n", $outputHtml))
        );
    }
}
