<?php

namespace Nlk\Theme\PageBuilder;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PageBuilder
{
    /**
     * Page configurations storage.
     *
     * @var array
     */
    protected $pages = [];

    /**
     * Current page name.
     *
     * @var string
     */
    protected $currentPage;

    /**
     * Cache prefix.
     *
     * @var string
     */
    protected $cachePrefix = 'page_builder_';

    /**
     * Load page configuration.
     *
     * @param  string $pageName
     * @return array
     */
    public function loadPage($pageName)
    {
        $cacheKey = $this->cachePrefix . $pageName;

        return Cache::remember($cacheKey, 3600, function() use ($pageName) {
            $configPath = storage_path('app/pagebuilder/' . $pageName . '.json');
            
            if (file_exists($configPath)) {
                return json_decode(file_get_contents($configPath), true);
            }

            return [];
        });
    }

    /**
     * Save page configuration.
     *
     * @param  string $pageName
     * @param  array  $config
     * @return bool
     */
    public function savePage($pageName, array $config)
    {
        $directory = storage_path('app/pagebuilder');
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $configPath = $directory . '/' . $pageName . '.json';
        
        $saved = file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($saved) {
            Cache::forget($this->cachePrefix . $pageName);
            return true;
        }

        return false;
    }

    /**
     * Set current page.
     *
     * @param  string $pageName
     * @return $this
     */
    public function page($pageName)
    {
        $this->currentPage = $pageName;
        $this->pages[$pageName] = $this->loadPage($pageName);

        return $this;
    }

    /**
     * Add a widget to page.
     *
     * @param  string $widgetName
     * @param  array  $data
     * @param  int|null $position
     * @return $this
     */
    public function addWidget($widgetName, array $data = [], $position = null)
    {
        if (!$this->currentPage) {
            throw new \Exception('No page selected. Use page() method first.');
        }

        $widget = [
            'type' => 'widget',
            'name' => $widgetName,
            'data' => $data,
            'position' => $position ?? count($this->pages[$this->currentPage]['sections'] ?? []),
        ];

        if (!isset($this->pages[$this->currentPage]['sections'])) {
            $this->pages[$this->currentPage]['sections'] = [];
        }

        $this->pages[$this->currentPage]['sections'][] = $widget;

        return $this;
    }

    /**
     * Add a blade component to page.
     *
     * @param  string $componentName
     * @param  array  $data
     * @param  int|null $position
     * @return $this
     */
    public function addComponent($componentName, array $data = [], $position = null)
    {
        if (!$this->currentPage) {
            throw new \Exception('No page selected. Use page() method first.');
        }

        $component = [
            'type' => 'component',
            'name' => $componentName,
            'data' => $data,
            'position' => $position ?? count($this->pages[$this->currentPage]['sections'] ?? []),
        ];

        if (!isset($this->pages[$this->currentPage]['sections'])) {
            $this->pages[$this->currentPage]['sections'] = [];
        }

        $this->pages[$this->currentPage]['sections'][] = $component;

        return $this;
    }

    /**
     * Add a blade partial to page.
     *
     * @param  string $partialName
     * @param  array  $data
     * @param  int|null $position
     * @return $this
     */
    public function addPartial($partialName, array $data = [], $position = null)
    {
        if (!$this->currentPage) {
            throw new \Exception('No page selected. Use page() method first.');
        }

        $partial = [
            'type' => 'partial',
            'name' => $partialName,
            'data' => $data,
            'position' => $position ?? count($this->pages[$this->currentPage]['sections'] ?? []),
        ];

        if (!isset($this->pages[$this->currentPage]['sections'])) {
            $this->pages[$this->currentPage]['sections'] = [];
        }

        $this->pages[$this->currentPage]['sections'][] = $partial;

        return $this;
    }

    /**
     * Set page layout.
     *
     * @param  string $layout
     * @return $this
     */
    public function setLayout($layout)
    {
        if (!$this->currentPage) {
            throw new \Exception('No page selected. Use page() method first.');
        }

        $this->pages[$this->currentPage]['layout'] = $layout;

        return $this;
    }

    /**
     * Set page theme.
     *
     * @param  string $theme
     * @return $this
     */
    public function setTheme($theme)
    {
        if (!$this->currentPage) {
            throw new \Exception('No page selected. Use page() method first.');
        }

        $this->pages[$this->currentPage]['theme'] = $theme;

        return $this;
    }

    /**
     * Sort sections by position.
     *
     * @param  string|null $pageName
     * @return $this
     */
    public function sortSections($pageName = null)
    {
        $page = $pageName ?? $this->currentPage;

        if (!isset($this->pages[$page]['sections'])) {
            return $this;
        }

        usort($this->pages[$page]['sections'], function($a, $b) {
            return ($a['position'] ?? 0) <=> ($b['position'] ?? 0);
        });

        // Re-index positions
        foreach ($this->pages[$page]['sections'] as $index => &$section) {
            $section['position'] = $index;
        }

        return $this;
    }

    /**
     * Update section position.
     *
     * @param  int $oldPosition
     * @param  int $newPosition
     * @param  string|null $pageName
     * @return $this
     */
    public function updateSectionPosition($oldPosition, $newPosition, $pageName = null)
    {
        $page = $pageName ?? $this->currentPage;

        if (!isset($this->pages[$page]['sections'])) {
            return $this;
        }

        $sections = $this->pages[$page]['sections'];
        
        // Remove section from old position
        $moved = array_splice($sections, $oldPosition, 1);
        
        // Insert at new position
        array_splice($sections, $newPosition, 0, $moved);

        // Update positions
        foreach ($sections as $index => &$section) {
            $section['position'] = $index;
        }

        $this->pages[$page]['sections'] = $sections;

        return $this;
    }

    /**
     * Remove section from page.
     *
     * @param  int $position
     * @param  string|null $pageName
     * @return $this
     */
    public function removeSection($position, $pageName = null)
    {
        $page = $pageName ?? $this->currentPage;

        if (!isset($this->pages[$page]['sections'])) {
            return $this;
        }

        unset($this->pages[$page]['sections'][$position]);
        
        // Re-index
        $this->pages[$page]['sections'] = array_values($this->pages[$page]['sections']);
        
        // Update positions
        foreach ($this->pages[$page]['sections'] as $index => &$section) {
            $section['position'] = $index;
        }

        return $this;
    }

    /**
     * Get page sections.
     *
     * @param  string|null $pageName
     * @return array
     */
    public function getSections($pageName = null)
    {
        $page = $pageName ?? $this->currentPage;

        if (!isset($this->pages[$page])) {
            $this->pages[$page] = $this->loadPage($page);
        }

        $this->sortSections($page);

        return $this->pages[$page]['sections'] ?? [];
    }

    /**
     * Get page configuration.
     *
     * @param  string|null $pageName
     * @return array
     */
    public function getConfig($pageName = null)
    {
        $page = $pageName ?? $this->currentPage;

        if (!isset($this->pages[$page])) {
            $this->pages[$page] = $this->loadPage($page);
        }

        return $this->pages[$page] ?? [];
    }

    /**
     * Render page.
     *
     * @param  string|null $pageName
     * @return string
     */
    public function render($pageName = null)
    {
        $page = $pageName ?? $this->currentPage;
        $sections = $this->getSections($page);
        $config = $this->getConfig($page);

        $html = '';

        foreach ($sections as $section) {
            switch ($section['type']) {
                case 'widget':
                    if (function_exists('theme')) {
                        $html .= theme()->widget($section['name'], $section['data'] ?? [])->render();
                    }
                    break;
                
                case 'component':
                    if (function_exists('theme_component')) {
                        $html .= theme_component($section['name'], null, $section['data'] ?? []);
                    }
                    break;
                
                case 'partial':
                    if (function_exists('theme')) {
                        $html .= theme()->partial($section['name'], $section['data'] ?? []);
                    }
                    break;
            }
        }

        return $html;
    }

    /**
     * Save current page configuration.
     *
     * @param  string|null $pageName
     * @return bool
     */
    public function save($pageName = null)
    {
        $page = $pageName ?? $this->currentPage;

        if (!$page || !isset($this->pages[$page])) {
            return false;
        }

        $this->sortSections($page);

        return $this->savePage($page, $this->pages[$page]);
    }

    /**
     * Delete page configuration.
     *
     * @param  string $pageName
     * @return bool
     */
    public function delete($pageName)
    {
        $configPath = storage_path('app/pagebuilder/' . $pageName . '.json');
        
        if (file_exists($configPath)) {
            Cache::forget($this->cachePrefix . $pageName);
            return unlink($configPath);
        }

        return false;
    }
}

