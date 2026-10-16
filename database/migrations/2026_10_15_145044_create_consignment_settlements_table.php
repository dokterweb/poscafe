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
        Schema::create('consignment_settlements', function (Blueprint $table) {
            $table->id();

            $table->string('settlement_number', 50)->unique();

            $table->unsignedInteger('consignment_partner_id');

            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->decimal('total_amount', 15, 2)->default(0);

            $table->enum('status', [
                'draft',
                'paid',
            ])->default('draft');

            $table->string('payment_method', 30)->nullable();
            $table->string('payment_reference', 100)->nullable();

            $table->dateTime('paid_at')->nullable();

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->foreign('consignment_partner_id')
                ->references('id')
                ->on('consignment_partners')
                ->restrictOnDelete();

            $table->index(['consignment_partner_id', 'status']);
            $table->index(['period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_settlements');
    }
};
