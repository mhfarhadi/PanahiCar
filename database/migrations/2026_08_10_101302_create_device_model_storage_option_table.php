<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_model_storage_option', function (Blueprint $table) {
            $table->id();

            $table->foreignId('device_model_id')
                ->constrained('device_models')
                ->cascadeOnDelete();

            $table->foreignId('storage_option_id')
                ->constrained('storage_options')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['device_model_id', 'storage_option_id'],
                'model_storage_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_model_storage_option');
    }
};
