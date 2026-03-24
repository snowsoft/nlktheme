<?php namespace Nlk\Theme;

use Illuminate\Support\Facades\Blade;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Nlk\Theme\SEO\SeoManager;
use Nlk\Theme\Tracking\TrackingManager;
use Nlk\Theme\Image\CdnImageManager;
use Nlk\Theme\Image\ArImageManager;

// FlexPage engine
use Nlk\Theme\FlexPage\SectionRegistry;
use Nlk\Theme\FlexPage\DataAdapters\MysqlAdapter;
use Nlk\Theme\FlexPage\DataAdapters\ApiAdapter;
use Nlk\Theme\FlexPage\DataAdapters\HybridAdapter;

// Built-in sections
use Nlk\Theme\FlexPage\Sections\HeroSection;
use Nlk\Theme\FlexPage\Sections\AnnouncementBarSection;
use Nlk\Theme\FlexPage\Sections\FeaturedProductsSection;
use Nlk\Theme\FlexPage\Sections\BannerSection;
use Nlk\Theme\FlexPage\Sections\CollectionListSection;
use Nlk\Theme\FlexPage\Sections\RichTextSection;
use Nlk\Theme\FlexPage\Sections\CustomHtmlSection;
// E-Commerce sections (Faz 2)
use Nlk\Theme\FlexPage\Sections\SocialProofSection;
use Nlk\Theme\FlexPage\Sections\FlashSaleSection;
use Nlk\Theme\FlexPage\Sections\MiniCartSection;
use Nlk\Theme\FlexPage\Sections\UrgencyBadgeSection;
use Nlk\Theme\FlexPage\Sections\UpsellSection;
use Nlk\Theme\FlexPage\Sections\RecentlyViewedSection;
// Trust & Review sections (Faz 4)
use Nlk\Theme\FlexPage\Sections\CustomerReviewSection;
use Nlk\Theme\FlexPage\Sections\TrustBadgeSection;
use Nlk\Theme\FlexPage\Sections\FaqAccordionSection;
// Media & Content sections (Faz 6-7)
use Nlk\Theme\FlexPage\Sections\EmailCaptureSection;
use Nlk\Theme\FlexPage\Sections\VideoCommerceSection;
use Nlk\Theme\FlexPage\Sections\BlogListSection;
use Nlk\Theme\FlexPage\Sections\InstagramFeedSection;
// Faz 5: Arama & Keşif
use Nlk\Theme\FlexPage\Sections\SearchSection;
use Nlk\Theme\FlexPage\Sections\FilterSidebarSection;
// Faz 6 (kalan): Shoppable Image
use Nlk\Theme\FlexPage\Sections\ShoppableImageSection;
// Faz 7 (kalan): Loyalty, Referral, WebPush
use Nlk\Theme\FlexPage\Sections\LoyaltyWidgetSection;
use Nlk\Theme\FlexPage\Sections\ReferralSection;
use Nlk\Theme\FlexPage\Sections\WebPushSection;
// Faz 9: Market Switcher
use Nlk\Theme\FlexPage\Sections\MarketSwitcherSection;
// GDPR
use Nlk\Theme\GDPR\CookieConsentManager;
// Performance (Faz 8)
use Nlk\Theme\Performance\CriticalCssExtractor;
use Nlk\Theme\Performance\CacheTagManager;
use Nlk\Theme\Performance\PwaManifestGenerator;
// i18n (Faz 9)
use Nlk\Theme\I18n\SectionI18nResolver;
use Nlk\Theme\I18n\CurrencyFormatter;

// PageBuilder
use Nlk\Theme\PageBuilder\PageBuilder;
use Nlk\Theme\PageBuilder\PageRenderer;

// Commands
use Nlk\Theme\Commands\ThemeGeneratorCommand;
use Nlk\Theme\Commands\ThemeDuplicateCommand;
use Nlk\Theme\Commands\WidgetGeneratorCommand;
use Nlk\Theme\Commands\ThemeListCommand;
use Nlk\Theme\Commands\ThemeDestroyCommand;
use Nlk\Theme\Commands\ThemeExportCommand;
use Nlk\Theme\Commands\ThemeImportCommand;

class ThemeServiceProvider extends ServiceProvider
{
    protected $defer = false;

    // ─── Boot ─────────────────────────────────────────────────────────────────

    public function boot(Router $router): void
    {
        $configPath = __DIR__ . '/../config/theme.php';

        // Publish config
        $this->publishes([$configPath => config_path('theme.php')], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/Database/Migrations' => database_path('migrations'),
        ], 'theme-migrations');

        // Load migrations automatically (package-level)
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Middleware aliases
        $router->aliasMiddleware('theme', Middleware\ThemeLoader::class);
        $router->aliasMiddleware('theme.security', Middleware\SecurityMiddleware::class);

        // Blade directives (existing)
        $this->registerBladeDirectives();
    }

    // ─── Register ─────────────────────────────────────────────────────────────

    public function register(): void
    {
        $configPath = __DIR__ . '/../config/theme.php';
        $this->mergeConfigFrom($configPath, 'theme');

        // ── Legacy singletons (Theme, Asset, Widget, Breadcrumb, Manifest)
        $this->registerAsset();
        $this->registerTheme();
        $this->registerWidget();
        $this->registerBreadcrumb();
        $this->registerManifest();

        // ── FlexPage Engine
        $this->registerSectionRegistry();
        $this->registerDataAdapters();
        $this->registerPageBuilder();

        // ── SEO + Tracking
        $this->registerSeoManager();
        $this->registerTrackingManager();

        // ── CDN + AR Image Engine
        $this->registerCdnEngine();

        // ── GDPR
        $this->app->singleton(CookieConsentManager::class, fn () => new CookieConsentManager());
        $this->app->alias(CookieConsentManager::class, 'nlk.consent');

        // ── Performance (Faz 8)
        $this->app->singleton(CriticalCssExtractor::class, fn () => new CriticalCssExtractor());
        $this->app->alias(CriticalCssExtractor::class, 'nlk.critical_css');
        $this->app->singleton(CacheTagManager::class, fn () => new CacheTagManager());
        $this->app->alias(CacheTagManager::class, 'nlk.cache_tags');
        $this->app->singleton(PwaManifestGenerator::class, fn () => new PwaManifestGenerator());
        $this->app->alias(PwaManifestGenerator::class, 'nlk.pwa');

        // ── i18n (Faz 9)
        $this->app->singleton(SectionI18nResolver::class, fn () => new SectionI18nResolver());
        $this->app->alias(SectionI18nResolver::class, 'nlk.i18n');
        $this->app->singleton(CurrencyFormatter::class, fn () => new CurrencyFormatter());
        $this->app->alias(CurrencyFormatter::class, 'nlk.currency');

        // ── Commands
        $this->registerCommands();
    }

    // ─── FlexPage Engine ───────────────────────────────────────────────────────

    protected function registerSectionRegistry(): void
    {
        $this->app->singleton(SectionRegistry::class, function () {
            $registry = new SectionRegistry();

            // Register built-in sections
            $registry->register('hero',               HeroSection::class);
            $registry->register('announcement-bar',   AnnouncementBarSection::class);
            $registry->register('featured-products',  FeaturedProductsSection::class);
            $registry->register('image-banner',       BannerSection::class);
            $registry->register('collection-list',    CollectionListSection::class);
            $registry->register('rich-text',          RichTextSection::class);
            $registry->register('custom-html',        CustomHtmlSection::class);

            // E-Commerce (Faz 2)
            $registry->register('social-proof',       SocialProofSection::class);
            $registry->register('flash-sale',         FlashSaleSection::class);
            $registry->register('mini-cart',          MiniCartSection::class);
            $registry->register('urgency-badge',      UrgencyBadgeSection::class);
            $registry->register('upsell',             UpsellSection::class);
            $registry->register('recently-viewed',    RecentlyViewedSection::class);

            // Trust & Review (Faz 4)
            $registry->register('customer-reviews',   CustomerReviewSection::class);
            $registry->register('trust-badges',       TrustBadgeSection::class);
            $registry->register('faq-accordion',      FaqAccordionSection::class);

            // Media & Marketing (Faz 6-7)
            $registry->register('email-capture',      EmailCaptureSection::class);
            $registry->register('video-commerce',     VideoCommerceSection::class);
            $registry->register('blog-list',          BlogListSection::class);
            $registry->register('instagram-feed',     InstagramFeedSection::class);

            // Arama & Keşif (Faz 5)
            $registry->register('ajax-search',        SearchSection::class);
            $registry->register('filter-sidebar',     FilterSidebarSection::class);

            // Shoppable & AR (Faz 6)
            $registry->register('shoppable-image',    ShoppableImageSection::class);

            // Sadakat & Pazarlama (Faz 7)
            $registry->register('loyalty-widget',     LoyaltyWidgetSection::class);
            $registry->register('referral',           ReferralSection::class);
            $registry->register('web-push',           WebPushSection::class);

            // i18n / Market (Faz 9)
            $registry->register('market-switcher',    MarketSwitcherSection::class);

        });

        // Alias for DI
        $this->app->alias(SectionRegistry::class, 'nlk.sections');
    }

    protected function registerDataAdapters(): void
    {
        $this->app->singleton(MysqlAdapter::class, fn () => new MysqlAdapter(
            connection: config('theme.data_sources.mysql_connection', 'mysql'),
            cacheTtl:   config('theme.builder.cache_ttl', 300),
        ));

        $this->app->singleton(ApiAdapter::class, fn () => ApiAdapter::make());

        $this->app->singleton(HybridAdapter::class, fn ($app) => new HybridAdapter(
            mysql: $app->make(MysqlAdapter::class),
            api:   $app->make(ApiAdapter::class),
        ));
    }

    protected function registerSeoManager(): void
    {
        $this->app->singleton(SeoManager::class, fn () => new SeoManager());
        $this->app->alias(SeoManager::class, 'nlk.seo');
    }

    protected function registerTrackingManager(): void
    {
        $this->app->singleton(TrackingManager::class, function () {
            $manager = new TrackingManager();
            $manager->configure([
                'enabled'        => (bool) config('theme.tracking.enabled', true),
                'gtm_id'         => config('theme.tracking.gtm_id', ''),
                'ga4_id'         => config('theme.tracking.ga4_id', ''),
                'google_ads_id'  => config('theme.tracking.google_ads_id', ''),
                'fb_pixel_id'    => config('theme.tracking.fb_pixel_id', ''),
                'consent_mode'   => config('theme.tracking.consent_mode', 'default'),
            ]);
            return $manager;
        });
        $this->app->alias(TrackingManager::class, 'nlk.tracking');
    }

    protected function registerCdnEngine(): void
    {
        $this->app->singleton(CdnImageManager::class, fn () => new CdnImageManager());
        $this->app->alias(CdnImageManager::class, 'nlk.cdn');

        $this->app->singleton(ArImageManager::class,
            fn ($app) => new ArImageManager($app->make(CdnImageManager::class))
        );
        $this->app->alias(ArImageManager::class, 'nlk.ar');
    }

    protected function registerPageBuilder(): void
    {
        $this->app->singleton(PageBuilder::class, fn ($app) => new PageBuilder(
            registry: $app->make(SectionRegistry::class),
        ));
        $this->app->alias(PageBuilder::class, 'nlk.pagebuilder');

        $this->app->singleton(PageRenderer::class, fn ($app) => new PageRenderer(
            builder: $app->make(PageBuilder::class),
        ));
    }

    // ─── Commands ─────────────────────────────────────────────────────────────

    protected function registerCommands(): void
    {
        $this->app->singleton('theme.create',    fn ($app) => new ThemeGeneratorCommand($app['config'], $app['files']));
        $this->app->singleton('theme.duplicate', fn ($app) => new ThemeDuplicateCommand($app['config'], $app['files']));
        $this->app->singleton('theme.widget',    fn ($app) => new WidgetGeneratorCommand($app['config'], $app['files']));
        $this->app->singleton('theme.list',      fn ($app) => new ThemeListCommand($app['config'], $app['files']));
        $this->app->singleton('theme.destroy',   fn ($app) => new ThemeDestroyCommand($app['config'], $app['files']));
        $this->app->singleton('theme.export',    fn ($app) => new ThemeExportCommand());
        $this->app->singleton('theme.import',    fn ($app) => new ThemeImportCommand());

        $this->commands([
            'theme.create',
            'theme.duplicate',
            'theme.widget',
            'theme.list',
            'theme.destroy',
            'theme.export',
            'theme.import',
        ]);
    }

    // ─── Blade Directives ─────────────────────────────────────────────────────

    protected function registerBladeDirectives(): void
    {
        $directives = [
            ['dd',         'dd(%s);'],
            ['dv',         'dd(get_defined_vars()[%s]);', 'dd(get_defined_vars()["__data"]);'],
            ['d',          'dump(%s);'],
            ['get',        'Theme::get(%s);'],
            ['getIfHas',   'Theme::has(%1$s) ? Theme::get(%1$s) : ""'],
            ['partial',    'Theme::partial(%s);'],
            ['sections',   'Theme::partial("sections.".%s);'],
            ['content',    null, 'Theme::content();'],
            ['asset',      'Theme::asset()->absUrl(%s);'],
            ['protect',    'protectEmail(%s);'],
            ['styles',     'Theme::asset()->container(%s)->styles();', 'Theme::asset()->styles();'],
            ['scripts',    'Theme::asset()->container(%s)->scripts();', 'Theme::asset()->scripts();'],
            ['widget',     'Theme::widget(%s)->render();'],
        ];

        foreach ($directives as $directive) {
            $this->addToBlade($directive);
        }

        // @page_render('home', $tenantId)
        Blade::directive('page_render', function (string $expression) {
            return "<?php echo app(\Nlk\Theme\PageBuilder\PageBuilder::class)->renderPage({$expression}); ?>";
        });

        // @section_render('hero')
        Blade::directive('section_render', function (string $expression) {
            return "<?php echo app(\Nlk\Theme\PageBuilder\PageRenderer::class)->renderSection(\$_pageKey ?? 'home', \$_tenantId ?? '', {$expression}); ?>";
        });

        // ── SEO ──
        // @seo_head  — render all meta/OG/JSON-LD
        Blade::directive('seo_head', function () {
            return "<?php echo app('nlk.seo')->render(); ?>";
        });

        // ── Tracking ──
        // @tracking_head  — in <head> (GTM/GA4/FB pixel init)
        Blade::directive('tracking_head', function () {
            return "<?php echo app('nlk.tracking')->renderHead(); ?>";
        });
        // @tracking_body  — right after <body>
        Blade::directive('tracking_body', function () {
            return "<?php echo app('nlk.tracking')->renderBody(); ?>";
        });
        // ── CDN Image ──
        // @cdn_img($id, $opts) — temel CDN img etiketi
        Blade::directive('cdn_img', function (string $expression) {
            return "<?php echo app('nlk.cdn')->imgTag({$expression}); ?>";
        });
        // @cdn_img_lazy($id, $opts) — LQIP + lazy load
        Blade::directive('cdn_img_lazy', function (string $expression) {
            return "<?php echo app('nlk.cdn')->lazyImgTag({$expression}); ?>";
        });
        // @cdn_srcset($id, $widths) — responsive srcset string
        Blade::directive('cdn_srcset', function (string $expression) {
            return "<?php echo app('nlk.cdn')->srcset({$expression}); ?>";
        });
        // @cdn_placeholder($id) — LQIP URL
        Blade::directive('cdn_placeholder', function (string $expression) {
            return "<?php echo app('nlk.cdn')->placeholderUrl({$expression}); ?>";
        });

        // ── AR Image ──
        // @cdn_img_ar($id) — no-bg img
        Blade::directive('cdn_img_ar', function (string $expression) {
            return "<?php echo app('nlk.ar')->noBgImg({$expression}); ?>";
        });
        // @ar_viewer($id, $glbUrl, $usdzUrl, $opts) — AR Quick Look / Scene Viewer
        Blade::directive('ar_viewer', function (string $expression) {
            return "<?php echo app('nlk.ar')->arTag({$expression}); ?>";
        });
        // @ar_script — AR JavaScript (layout'ta bir kez)
        Blade::directive('ar_script', function () {
            return "<?php echo app('nlk.ar')->arScript(); ?>";
        });

        // ── Performance ──
        // @critical_css('home') — inline critical CSS
        Blade::directive('critical_css', function (string $expression) {
            return "<?php echo app('nlk.critical_css')->renderTag({$expression}); ?>";
        });
        // @pwa_head — manifest + meta + SW script
        Blade::directive('pwa_head', function () {
            return "<?php echo app('nlk.pwa')->headTags(); ?>";
        });
        // @pwa_sw_script — sadece SW register scripti
        Blade::directive('pwa_sw_script', function () {
            return "<?php echo app('nlk.pwa')->swScript(); ?>";
        });

        // ── GDPR ──
        // @cookie_banner — cookie consent banner + modal
        Blade::directive('cookie_banner', function () {
            return "<?php echo app('nlk.consent')->renderBanner(); ?>";
        });
        // @gtm_consent_update — GTM Consent Mode v2 update
        Blade::directive('gtm_consent_update', function () {
            return "<?php echo app('nlk.consent')->gtmUpdateConsent(); ?>";
        });

        // ── i18n ──
        // @html_dir — 'rtl' veya 'ltr'
        Blade::directive('html_dir', function () {
            return "<?php echo app('nlk.i18n')->htmlDir(); ?>";
        });
        // @currency($amount, $currency) — para birimi format
        Blade::directive('currency', function (string $expression) {
            return "<?php echo app('nlk.currency')->format({$expression}); ?>";
        });
        // @currency_short($amount, $currency)
        Blade::directive('currency_short', function (string $expression) {
            return "<?php echo app('nlk.currency')->short({$expression}); ?>";
        });
    }

    protected function addToBlade(array $array): void
    {
        Blade::directive($array[0], function ($data) use ($array) {
            if (!$data) {
                return isset($array[2]) ? "<?php echo {$array[2]} ?>" : '';
            }
            return sprintf('<?php echo ' . $array[1] . ' ?>', $data ?? "get_defined_vars()['__data']");
        });
    }

    // ─── Legacy singletons (preserved) ───────────────────────────────────────

    public function registerAsset(): void
    {
        $this->app->singleton('asset', fn () => new Asset());
    }

    public function registerTheme(): void
    {
        $this->app->singleton('theme', fn ($app) => new Theme(
            $app['config'], $app['events'], $app['view'],
            $app['asset'], $app['files'], $app['breadcrumb'], $app['manifest']
        ));
        $this->app->alias('theme', Contracts\Theme::class);
    }

    public function registerWidget(): void
    {
        $this->app->singleton('widget', fn ($app) => new Widget($app['view']));
    }

    public function registerBreadcrumb(): void
    {
        $this->app->singleton('breadcrumb', fn ($app) => new Breadcrumb($app['files']));
    }

    public function registerManifest(): void
    {
        $this->app->singleton('manifest', fn ($app) => new Manifest($app['files']));
    }

    public function provides(): array
    {
        return [
            'asset', 'theme', 'widget', 'breadcrumb',
            'nlk.pagebuilder', 'nlk.sections',
            'nlk.seo', 'nlk.tracking',
            'nlk.cdn', 'nlk.ar',
            'nlk.consent',
            'nlk.critical_css', 'nlk.cache_tags', 'nlk.pwa',
            'nlk.i18n', 'nlk.currency',
        ];
    }
}