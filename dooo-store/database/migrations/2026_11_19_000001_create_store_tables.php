<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 100)->unique();
            $table->string('description', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('design_no', 20)->unique();
            $table->string('name', 160);
            $table->string('slug', 190)->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('size_type', 10)->default('alpha'); // waist | alpha
            $table->json('colours')->nullable();
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('price_large')->nullable();
            $table->unsignedInteger('compare_price')->nullable();
            $table->boolean('price_from')->default(false);
            $table->string('tag', 30)->nullable();
            $table->boolean('is_signature')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('in_stock')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'product_id']);
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('phone', 20)->unique();
            $table->string('alt_phone', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('district', 40)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 20)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 120);
            $table->string('phone', 20);
            $table->string('alt_phone', 20)->nullable();
            $table->string('address', 255);
            $table->string('city', 80);
            $table->string('district', 40);
            $table->text('notes')->nullable();
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('items_tbc')->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('payment_status', 20)->default('unpaid');
            $table->string('payment_method', 20)->default('cod');
            $table->string('tracking_no', 80)->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('source', 20)->default('website');
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('design_no', 20);
            $table->string('name', 160);
            $table->string('size', 10);
            $table->string('colour', 40)->nullable();
            $table->unsignedInteger('qty');
            $table->unsignedInteger('unit_price')->nullable();
            $table->unsignedInteger('line_total')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('phone', 30)->nullable();
            $table->string('topic', 60)->nullable();
            $table->string('order_ref', 60)->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('settings');
    }
};
