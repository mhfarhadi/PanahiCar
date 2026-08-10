<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('contact_type', 20)
                ->default('individual')
                ->after('description')
                ->index();
        });

        DB::table('contacts')
            ->whereIn(
                'id',
                DB::table('devices')
                    ->whereNotNull('announced_by_id')
                    ->select('announced_by_id')
            )
            ->update([
                'contact_type' => 'colleague',
            ]);
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['contact_type']);
            $table->dropColumn('contact_type');
        });
    }
};
