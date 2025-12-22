<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            // Digital Menu Settings
            $table->boolean('digital_menu_enabled')->default(false)->after('social_media');
            $table->string('whatsapp_number', 20)->nullable()->after('digital_menu_enabled');
            $table->text('digital_menu_welcome_message')->nullable()->after('whatsapp_number');
            $table->string('digital_menu_closed_message', 500)->default('Estamos cerrados en este momento')->after('digital_menu_welcome_message');
            $table->decimal('min_order_amount', 10, 2)->default(0.00)->after('digital_menu_closed_message');
            $table->unsignedInteger('estimated_prep_time')->default(30)->after('min_order_amount');
            $table->boolean('require_phone_verification')->default(true)->after('estimated_prep_time');

            // Delivery Methods
            $table->boolean('allow_pickup')->default(true)->after('require_phone_verification');
            $table->boolean('allow_delivery')->default(false)->after('allow_pickup');
            $table->boolean('allow_dine_in')->default(false)->after('allow_delivery');
            $table->decimal('delivery_fee', 10, 2)->default(0.00)->after('allow_dine_in');
            $table->decimal('delivery_min_amount', 10, 2)->default(0.00)->after('delivery_fee');

            // Schedule (JSON: horarios de apertura)
            $table->json('digital_menu_schedule')->nullable()->after('delivery_min_amount');

            // Custom CSS for digital menu
            $table->text('digital_menu_custom_css')->nullable()->after('digital_menu_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn([
                'digital_menu_enabled',
                'whatsapp_number',
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
            ]);
        });
    }
};
