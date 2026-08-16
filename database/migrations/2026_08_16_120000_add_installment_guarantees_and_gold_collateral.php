<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('guarantee_type', 20)
                ->nullable()
                ->after('sale_type');

            $table->index('guarantee_type');
        });

        DB::table('sales')
            ->where('sale_type', 'installment')
            ->whereNull('guarantee_type')
            ->update([
                'guarantee_type' => 'check',
            ]);

        Schema::create('gold_rates', function (Blueprint $table) {
            $table->id();
            $table->string('item', 50)->default('18ayar');
            $table->unsignedBigInteger('rate_per_gram');
            $table->date('rate_date');
            $table->string('source', 50)->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['item', 'rate_date']);
            $table->index('rate_date');
        });

        Schema::create('sale_gold_collaterals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->unique()
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('base_principal');
            $table->unsignedSmallInteger('coverage_months')->default(2);
            $table->decimal('monthly_profit_rate', 5, 2);
            $table->unsignedBigInteger('coverage_profit');
            $table->unsignedBigInteger('coverage_amount');

            $table->string('gold_rate_item', 50)->default('18ayar');
            $table->unsignedBigInteger('gold_rate_per_gram');
            $table->date('gold_rate_date')->nullable();
            $table->string('gold_rate_source', 50)->nullable();

            $table->unsignedSmallInteger('gold_karat')->default(18);
            $table->decimal('required_weight', 12, 4);
            $table->decimal('received_weight', 12, 4);

            $table->string('gold_type', 100);
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_gold_collaterals');
        Schema::dropIfExists('gold_rates');

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['guarantee_type']);
            $table->dropColumn('guarantee_type');
        });
    }
};
