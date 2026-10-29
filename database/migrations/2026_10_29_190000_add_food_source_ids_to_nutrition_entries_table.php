<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where an entry's food came from, so editing can load its serving sizes:
     * fdc_id for USDA FoodData Central search results, barcode for scanned
     * Open Food Facts products. Both are null for older entries.
     */
    public function up(): void
    {
        Schema::table('nutrition_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('fdc_id')->nullable()->after('user_id');
            $table->string('barcode', 32)->nullable()->after('fdc_id');
        });
    }

    public function down(): void
    {
        Schema::table('nutrition_entries', function (Blueprint $table) {
            $table->dropColumn(['fdc_id', 'barcode']);
        });
    }
};
