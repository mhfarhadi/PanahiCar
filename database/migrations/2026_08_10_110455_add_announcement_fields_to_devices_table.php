<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->foreignId('announced_by_id')
                ->nullable()
                ->after('status')
                ->constrained('contacts')
                ->nullOnDelete();

            $table->unsignedBigInteger('announced_price')
                ->nullable()
                ->after('announced_by_id');

            $table->date('announced_at')
                ->nullable()
                ->after('announced_price');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['announced_by_id']);
            $table->dropColumn([
                'announced_by_id',
                'announced_price',
                'announced_at',
            ]);
        });
    }
};
