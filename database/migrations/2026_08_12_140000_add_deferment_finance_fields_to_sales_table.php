<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->date('standard_first_due_date')
                ->nullable()
                ->after('contract_total');

            $table->date('first_due_date')
                ->nullable()
                ->after('standard_first_due_date');

            $table->unsignedSmallInteger('deferment_months')
                ->default(0)
                ->after('first_due_date');

            $table->unsignedSmallInteger('deferment_days')
                ->default(0)
                ->after('deferment_months');

            $table->unsignedBigInteger('deferment_profit')
                ->default(0)
                ->after('deferment_days');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'standard_first_due_date',
                'first_due_date',
                'deferment_months',
                'deferment_days',
                'deferment_profit',
            ]);
        });
    }
};
