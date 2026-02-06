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
        'digital_menu_enabled',
        'whatsapp_number',
        'country_code',
        'digital_menu_welcome_message',
        'digital_menu_closed_message',
        'min_order_amount',
        'estimated_prep_time',
        'require_phone_verification',
        'allow_pickup',
        'allow_delivery',
        'allow_dine_in',
        'delivery_fee',
        'delivery_min_amount',
        'digital_menu_schedule',
        'digital_menu_custom_css',
    ];

    protected $casts = [
        'social_media' => 'array',
        'digital_menu_enabled' => 'boolean',
        'require_phone_verification' => 'boolean',
        'allow_pickup' => 'boolean',
        'allow_delivery' => 'boolean',
        'allow_dine_in' => 'boolean',
        'min_order_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'delivery_min_amount' => 'decimal:2',
        'estimated_prep_time' => 'integer',
        'digital_menu_schedule' => 'array',
    ];

    /**
     * Singleton pattern - solo un registro
     */
    public static function get()
    {
        $settings = static::first();

        if (!$settings) {
            $settings = static::create([
                'restaurant_name' => config('app.name', 'Restaurant POS'),
                'digital_menu_schedule' => static::getDefaultSchedule(),
            ]);
        }

        return $settings;
    }

    /**
     * Get default schedule for digital menu
     */
    public static function getDefaultSchedule(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $schedule = [];

        foreach ($days as $day) {
            $schedule[$day] = [
                'enabled' => $day !== 'sunday',
                'open' => '08:00',
                'close' => '20:00',
            ];
        }

        return $schedule;
    }

    /**
     * Check if the digital menu is currently open
     */
    public function isDigitalMenuOpen(): bool
    {
        if (!$this->digital_menu_enabled) {
            return false;
        }

        if (!$this->digital_menu_schedule) {
            return false;
        }

        $now = now();
        $dayName = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');

        $daySchedule = $this->digital_menu_schedule[$dayName] ?? null;

        if (!$daySchedule || !$daySchedule['enabled']) {
            return false;
        }

        return $currentTime >= $daySchedule['open'] && $currentTime <= $daySchedule['close'];
    }

    /**
     * Get available delivery methods with labels and fees
     */
    public function getAvailableDeliveryMethods(): array
    {
        $methods = [];

        if ($this->allow_pickup) {
            $methods[] = ['value' => 'pickup', 'label' => 'Para llevar', 'fee' => 0];
        }

        if ($this->allow_delivery) {
            $methods[] = ['value' => 'delivery', 'label' => 'Delivery', 'fee' => (float) $this->delivery_fee];
        }

        if ($this->allow_dine_in) {
            $methods[] = ['value' => 'dine_in', 'label' => 'Comer aqui', 'fee' => 0];
        }

        return $methods;
    }

    /**
     * Get the next opening time for the digital menu
     */
    public function getNextOpeningTime(): ?string
    {
        if (!$this->digital_menu_schedule) {
            return null;
        }

        $now = now();
        $currentDayName = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');

        // Check if opens later today
        $todaySchedule = $this->digital_menu_schedule[$currentDayName] ?? null;
        if ($todaySchedule && $todaySchedule['enabled'] && $currentTime < $todaySchedule['open']) {
            return 'Hoy a las ' . $this->formatTime($todaySchedule['open']);
        }

        // Check next 7 days
        $daysOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $currentDayIndex = array_search($currentDayName, $daysOrder);

        for ($i = 1; $i <= 7; $i++) {
            $nextDayIndex = ($currentDayIndex + $i) % 7;
            $nextDayName = $daysOrder[$nextDayIndex];
            $daySchedule = $this->digital_menu_schedule[$nextDayName] ?? null;

            if ($daySchedule && $daySchedule['enabled']) {
                $dayLabel = $this->getDayLabel($nextDayName, $i);
                return $dayLabel . ' a las ' . $this->formatTime($daySchedule['open']);
            }
        }

        return null;
    }

    private function formatTime(string $time): string
    {
        return \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A');
    }

    private function getDayLabel(string $dayName, int $daysFromNow): string
    {
        if ($daysFromNow === 1) {
            return 'Mañana';
        }

        $days = [
            'monday' => 'Lunes',
            'tuesday' => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday' => 'Jueves',
            'friday' => 'Viernes',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo',
        ];

        return $days[$dayName] ?? $dayName;
    }
}
