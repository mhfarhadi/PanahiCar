<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('usd_rate')->nullable()->after('purchase_date');
            $table->date('usd_rate_date')->nullable()->after('usd_rate');
            $table->string('usd_rate_source', 100)->nullable()->after('usd_rate_date');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'usd_rate',
                'usd_rate_date',
                'usd_rate_source',
            ]);
        });
    }
};
