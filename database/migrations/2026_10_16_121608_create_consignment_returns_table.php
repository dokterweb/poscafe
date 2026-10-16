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
        Schema::create('consignment_returns', function (Blueprint $table) {
            $table->id();

            $table->string('return_number', 50)->unique();

            $table->unsignedInteger('consignment_partner_id');

            $table->date('return_date');

            $table->enum('return_type', [
                'stock_return',
                'sales_return',
            ]);

            $table->string('reason', 100);

            $table->enum('financial_treatment', [
                'no_adjustment',
                'deduct_from_draft',
                'credit_next_settlement',
                'partner_responsibility',
            ])->default('no_adjustment');

            $table->enum('status', [
                'draft',
                'processed',
                'cancelled',
            ])->default('draft');

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->dateTime('processed_at')->nullable();

            $table->timestamps();

            $table->foreign('consignment_partner_id')
                ->references('id')
                ->on('consignment_partners')
                ->restrictOnDelete();

            $table->index([
                'consignment_partner_id',
                'status',
            ]);

            $table->index([
                'return_date',
                'return_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_returns');
    }
};
