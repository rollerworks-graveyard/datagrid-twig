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

use Rollerworks\Component\Datagrid\Test\DatagridPerformanceTestCase;
use Rollerworks\Component\Datagrid\Twig\Extension\DatagridExtension;

class DatagridExtensionPerformanceTest extends DatagridPerformanceTestCase
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
            ]
        );

        $cacheDir = sys_get_temp_dir().'/twig-perf';

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir);
        }

        $this->twig = new \Twig_Environment($loader, ['debug' => true, 'cache' => $cacheDir]);
        $this->extension = new DatagridExtension('datagrid.html.twig');

        $this->twig->addExtension($this->extension);
        $this->twig->initRuntime();

        // load template to ensure a compile, we are only interested in the rendering speed
        $this->twig->loadTemplate('datagrid.html.twig');
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

        $datagrid = $this->factory->createDatagrid('test');

        $datagrid->addColumn($this->factory->createColumn('id', 'number', $datagrid, ['label' => '#', 'field_mapping' => ['[id]']]));
        $datagrid->addColumn($this->factory->createColumn('name', 'text', $datagrid, ['label' => 'Name', 'field_mapping' => ['[name]']]));
        $datagrid->addColumn($this->factory->createColumn('email', 'text', $datagrid, ['label' => 'Email', 'field_mapping' => ['[email]']]));
        $datagrid->addColumn($this->factory->createColumn('regdate', 'datetime', $datagrid, ['label' => 'regdate', 'field_mapping' => ['[regdate]']]));
        $datagrid->addColumn($this->factory->createColumn('last_modified', 'datetime', $datagrid, ['label' => 'last_modified', 'field_mapping' => ['[lastModified]']]));
        $datagrid->addColumn(
            $this->factory->createColumn(
                'status',
                'text',
                $datagrid,
                [
                    'label' => 'status',
                    'field_mapping' => ['[status]'],
                    'value_format' => function ($value) {
                        return $value === 1 ? 'active' : 'deactivated';
                    }
                ]
            )
        );
        $datagrid->addColumn($this->factory->createColumn('group', 'text', $datagrid, ['label' => 'group', 'field_mapping' => ['[group]']]));
        $datagrid->addColumn(
            $this->factory->createColumn(
                'actions',
                'action',
                $datagrid,
                [
                    'label' => 'actions',
                    'field_mapping' => ['[id]'],
                    'actions' => [
                        'modify' => [
                            'label' => 'Modify',
                            'uri_scheme' => 'entity/%d/modify',
                        ],
                        'delete' => [
                            'label' => 'Delete',
                            'uri_scheme' => 'entity/%d/delete',
                        ],
                    ]
                ]
            )
        );

        $data = [];

        for ($i = 0; $i < 100; ++$i) {
            $data[] = [
                'id' => $i,
                'name' => 'Who',
                'email' => 'me@example.com',
                'regdate' => new \DateTime(),
                'lastModified' => new \DateTime(),
                'status' => rand(0, 1),
                'group' => 'Default'
            ];
        }

        $datagrid->setData($data);

        $this->extension->datagrid($datagrid->createView());
        $this->assertTrue(true);
    }
}
