<?php

namespace Nlk\Theme\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $id
 * @property int    $page_settings_id
 * @property string $tenant_id
 * @property string $section_id   UUID — matches sections_order key
 * @property string $type         e.g. 'hero', 'featured-products'
 * @property array  $settings
 * @property array  $block_order
 * @property int    $position
 * @property bool   $disabled
 */
class ThemeSectionRow extends Model
{
    protected $table = 'theme_sections';

    protected $fillable = [
        'page_settings_id',
        'tenant_id',
        'section_id',
        'type',
        'settings',
        'block_order',
        'position',
        'disabled',
    ];

    protected $casts = [
        'settings'    => 'array',
        'block_order' => 'array',
        'disabled'    => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function pageSetting(): BelongsTo
    {
        return $this->belongsTo(ThemePageSetting::class, 'page_settings_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isEnabled(): bool
    {
        return !$this->disabled;
    }
}
