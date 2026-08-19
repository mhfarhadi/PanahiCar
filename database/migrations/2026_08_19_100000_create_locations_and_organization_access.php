<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('location_id')
                ->nullable()
                ->after('is_active')
                ->constrained('locations')
                ->nullOnDelete();
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->foreignId('location_id')
                ->nullable()
                ->after('created_by')
                ->constrained('locations')
                ->nullOnDelete();

            $table->index('location_id');
        });

        $now = now();
        $locationId = DB::table('locations')->insertGetId([
            'name' => 'شعبه مرکزی',
            'code' => 'main',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('users')->update([
            'location_id' => DB::raw("CASE WHEN role = 'super_admin' THEN NULL ELSE {$locationId} END"),
        ]);

        DB::table('devices')->update(['location_id' => $locationId]);

        DB::table('users')
            ->where('id', DB::table('users')->min('id'))
            ->update([
                'role' => 'super_admin',
                'is_active' => true,
                'location_id' => null,
            ]);
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
        });

        Schema::dropIfExists('locations');
    }
};
