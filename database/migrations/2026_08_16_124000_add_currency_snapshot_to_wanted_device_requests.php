<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wanted_device_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('usd_rate')
                ->nullable()
                ->after('max_price');

            $table->date('usd_rate_date')
                ->nullable()
                ->after('usd_rate');

            $table->string('usd_rate_source', 100)
                ->nullable()
                ->after('usd_rate_date');
        });
    }

    public function down(): void
    {
        Schema::table('wanted_device_requests', function (Blueprint $table) {
            $table->dropColumn([
                'usd_rate',
                'usd_rate_date',
                'usd_rate_source',
            ]);
        });
    }
};
