<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_model_color_option', function (Blueprint $table) {
            $table->id();

            $table->foreignId('device_model_id')
                ->constrained('device_models')
                ->cascadeOnDelete();

            $table->foreignId('color_option_id')
                ->constrained('color_options')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['device_model_id', 'color_option_id'],
                'model_color_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_model_color_option');
    }
};
