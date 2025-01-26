<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribution_order_id')->nullable();
            $table->string('order_no')->nullable();
            $table->unsignedBigInteger('company_id')->comment('Refer Supplier table type 1 data');
            $table->unsignedBigInteger('customer_id')->comment('Refer Supplier table type 3 data');
            $table->float('total',100,2);
            $table->float('paid',100,2);
            $table->float('due',100,2);
            $table->text('notes')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_orders');
    }
};
