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
        Schema::create('nutrition_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->string('meal_type');
            $table->string('food_item');
            $table->integer('calories');
            $table->decimal('protein', 5, 2);
            $table->decimal('carbohydrates', 5, 2);
            $table->decimal('fats', 5, 2);
            $table->string('serving_size')->nullable();
            $table->decimal('quantity', 5, 2)->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrition_entries');
    }
};
