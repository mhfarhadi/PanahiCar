<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_model_part_number_option', function (Blueprint $table) {
            $table->id();

            $table->foreignId('device_model_id')
                ->constrained('device_models')
                ->cascadeOnDelete();

            $table->foreignId('part_number_option_id')
                ->constrained('part_number_options')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['device_model_id', 'part_number_option_id'],
                'model_part_number_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_model_part_number_option');
    }
};
