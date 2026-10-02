<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'group', 'label_ar', 'label_en'];

    /**
     * جلب قيمة إعداد بالاسم.
     */
    public static function get(string $key, $default = null)
    {
        $settings = Cache::rememberForever('settings', function () {
            return self::query()->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value]
        );
        Cache::forget('settings');
    }

    public static function forget(string $key): void
    {
        self::where('key', $key)->delete();
        Cache::forget('settings');
    }
}
