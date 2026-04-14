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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            //foreign keys
            $table->foreignId('buyer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('restrict');
            $table->foreignId('seller_id')->constrained('users')->onDelete('restrict');

            //order details
            $table->decimal('amount', 10, 2);
            $table->decimal('tax', 10, 2)->nullable();
            $table->decimal('total', 10, 2);

            // status
            $table->enum('status', [
                'pending',
                'payment_processing',
                'payment_completed',
                'completed',
                'cancelled',
                'refunded'
            ])->default('pending');

            // payment info
            $table->string('payment_method')->nullable();
            $table->string('payment_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();

            // shipping info
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // indexes
            $table->index('order_number');
            $table->index('buyer_id');
            $table->index('vehicle_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
