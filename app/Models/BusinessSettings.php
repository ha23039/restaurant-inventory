<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSettings extends Model
{
    protected $fillable = [
        'restaurant_name',
        'restaurant_address',
        'restaurant_phone',
        'restaurant_email',
        'restaurant_tax_id',
        'logo_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'welcome_message',
        'footer_message',
        'currency',
        'timezone',
        'social_media',
    ];

    protected $casts = [
        'social_media' => 'array',
    ];

    /**
     * Singleton pattern - solo un registro
     */
    public static function get()
    {
        return static::first() ?? static::create([
            'restaurant_name' => config('app.name', 'Restaurant POS'),
        ]);
    }
}
