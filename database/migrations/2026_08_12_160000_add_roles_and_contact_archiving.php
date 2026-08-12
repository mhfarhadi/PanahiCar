<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)
                ->default('staff')
                ->after('email')
                ->index();

            $table->boolean('is_active')
                ->default(true)
                ->after('role')
                ->index();
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->timestamp('archived_at')
                ->nullable()
                ->after('avatar_path')
                ->index();
        });

        // Existing installation owner becomes the first super admin.
        DB::table('users')
            ->where('id', DB::table('users')->min('id'))
            ->update([
                'role' => 'super_admin',
                'is_active' => true,
            ]);
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['archived_at']);
            $table->dropColumn('archived_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
