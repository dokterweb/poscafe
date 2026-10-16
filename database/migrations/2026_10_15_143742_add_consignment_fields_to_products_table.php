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
        Schema::table('products', function (Blueprint $table) {
             $table->enum('product_type', [
                'regular',
                'consignment',
            ])->default('regular')->after('price');

            $table->unsignedInteger('consignment_partner_id')
                ->nullable()
                ->after('product_type');

            $table->decimal('partner_price', 15, 2)
                ->nullable()
                ->after('consignment_partner_id');

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
        Schema::table('products', function (Blueprint $table) {
              $table->dropForeign(['consignment_partner_id']);

            $table->dropColumn([
                'product_type',
                'consignment_partner_id',
                'partner_price',
            ]);
        });
    }
};
