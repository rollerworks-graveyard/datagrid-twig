<?php

/*
 * This file is part of the RollerworksDatagrid package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Datagrid\Twig\Extension;

use Rollerworks\Component\Datagrid\Column\CellView;
use Rollerworks\Component\Datagrid\Column\HeaderView;
use Rollerworks\Component\Datagrid\DatagridView;
use Rollerworks\Component\Datagrid\Twig\TokenParser\DatagridThemeTokenParser;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Norbert Orzechowicz <norbert@fsi.pl>
 */
class DatagridExtension extends \Twig_Extension implements \Twig_Extension_InitRuntimeInterface
{
    /**
     * @var array
     */
    private $themes = [];

    /**
     * @var array[]
     */
    private $themesVars = [];

    /**
     * @var array
     */
    private $blocksCache = [];

    /**
     * @var \Twig_Template[]
     */
    private $baseThemes;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * Constructor.
     *
     * @param string|string[]|\Twig_Template|\Twig_Template[] $theme
     */
    public function __construct($theme)
    {
        $this->baseThemes = is_array($theme) ? $theme : [$theme];
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;

        for ($i = count($this->baseThemes) - 1; $i >= 0; --$i) {
            $theme = $this->baseThemes[$i];

            if (!$theme instanceof \Twig_Template) {
                $theme = $this->environment->loadTemplate($theme);
            }

            $this->baseThemes[$i] = $theme;
        }
    }
    /**
     * Set base theme or themes.
     *
     * @param string|string[]|\Twig_Template|\Twig_Template[] $theme
     */
    public function setBaseTheme($theme)
    {
        $themes = is_array($theme) ? $theme : [$theme];
        $this->baseThemes = [];

        foreach ($themes as $theme) {
            if (!$theme instanceof \Twig_Template) {
                $theme = $this->environment->loadTemplate($theme);
            }

            $this->baseThemes[] = $theme;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('rollerworks_datagrid', [$this, 'datagrid'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('rollerworks_datagrid_widget', [$this, 'datagrid'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('rollerworks_datagrid_header_widget', [$this, 'datagridHeader'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('rollerworks_datagrid_rowset_widget', [$this, 'datagridRowset'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('rollerworks_datagrid_column_header_widget', [$this, 'datagridColumnHeader'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('rollerworks_datagrid_column_cell_widget', [$this, 'datagridColumnCell'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('rollerworks_datagrid_attributes_widget', [$this, 'datagridAttributes'], ['is_safe' => ['html']]),
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
    public function getName()
    {
        return 'rollerworks_datagrid';
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
     * @param string $text The text to humanize.
     *
     * @return string The humanized text.
     */
    public function humanize($text)
    {
        return ucfirst(trim(strtolower(preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $text))));
    }

    /**
     * Set theme for specific DataGrid.
     *
     * Theme is nothing more than twig template that contains block required to
     * render a Datagrid.
     *
     * @internal
     *
     * @param DatagridView $datagrid
     * @param                       $theme
     * @param array                 $vars
     */
    public function setTheme(DatagridView $datagrid, $theme, array $vars = [])
    {
        if (!$theme instanceof \Twig_Template) {
            $theme = $this->environment->loadTemplate($theme);
        }

        $this->themes[$datagrid->name] = $theme;
        $this->themesVars[$datagrid->name] = $vars;
    }

    /**
     * Renders the DatagridView object.
     *
     * @internal
     *
     * @param DatagridView $view
     *
     * @return string
     */
    public function datagrid(DatagridView $view)
    {
        $datagridName = $view->name;
        $blockNames = [
            'datagrid_'.$datagridName,
            'datagrid',
        ];

        $context = [
            'datagrid' => $view,
            'vars' => $this->getVars($view),
        ];

        return $this->renderTheme($view, $context, $blockNames, $datagridName.'_widget');
    }

    /**
     * Render header row in datagrid.
     *
     * @internal
     *
     * @param DatagridView $view
     * @param array        $vars
     *
     * @return string
     */
    public function datagridHeader(DatagridView $view, array $vars = [])
    {
        $datagridName = $view->name;
        $blockNames = [
            'datagrid_'.$datagridName.'_header',
            'datagrid_header',
        ];

        $context = [
            'headers' => $view->columns,
            'vars' => array_merge(
                $this->getVars($view),
                $vars
            ),
        ];

        return $this->renderTheme($view, $context, $blockNames, $datagridName.'_header');
    }

    /**
     * Render column header.
     *
     * @internal
     *
     * @param HeaderView $view
     * @param array      $vars
     *
     * @return string
     */
    public function datagridColumnHeader(HeaderView $view, array $vars = [])
    {
        $datagridView = $view->datagrid;
        $datagridName = $datagridView->name;

        $blockNames = [
            'datagrid_'.$datagridName.'_column_name_'.$view->name.'_header',
            'datagrid_'.$datagridName.'_column_type_'.$view->prefix.'_header',
            'datagrid_column_name_'.$view->name.'_header',
            'datagrid_column_type_'.$view->prefix.'_header',
            'datagrid_'.$datagridName.'_column_header',
            'datagrid_column_header',
        ];

        $context = [
            'header' => $view,
            'translation_domain' => isset($view->attributes['translation_domain']) ? $view->attributes['translation_domain'] : null,
            'label' => $view->label,
            'vars' => array_merge($this->getVars($view->datagrid), $vars),
        ];

        return $this->renderTheme($datagridView, $context, $blockNames, $datagridName.'_column_name_'.$view->name.'_header');
    }

    /**
     * Render Datagrid rows except header.
     *
     * @internal
     *
     * @param DatagridView $view
     * @param array        $vars
     *
     * @return string
     */
    public function datagridRowset(DatagridView $view, array $vars = [])
    {
        $datagridName = $view->name;
        $blockNames = [
            'datagrid_'.$datagridName.'_rowset',
            'datagrid_rowset',
        ];

        $context = [
            'datagrid' => $view,
            'vars' => array_merge(
                $this->getVars($view),
                $vars
            ),
        ];

        return $this->renderTheme($view, $context, $blockNames, $datagridName.'_rowset');
    }

    /**
     * Render column cell.
     *
     * @internal
     *
     * @param CellView $view
     * @param array    $vars
     *
     * @return string
     */
    public function datagridColumnCell(CellView $view, array $vars = [])
    {
        $datagridView = $view->datagrid;
        $datagridName = $datagridView->name;

        $blockNames = [
            'datagrid_'.$datagridName.'_column_name_'.$view->name.'_cell',
            'datagrid_'.$datagridName.'_column_type_'.$view->prefix.'_cell',
            'datagrid_column_name_'.$view->name.'_cell',
            'datagrid_column_type_'.$view->prefix.'_cell',
            'datagrid_'.$datagridName.'_column_cell',
            'datagrid_column_cell',
        ];

        $context = array_merge(
            [
                'cell' => $view,
                'row_index' => $view->attributes['row'],
                'datagrid_name' => $datagridName,
                'translation_domain' => isset($view->attributes['translation_domain']) ? $view->attributes['translation_domain'] : null,
                'vars' => array_merge(
                    $view->attributes,
                    $vars
                ),
            ],
            $this->getVars($datagridView)
        );

        return $this->renderTheme($datagridView, $context, $blockNames, $datagridName.'_column_name_'.$view->name.'_cell');
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
        $attrs = [];

        foreach ($attributes as $attributeName => $attributeValue) {
            $attrs[] = $attributeName.'="'.$attributeValue.'"';
        }

        return ' '.implode(' ', $attrs);
    }

    /**
     * Returns a list of templates that could be able to render
     * the DatagridView.
     *
     * The last template will always be the default one.
     *
     * @param DatagridView $datagrid
     *
     * @return array
     */
    private function getTemplates(DatagridView $datagrid)
    {
        $templates = [];

        if (isset($this->themes[$datagrid->name])) {
            $templates[] = $this->themes[$datagrid->name];
        }

        for ($i = count($this->baseThemes) - 1; $i >= 0; --$i) {
            $templates[] = $this->baseThemes[$i];
        }

        return $templates;
    }

    /**
     * Returns the variables that were assigned to the DatagridView.
     *
     * @param DatagridView $datagrid
     *
     * @return array
     */
    private function getVars(DatagridView $datagrid)
    {
        if (isset($this->themesVars[$datagrid->name])) {
            return $this->themesVars[$datagrid->name];
        }

        return [];
    }

    /**
     * Render the theme block for the datagrid.
     *
     * The resolved template and block are cached for future reference.
     *
     * @param DatagridView $datagridView
     * @param array        $contextVars
     * @param array        $availableBlocks
     * @param string|null  $cacheKey
     *
     * @throws \Exception
     * @throws \Twig_Error
     * @throws \Twig_Error_Runtime
     *
     * @return string
     */
    private function renderTheme(
        DatagridView $datagridView,
        array $contextVars = [],
        $availableBlocks = [],
        $cacheKey = null
    ) {
        $contextVars = $this->environment->mergeGlobals($contextVars);

        if ($cacheKey && isset($this->blocksCache[$cacheKey])) {
            ob_start();

            $this->blocksCache[$cacheKey][0]->displayBlock($this->blocksCache[$cacheKey][1], $contextVars);

            return ob_get_clean();
        }

        $templates = $this->getTemplates($datagridView);

        ob_start();

        foreach ($availableBlocks as $blockName) {
            foreach ($templates as $template) {
                if (false !== ($template = $this->findTemplateWithBlock($template, $blockName))) {
                    $template->displayBlock($blockName, $contextVars);

                    if ($cacheKey) {
                        $this->blocksCache[$cacheKey] = [$template, $blockName];
                    }

                    return ob_get_clean();
                }
            }
        }

        return ob_get_clean();
    }

    /**
     * @param \Twig_Template $template
     * @param string         $blockName
     *
     * @return \Twig_Template|bool
     */
    private function findTemplateWithBlock(\Twig_Template $template, $blockName)
    {
        if ($template->hasBlock($blockName)) {
            return $template;
        }

        if (false === ($parent = $template->getParent([]))) {
            return false;
        }

        if (false !== $this->findTemplateWithBlock($parent, $blockName)) {
            return $template;
        }

        return false;
    }
}
