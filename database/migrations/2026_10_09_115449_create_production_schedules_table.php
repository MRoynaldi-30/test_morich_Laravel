<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sewing_line_id')
                ->constrained('sewing_lines')
                ->restrictOnDelete();

            $table->foreignId('production_order_id')
                ->constrained('production_orders')
                ->restrictOnDelete();

            $table->date('start_date');
            $table->date('finish_date');
            $table->unsignedInteger('total_target');

            $table->string('status', 20)->default('planned');

            $table->timestamps();

            $table->index(['sewing_line_id', 'start_date']);
            $table->index(['sewing_line_id', 'finish_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_schedules');
    }
};
