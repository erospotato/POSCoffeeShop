<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("sys_setting_{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, mixed $value, ?string $group = null, ?string $description = null): self
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            $setting->value = (string) $value;
            if ($group !== null) {
                $setting->group = $group;
            }
            if ($description !== null) {
                $setting->description = $description;
            }
            $setting->save();
        } else {
            $setting = static::create([
                'key' => $key,
                'value' => (string) $value,
                'group' => $group ?? 'general',
                'description' => $description,
            ]);
        }

        Cache::forget("sys_setting_{$key}");

        return $setting;
    }
}
