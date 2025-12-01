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
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('restaurant_name');
            $table->string('restaurant_address')->nullable();
            $table->string('restaurant_phone')->nullable();
            $table->string('restaurant_email')->nullable();
            $table->string('restaurant_tax_id')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color')->default('#f97316');
            $table->string('secondary_color')->default('#ea580c');
            $table->string('accent_color')->default('#fb923c');
            $table->text('welcome_message')->nullable();
            $table->text('footer_message')->nullable();
            $table->string('currency')->default('MXN');
            $table->string('timezone')->default('America/Mexico_City');
            $table->json('social_media')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
