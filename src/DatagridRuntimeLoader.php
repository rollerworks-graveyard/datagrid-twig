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

namespace Rollerworks\Component\Datagrid\Twig;

use Rollerworks\Component\Datagrid\Twig\Renderer\TwigRendererEngine;

/**
 * Twig RuntimeLoader for the DatagridExtension.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class DatagridRuntimeLoader implements \Twig_RuntimeLoaderInterface
{
    /**
     * @var TwigRendererEngine
     */
    private $rendererEngine;

    public function __construct(TwigRendererEngine $rendererEngine)
    {
        $this->rendererEngine = $rendererEngine;
    }

    public function load($class)
    {
        if (Renderer\TwigRenderer::class === $class) {
            return new Renderer\TwigRenderer($this->rendererEngine);
        }
    }
}
