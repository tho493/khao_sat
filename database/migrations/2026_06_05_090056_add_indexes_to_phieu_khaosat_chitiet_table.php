<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('phieu_khaosat_chitiet', function (Blueprint $table) {
            $table->index(['cauhoi_id', 'phuongan_id'], 'idx_filter_choice');
            $table->index(['cauhoi_id', 'giatri_number'], 'idx_filter_number');
        });

        // MySQL TEXT column prefix index needs DB statement for length limit
        DB::statement('CREATE INDEX idx_filter_text ON phieu_khaosat_chitiet(cauhoi_id, giatri_text(191))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phieu_khaosat_chitiet', function (Blueprint $table) {
            $table->dropIndex('idx_filter_choice');
            $table->dropIndex('idx_filter_number');
            $table->dropIndex('idx_filter_text');
        });
    }
};
