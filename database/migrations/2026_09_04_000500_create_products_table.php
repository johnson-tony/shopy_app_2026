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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mode_id')->constrained('modes')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Performant query indexes
            $table->index(['mode_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['featured', 'status']);
            $table->index(['status', 'sort_order']);
            $table->index(['sort_order', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
