<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wanted_device_requests', function (Blueprint $table) {
            $table->id();

            // Snapshot of the colleague/requester as entered publicly.
            $table->string('requester_name', 150);
            $table->string('requester_mobile', 20)->index();

            // Structured market-demand signal.
            $table->string('brand', 100)->index();
            $table->string('model', 150)->index();
            $table->string('storage', 50)->index();
            $table->string('color', 100)->nullable();
            $table->string('condition_grade', 50)->nullable();
            $table->string('registration_status', 50)->nullable();
            $table->unsignedTinyInteger('battery_health')->nullable();
            $table->string('battery_condition', 30)->nullable();

            // Real price expectation supplied by the colleague.
            $table->unsignedBigInteger('max_price')->index();

            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['brand', 'model', 'storage']);
            $table->index(['brand', 'model', 'storage', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wanted_device_requests');
    }
};
