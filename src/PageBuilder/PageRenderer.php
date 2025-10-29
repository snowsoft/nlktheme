<?php

namespace Nlk\Theme\PageBuilder;

class PageRenderer
{
    /**
     * Page builder instance.
     *
     * @var PageBuilder
     */
    protected $builder;

    /**
     * Create new PageRenderer instance.
     *
     * @param  PageBuilder $builder
     */
    public function __construct(PageBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Render page with theme layout.
     *
     * @param  string $pageName
     * @return \Illuminate\Http\Response
     */
    public function render($pageName)
    {
        $config = $this->builder->getConfig($pageName);
        $sections = $this->builder->getSections($pageName);

        $theme = $config['theme'] ?? config('theme.themeDefault', 'default');
        $layout = $config['layout'] ?? config('theme.layoutDefault', 'layout');

        // Set theme and layout
        if (function_exists('theme')) {
            theme()->uses($theme)->layout($layout);
        }

        // Collect rendered sections
        $renderedSections = [];
        
        foreach ($sections as $section) {
            $renderedSections[] = $this->renderSection($section);
        }

        // Create view data
        $viewData = array_merge(
            $config['data'] ?? [],
            ['sections' => $renderedSections, 'pageConfig' => $config]
        );

        // Render page view or return content
        if (isset($config['view'])) {
            return theme()->view($config['view'], $viewData);
        }

        // Or render sections directly
        $content = implode('', $renderedSections);
        
        return theme()->of('pagebuilder::dynamic', ['content' => $content])->render();
    }

    /**
     * Render a single section.
     *
     * @param  array $section
     * @return string
     */
    protected function renderSection(array $section)
    {
        switch ($section['type']) {
            case 'widget':
                if (function_exists('theme')) {
                    try {
                        return theme()->widget($section['name'], $section['data'] ?? [])->render();
                    } catch (\Exception $e) {
                        return '<!-- Widget Error: ' . $e->getMessage() . ' -->';
                    }
                }
                break;
            
            case 'component':
                if (function_exists('theme_component')) {
                    try {
                        return theme_component($section['name'], null, $section['data'] ?? []) ?: '';
                    } catch (\Exception $e) {
                        return '<!-- Component Error: ' . $e->getMessage() . ' -->';
                    }
                }
                break;
            
            case 'partial':
                if (function_exists('theme')) {
                    try {
                        return theme()->partial($section['name'], $section['data'] ?? []) ?: '';
                    } catch (\Exception $e) {
                        return '<!-- Partial Error: ' . $e->getMessage() . ' -->';
                    }
                }
                break;

            case 'html':
                return $section['content'] ?? '';
            
            case 'view':
                if (function_exists('theme')) {
                    try {
                        return theme()->view($section['name'], $section['data'] ?? [])->render();
                    } catch (\Exception $e) {
                        return '<!-- View Error: ' . $e->getMessage() . ' -->';
                    }
                }
                break;
        }

        return '';
    }
}

