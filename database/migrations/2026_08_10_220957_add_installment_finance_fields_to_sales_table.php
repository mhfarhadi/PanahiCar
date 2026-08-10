<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('monthly_profit_rate', 5, 2)
                ->nullable()
                ->after('down_payment');

            $table->unsignedBigInteger('installment_profit')
                ->default(0)
                ->after('monthly_profit_rate');

            $table->unsignedBigInteger('contract_total')
                ->nullable()
                ->after('installment_profit');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_profit_rate',
                'installment_profit',
                'contract_total',
            ]);
        });
    }
};
