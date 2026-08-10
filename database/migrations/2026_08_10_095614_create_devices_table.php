<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();

            // مشخصات اصلی دستگاه
            $table->string('brand', 100);
            $table->string('model', 150);
            $table->string('storage', 50)->nullable();
            $table->string('color', 100)->nullable();
            $table->string('part_number', 100)->nullable();

            // وضعیت فنی
            $table->unsignedTinyInteger('battery_health')->nullable();
            $table->string('condition_grade', 50)->nullable();
            $table->string('imei', 20)->nullable()->unique();
            $table->string('registration_status', 50)->nullable();

            // وضعیت دستگاه در سیستم
            $table->string('status', 30)->default('in_stock');

            // توضیحات تکمیلی
            $table->text('description')->nullable();

            // کاربری که دستگاه را ثبت کرده
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // برای جستجوهای پرتکرار
            $table->index(['brand', 'model']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
