<?php

namespace Rafaelogic\CodeSnoutr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $table = 'codesnoutr_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_encrypted',
    ];

    protected $casts = [
        'value' => 'array',
        'is_encrypted' => 'boolean',
    ];

    /**
     * Get setting value with decryption if needed
     */
    public function getValueAttribute($value): mixed
    {
        $decoded = json_decode($value, true);
        
        if ($this->is_encrypted && is_string($decoded)) {
            return Crypt::decrypt($decoded);
        }
        
        return $decoded;
    }

    /**
     * Set setting value with encryption if needed
     */
    public function setValueAttribute($value): void
    {
        if ($this->is_encrypted && is_string($value)) {
            $value = Crypt::encrypt($value);
        }
        
        $this->attributes['value'] = json_encode($value);
    }

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        
        return $setting ? $setting->value : $default;
    }

    /**
     * Alias for get method for backward compatibility
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::get($key, $default);
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, mixed $value, string $type = 'general', bool $encrypted = false): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'is_encrypted' => $encrypted,
            ]
        );
    }

    /**
     * Get OpenAI API key
     */
    public static function getOpenAiApiKey(): ?string
    {
        return static::get('openai_api_key');
    }

    /**
     * Set OpenAI API key (not encrypted - for developer access)
     */
    public static function setOpenAiApiKey(string $apiKey): static
    {
        return static::set('openai_api_key', $apiKey, 'ai', false);
    }

    /**
     * Get UI theme preference
     */
    public static function getTheme(): string
    {
        return static::get('ui_theme', 'system');
    }

    /**
     * Set UI theme preference
     */
    public static function setTheme(string $theme): static
    {
        return static::set('ui_theme', $theme, 'ui');
    }

    /**
     * Get AI settings
     */
    public static function getAiSettings(): array
    {
        return [
            'enabled' => static::get('ai_enabled', false),
            'api_key' => static::getOpenAiApiKey(),
            'model' => static::get('ai_model', 'gpt-4'),
            'auto_fix_enabled' => static::get('ai_auto_fix_enabled', false),
            'monthly_limit' => static::get('ai_monthly_limit', 50.00),
            'current_usage' => static::get('ai_current_usage', 0.00),
        ];
    }

    /**
     * Update AI settings
     */
    public static function updateAiSettings(array $settings): void
    {
        if (isset($settings['enabled'])) {
            static::set('ai_enabled', $settings['enabled'], 'ai');
        }
        
        if (isset($settings['api_key'])) {
            static::setOpenAiApiKey($settings['api_key']);
        }
        
        if (isset($settings['model'])) {
            static::set('ai_model', $settings['model'], 'ai');
        }
        
        if (isset($settings['auto_fix_enabled'])) {
            static::set('ai_auto_fix_enabled', $settings['auto_fix_enabled'], 'ai');
        }
        
        if (isset($settings['monthly_limit'])) {
            static::set('ai_monthly_limit', $settings['monthly_limit'], 'ai');
        }
    }

    /**
     * Add to AI usage cost
     */
    public static function addAiUsage(float $cost): void
    {
        $currentUsage = static::get('ai_current_usage', 0.00);
        static::set('ai_current_usage', $currentUsage + $cost, 'ai');
    }

    /**
     * Reset monthly AI usage (called by scheduler)
     */
    public static function resetMonthlyAiUsage(): void
    {
        static::set('ai_current_usage', 0.00, 'ai');
        static::set('ai_last_reset', now(), 'ai');
    }

    /**
     * Get scan preferences
     */
    public static function getScanPreferences(): array
    {
        return [
            'default_categories' => static::get('scan_default_categories', ['security', 'performance']),
            'auto_scan_on_save' => static::get('scan_auto_on_save', false),
            'include_vendor' => static::get('scan_include_vendor', false),
            'max_file_size' => static::get('scan_max_file_size', 1024 * 1024), // 1MB
        ];
    }

    /**
     * Update scan preferences
     */
    public static function updateScanPreferences(array $preferences): void
    {
        foreach ($preferences as $key => $value) {
            static::set("scan_{$key}", $value, 'scan');
        }
    }

    /**
     * Scope for settings by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for encrypted settings
     */
    public function scopeEncrypted($query)
    {
        return $query->where('is_encrypted', true);
    }
}
