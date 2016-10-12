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

namespace Rollerworks\Component\Datagrid\Tests\Twig\Extension;

use Rollerworks\Component\Datagrid\Extension\Core\Type\ActionType;
use Rollerworks\Component\Datagrid\Extension\Core\Type\DateTimeType;
use Rollerworks\Component\Datagrid\Extension\Core\Type\NumberType;
use Rollerworks\Component\Datagrid\Extension\Core\Type\TextType;
use Rollerworks\Component\Datagrid\Test\DatagridPerformanceTestCase;
use Rollerworks\Component\Datagrid\Twig\Extension\DatagridExtension;
use Rollerworks\Component\Datagrid\Twig\Renderer\TwigRenderer;
use Rollerworks\Component\Datagrid\Twig\Renderer\TwigRendererEngine;

class DatagridExtensionPerformanceTest extends DatagridPerformanceTestCase
{
    /** @var TwigRenderer */
    private $renderer;

    protected function setUp()
    {
        parent::setUp();

        $loader = new \Twig_Loader_Filesystem([
                __DIR__.'/../../Resources/theme', // datagrid base theme
        ]);

        $cacheDir = __DIR__.'/twig-perf';

        if (!file_exists($cacheDir)) {
            @mkdir($cacheDir);
        }

        $environment = new \Twig_Environment($loader, ['debug' => true, 'strict_variables' => true, 'cache' => $cacheDir]);
        $environment->addExtension(new DatagridExtension());

        $rendererEngine = new TwigRendererEngine($environment, ['datagrid.html.twig']);
        $this->renderer = new TwigRenderer($rendererEngine);

        $loader = $this->prophesize(\Twig_RuntimeLoaderInterface::class);
        $loader->load(TwigRenderer::class)->willReturn($this->renderer);

        $environment->addRuntimeLoader($loader->reveal());

        // load the theme template to ensure a compile, we are only interested in the rendering speed
        $environment->loadTemplate('datagrid.html.twig');
    }

    /**
     * This test case is realistic in collection rows where each
     * row contains the same data.
     *
     * This is most common use-case, showing more rows
     * on the page is likely to hit memory limits (for the data set itself).
     * And more rows will give display problems.
     *
     * @group benchmark
     */
    public function testGenerateViewWith100RowsAnd10Columns()
    {
        $this->setMaxRunningTime(1);

        $datagrid = $this->factory->createDatagridBuilder();

        $datagrid->add('id', NumberType::class, ['data_provider' => function ($data) {
            return $data['id'];
        }]);
        $datagrid->add('name', TextType::class, ['data_provider' => function ($data) {
            return $data['name'];
        }]);
        $datagrid->add('email', TextType::class, ['data_provider' => function ($data) {
            return $data['email'];
        }]);
        $datagrid->add('regdate', DateTimeType::class, ['data_provider' => function ($data) {
            return $data['regdate'];
        }]);
        $datagrid->add('lastModified', DateTimeType::class, ['data_provider' => function ($data) {
            return $data['lastModified'];
        }]);
        $datagrid->add(
            'status',
            TextType::class,
            [
                'label' => 'last_modified',
                'data_provider' => function ($data) {
                    return $data['lastModified'];
                },
                'value_format' => function ($value) {
                    return $value === 1 ? 'active' : 'deactivated';
                },
            ]
        );
        $datagrid->add('group', TextType::class);

        $datagrid
            ->createCompound(
                    'actions',
                    [
                        'label' => 'Actions',
                        'data_provider' => function ($data) {
                            return ['id' => $data['id']];
                        },
                    ]
                )
                ->add(
                    'modify',
                    ActionType::class,
                    [
                        'label' => 'Modify',
                        'uri_scheme' => 'entity/{id}/modify',
                    ]
                )
                ->add(
                    'delete',
                    ActionType::class,
                    [
                        'label' => 'Delete',
                        'data_provider' => function ($data) {
                            return ['id' => $data['id']];
                        },
                        'uri_scheme' => 'entity/{id}/delete',
                    ]
                )
            ->end()
        ;

        $data = [];

        for ($i = 0; $i < 100; ++$i) {
            $data[] = [
                'id' => $i,
                'name' => 'Who',
                'email' => 'me@example.com',
                'regdate' => new \DateTime(),
                'lastModified' => new \DateTime(),
                'status' => mt_rand(0, 1),
                'group' => 'Default',
            ];
        }

        $datagrid = $datagrid->getDatagrid('tests');
        $datagrid->setData($data);

        $view = $datagrid->createView();

        $this->renderer->searchAndRenderBlock($view, 'container');

        self::assertTrue(true);
    }
}
