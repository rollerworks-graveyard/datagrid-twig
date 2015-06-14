<?php

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
 * @author Norbert Orzechowicz <norbert@fsi.pl>
 */
class DatagridThemeTokenParser extends \Twig_TokenParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(\Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $datagrid = $this->parser->getExpressionParser()->parseExpression();
        $theme = $this->parser->getExpressionParser()->parseExpression();

        $vars = new \Twig_Node_Expression_Array([], $stream->getCurrent()->getLine());

        if ($this->parser->getStream()->test(\Twig_Token::NAME_TYPE, 'with')) {
            $this->parser->getStream()->next();

            if ($this->parser->getStream()->test(\Twig_Token::PUNCTUATION_TYPE)) {
                $vars = $this->parser->getExpressionParser()->parseExpression();
            }
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new DatagridThemeNode($datagrid, $theme, $vars, $token->getLine(), $this->getTag());
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return 'rollerworks_datagrid_theme';
    }
}
