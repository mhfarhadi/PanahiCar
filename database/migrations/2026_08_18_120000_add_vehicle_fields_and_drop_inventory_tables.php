<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->unsignedSmallInteger('model_year')->nullable()->after('model');
            $table->unsignedInteger('mileage')->nullable()->after('model_year');
            $table->string('transmission', 20)->nullable()->after('color');
            $table->string('fuel_type', 20)->nullable()->after('transmission');
            $table->unsignedTinyInteger('insurance_months')->nullable()->after('fuel_type');
            $table->string('body_condition', 30)->nullable()->after('insurance_months');
            $table->string('vin', 30)->nullable()->unique()->after('body_condition');
        });

        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_levels');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropUnique(['vin']);
            $table->dropColumn([
                'model_year',
                'mileage',
                'transmission',
                'fuel_type',
                'insurance_months',
                'body_condition',
                'vin',
            ]);
        });
    }
};
