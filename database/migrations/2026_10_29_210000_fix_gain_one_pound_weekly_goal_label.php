<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Weekly goal 7 adds 500 calories a day (gain 1 lb per week), but the
     * seeder labeled it "Lose 1 lb per week", so users picking it to lose
     * weight got a calorie surplus.
     */
    public function up(): void
    {
        DB::table('weekly_goals')->where('id', 7)->update(['name' => 'Gain 1 lb per week']);
    }

    public function down(): void
    {
        DB::table('weekly_goals')->where('id', 7)->update(['name' => 'Lose 1 lb per week']);
    }
};
