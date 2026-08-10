<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('device_id')
                ->unique()
                ->constrained('devices')
                ->restrictOnDelete();

            $table->foreignId('buyer_id')
                ->constrained('contacts')
                ->restrictOnDelete();

            $table->string('sale_type', 20)->default('cash');
            $table->unsignedBigInteger('sale_price');
            $table->unsignedBigInteger('down_payment')->default(0);
            $table->date('sale_date');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index('sale_type');
            $table->index('sale_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
