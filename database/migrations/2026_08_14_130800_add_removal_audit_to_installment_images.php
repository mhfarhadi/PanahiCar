<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_images', function (Blueprint $table) {
            $table->timestamp('removed_at')->nullable()->after('uploaded_by');

            $table->foreignId('removed_by')
                ->nullable()
                ->after('removed_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('removal_reason')->nullable()->after('removed_by');

            $table->index(['installment_id', 'removed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('installment_images', function (Blueprint $table) {
            $table->dropIndex(['installment_id', 'removed_at']);
            $table->dropConstrainedForeignId('removed_by');
            $table->dropColumn([
                'removed_at',
                'removal_reason',
            ]);
        });
    }
};
