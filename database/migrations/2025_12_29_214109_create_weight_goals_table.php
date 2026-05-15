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
        Schema::create('weight_goals', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->date('start_date');
            $table->decimal('start_weight', 5, 2);
            $table->decimal('current_weight', 5, 2);
            $table->decimal('goal_weight', 5, 2);
            $table->unsignedBigInteger('weekly_goal_id');
            $table->unsignedBigInteger('activity_level_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_goals');
    }
};
