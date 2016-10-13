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

namespace Rollerworks\Component\Datagrid\Twig\Extension;

use Rollerworks\Component\Datagrid\Twig\Node\SearchAndRenderBlockNode;
use Rollerworks\Component\Datagrid\Twig\TokenParser\DatagridThemeTokenParser;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Norbert Orzechowicz <norbert@fsi.pl>
 */
class DatagridExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('rollerworks_datagrid', null, ['node_class' => SearchAndRenderBlockNode::class, 'is_safe' => ['html']]),
            new \Twig_SimpleFunction('rollerworks_datagrid_column_header', null, ['node_class' => SearchAndRenderBlockNode::class, 'is_safe' => ['html']]),
            new \Twig_SimpleFunction('rollerworks_datagrid_column_cell', null, ['node_class' => SearchAndRenderBlockNode::class, 'is_safe' => ['html']]),
            new \Twig_SimpleFunction('rollerworks_datagrid_attributes', [$this, 'datagridAttributes'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('humanize', [$this, 'humanize']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new DatagridThemeTokenParser(),
        ];
    }

    /**
     * Makes a technical name human readable.
     *
     * @param string $text The text to humanize
     *
     * @return string The humanized text
     */
    public function humanize($text)
    {
        return ucfirst(trim(strtolower(preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $text))));
    }

    /**
     * Render HTML element attributes.
     *
     * @internal
     *
     * @param array $attributes
     *
     * @return string
     */
    public function datagridAttributes(array $attributes)
    {
        $result = '';

        foreach ($attributes as $attributeName => $attributeValue) {
            $result .= ' '.$attributeName.'="'.$attributeValue.'"';
        }

        return $result;
    }
}
