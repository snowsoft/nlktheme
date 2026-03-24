<?php

namespace Nlk\Theme\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int    $id
 * @property string $tenant_id
 * @property string $page_key
 * @property string $template
 * @property array  $sections_order
 * @property array  $settings
 * @property bool   $is_published
 */
class ThemePageSetting extends Model
{
    protected $table = 'theme_page_settings';

    protected $fillable = [
        'tenant_id',
        'page_key',
        'template',
        'sections_order',
        'settings',
        'is_published',
    ];

    protected $casts = [
        'sections_order' => 'array',
        'settings'       => 'array',
        'is_published'   => 'boolean',
    ];

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForPage($query, string $pageKey)
    {
        return $query->where('page_key', $pageKey);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function sectionRows(): HasMany
    {
        return $this->hasMany(ThemeSectionRow::class, 'page_settings_id')
                    ->orderBy('position');
    }
}
