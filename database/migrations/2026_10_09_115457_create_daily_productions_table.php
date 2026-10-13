<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::create('daily_productions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('production_schedule_id')
                ->constrained('production_schedules')
                ->cascadeOnDelete();

            $table->date('production_date');
            $table->unsignedInteger('target_qty');
            $table->unsignedInteger('actual_qty')->default(0);

            $table->timestamps();

            $table->unique([
                'production_schedule_id',
                'production_date',
            ]);

            $table->index('production_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_productions');
    }
};
