<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('battery_condition', 30)
                ->nullable()
                ->after('battery_health');

            $table->string('manufacturing_country', 50)
                ->nullable()
                ->after('part_number');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'battery_condition',
                'manufacturing_country',
            ]);
        });
    }
};
