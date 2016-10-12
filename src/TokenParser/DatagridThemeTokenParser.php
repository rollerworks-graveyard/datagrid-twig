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

namespace Rollerworks\Component\Datagrid\Twig\TokenParser;

use Rollerworks\Component\Datagrid\Twig\Node\DatagridThemeNode;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DatagridThemeTokenParser extends \Twig_TokenParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $datagrid = $this->parser->getExpressionParser()->parseExpression();

        if ($this->parser->getStream()->test(\Twig_Token::NAME_TYPE, 'with')) {
            $this->parser->getStream()->next();
            $resources = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $resources = new \Twig_Node_Expression_Array([], $stream->getCurrent()->getLine());
            do {
                $resources->addElement($this->parser->getExpressionParser()->parseExpression());
            } while (!$stream->test(\Twig_Token::BLOCK_END_TYPE));
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new DatagridThemeNode($datagrid, $resources, $lineno, $this->getTag());
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return 'rollerworks_datagrid_theme';
    }
}
