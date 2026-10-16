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
        Schema::table('order_items', function (Blueprint $table) {
              $table->unsignedInteger('consignment_partner_id')
                ->nullable();

            $table->string('consignment_partner_name', 150)
                ->nullable();

            $table->decimal('partner_unit_price', 15, 2)
                ->nullable();

            $table->decimal('partner_amount', 15, 2)
                ->default(0);

            $table->decimal('cafe_profit', 15, 2)
                ->default(0);

            $table->foreign('consignment_partner_id')
                ->references('id')
                ->on('consignment_partners')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
             $table->dropForeign(['consignment_partner_id']);

            $table->dropColumn([
                'consignment_partner_id',
                'consignment_partner_name',
                'partner_unit_price',
                'partner_amount',
                'cafe_profit',
            ]);
        });
    }
};
