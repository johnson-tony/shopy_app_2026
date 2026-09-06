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
        // 1. Create Restaurants Table
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('cuisine')->default('Biryani, South Indian, Tandoori');
            $table->decimal('rating', 3, 1)->default(4.5);
            $table->unsignedInteger('ratings_count')->default(250);
            $table->string('delivery_time')->default('30-40 mins');
            $table->unsignedInteger('cost_for_two')->default(400);
            $table->string('address')->default('Main Road');
            $table->string('city')->default('Chennai');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_pure_veg')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['status', 'is_featured']);
            $table->index('slug');
        });

        // 2. Add restaurant_id, addons, and is_veg to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('category_id')->constrained('restaurants')->nullOnDelete();
            $table->json('addons')->nullable()->after('sizes');
            $table->boolean('is_veg')->default(false)->nullable()->after('return_policy');

            $table->index('restaurant_id');
        });

        // 3. Add selected_addons to cart_items
        Schema::table('cart_items', function (Blueprint $table) {
            $table->json('selected_addons')->nullable()->after('size');
        });

        // 4. Add selected_addons and restaurant_name to order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('restaurant_name')->nullable()->after('product_slug');
            $table->json('selected_addons')->nullable()->after('size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['restaurant_name', 'selected_addons']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('selected_addons');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn(['restaurant_id', 'addons', 'is_veg']);
        });

        Schema::dropIfExists('restaurants');
    }
};
