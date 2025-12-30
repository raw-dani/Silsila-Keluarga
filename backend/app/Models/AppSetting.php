<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        // Cast value based on type
        switch ($setting->type) {
            case 'integer':
                return (int) $setting->value;
            case 'boolean':
                return in_array(strtolower($setting->value), ['true', '1', 'yes', 'on']);
            case 'json':
                return json_decode($setting->value, true);
            default:
                return $setting->value;
        }
    }

    /**
     * Set a setting value by key
     */
    public static function setValue(string $key, $value, string $type = 'string', string $description = null)
    {
        // Convert value to string based on type
        $stringValue = match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json' => json_encode($value),
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAllSettings()
    {
        return static::all()->mapWithKeys(function ($setting) {
            return [$setting->key => static::getValue($setting->key)];
        });
    }
}
