<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $foreignKey = 'purchases_seller_id_post_contacts_foreign';

    public function up(): void
    {
        if (
            Schema::hasTable('purchases')
            && Schema::hasTable('contacts')
            && Schema::hasColumn('purchases', 'seller_id')
            && ! Schema::hasForeignKey('purchases', ['seller_id'])
        ) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->foreign('seller_id', $this->foreignKey)
                    ->references('id')
                    ->on('contacts')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('purchases')
            && Schema::hasForeignKey('purchases', $this->foreignKey)
        ) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropForeign($this->foreignKey);
            });
        }
    }
};
