<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 10);
            $table->unsignedBigInteger('rate');
            $table->date('rate_date');
            $table->string('source', 50)->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['currency', 'rate_date']);
            $table->index('rate_date');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('usd_rate')->nullable()->after('sale_date');
            $table->date('usd_rate_date')->nullable()->after('usd_rate');
            $table->string('usd_rate_source', 50)->nullable()->after('usd_rate_date');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'usd_rate',
                'usd_rate_date',
                'usd_rate_source',
            ]);
        });

        Schema::dropIfExists('exchange_rates');
    }
};
