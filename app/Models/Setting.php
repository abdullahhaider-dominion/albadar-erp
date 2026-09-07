<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('app_settings', 60, function () {
            return static::query()->pluck('value', 'key')->all();
        });

        return $settings[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('app_settings');
    }

    public static function allowEditorEdit(): bool
    {
        return filter_var(static::get('allow_editor_edit', '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public static function companyName(): string
    {
        return (string) static::get('company_name', 'Roznamcha ERP');
    }
}
