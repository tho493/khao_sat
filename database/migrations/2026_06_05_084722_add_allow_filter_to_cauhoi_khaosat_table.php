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
        Schema::table('cauhoi_khaosat', function (Blueprint $table) {
            $table->tinyInteger('allow_filter')->default(0)->after('is_personal_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cauhoi_khaosat', function (Blueprint $table) {
            $table->dropColumn('allow_filter');
        });
    }
};
