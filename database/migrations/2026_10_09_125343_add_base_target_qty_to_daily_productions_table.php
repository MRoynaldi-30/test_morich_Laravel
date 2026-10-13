<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->unsignedInteger('base_target_qty')
                ->default(0)
                ->after('production_date');
        });

        // Untuk data lama, anggap target yang ada sebagai target dasar.
        DB::table('daily_productions')->update([
            'base_target_qty' => DB::raw('target_qty'),
        ]);
    }

    public function down(): void
    {
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->dropColumn('base_target_qty');
        });
    }
};
