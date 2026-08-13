<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            $table->string('check_number', 50)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('sayad_id', 16)->nullable();
        });

        Schema::create('installment_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('installment_id')
                ->constrained('installments')
                ->cascadeOnDelete();

            $table->string('image_path');
            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['installment_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_images');

        Schema::table('installments', function (Blueprint $table) {
            $table->dropColumn([
                'check_number',
                'bank_name',
                'sayad_id',
            ]);
        });
    }
};
