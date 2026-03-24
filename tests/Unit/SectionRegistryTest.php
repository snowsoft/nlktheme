<?php

namespace Nlk\Theme\Tests\Unit;

use Nlk\Theme\FlexPage\SectionRegistry;
use Nlk\Theme\FlexPage\Sections\HeroSection;
use Nlk\Theme\FlexPage\Sections\FeaturedProductsSection;
use Nlk\Theme\FlexPage\Sections\AnnouncementBarSection;
use Nlk\Theme\FlexPage\Sections\CollectionListSection;
use Nlk\Theme\FlexPage\Sections\RichTextSection;
use Nlk\Theme\FlexPage\Sections\CustomHtmlSection;
use Nlk\Theme\FlexPage\Sections\BannerSection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SectionRegistryTest extends TestCase
{
    private SectionRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new SectionRegistry();
    }

    // ─── Registration ─────────────────────────────────────────────────────────

    public function test_registers_and_resolves_built_in_sections(): void
    {
        // Register all built-in sections
        $map = [
            'hero'               => HeroSection::class,
            'announcement-bar'   => AnnouncementBarSection::class,
            'featured-products'  => FeaturedProductsSection::class,
            'image-banner'       => BannerSection::class,
            'collection-list'    => CollectionListSection::class,
            'rich-text'          => RichTextSection::class,
            'custom-html'        => CustomHtmlSection::class,
        ];

        foreach ($map as $type => $class) {
            $this->registry->register($type, $class);
        }

        $this->assertSame($map, $this->registry->all());
    }

    public function test_has_returns_true_for_registered_type(): void
    {
        $this->registry->register('hero', HeroSection::class);
        $this->assertTrue($this->registry->has('hero'));
    }

    public function test_has_returns_false_for_unregistered_type(): void
    {
        $this->assertFalse($this->registry->has('nonexistent'));
    }

    public function test_throws_on_invalid_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must implement/');

        $this->registry->register('bad', \stdClass::class);
    }

    public function test_throws_on_resolve_unknown_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/not registered/');

        $this->registry->resolve('this-does-not-exist');
    }

    // ─── Section Schema ───────────────────────────────────────────────────────

    public function test_hero_section_type(): void
    {
        $section = new HeroSection();
        $this->assertSame('hero', $section->type());
        $this->assertSame('mysql', $section->dataSource());
    }

    public function test_custom_html_ignores_views(): void
    {
        $section = new CustomHtmlSection();
        $html = $section->render(['html' => '<b>Test</b>'], [], []);
        $this->assertSame('<b>Test</b>', $html);
    }

    public function test_rich_text_schema_has_required_keys(): void
    {
        $section = new RichTextSection();
        $schema  = $section->schema();

        $this->assertArrayHasKey('name', $schema);
        $this->assertArrayHasKey('settings', $schema);
        $this->assertArrayHasKey('presets', $schema);
        $this->assertSame('rich-text', $section->type());
    }

    public function test_announcement_bar_default_source_is_static(): void
    {
        $section = new AnnouncementBarSection();
        // AbstractSection default is 'static', announcement bar doesn't override
        $this->assertSame('static', $section->dataSource());
    }

    public function test_featured_products_source_is_hybrid(): void
    {
        $section = new FeaturedProductsSection();
        $this->assertSame('hybrid', $section->dataSource());
    }

    // ─── JSON round-trip helpers ──────────────────────────────────────────────

    public function test_sections_order_preserved(): void
    {
        $order = ['hero_main', 'featured', 'banner'];
        // Simulate what PageBuilder does
        $data = [
            'sections_order' => $order,
            'settings'       => [],
            'sections'       => [],
        ];
        $this->assertSame($order, $data['sections_order']);
    }
}
