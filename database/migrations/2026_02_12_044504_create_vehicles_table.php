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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            // VEHICLE IDENTITY
            $table->string('vin', 17)->unique();
            $table->string('make', 50);
            $table->string('model', 50);
            $table->integer('year');
            $table->string('color', 30);
            $table->integer('mileage');

            // PRICING
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();

            // STATUS
            $table->enum('status', [
                'draft',
                'listed',
                'reserved',
                'sold',
                'archived'
            ])->default('draft');

            $table->boolean('is_featured')->default(false);

            // DETAILS
            $table->text('description');
            $table->json('features')->nullable();
            $table->json('images')->nullable();

            // RELATIONS
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');

            // SEO
            $table->string('slug')->unique()->nullable();


            // TIMESTAMPS
            $table->timestamp('listed_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // INDEXES
            $table->index(['make', 'model', 'year']);
            $table->index('price');
            $table->index('seller_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
