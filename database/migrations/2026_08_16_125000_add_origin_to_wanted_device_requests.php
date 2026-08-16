<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wanted_device_requests', function (Blueprint $table) {
            $table->string('origin', 30)
                ->default('organic')
                ->after('requester_mobile');

            $table->string('market_reference_source', 50)
                ->nullable()
                ->after('origin');

            $table->index('origin');
        });
    }

    public function down(): void
    {
        Schema::table('wanted_device_requests', function (Blueprint $table) {
            $table->dropIndex(['origin']);
            $table->dropColumn([
                'origin',
                'market_reference_source',
            ]);
        });
    }
};
