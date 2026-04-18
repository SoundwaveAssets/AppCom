<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'firebase_uid')) {
                $table->string('firebase_uid')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'user'])->default('user')->after('email');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete()->after('id');
            }
            if (! Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });

        Schema::table('brands', function (Blueprint $table) {
            if (! Schema::hasColumn('brands', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('slug');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'promo_price')) {
                $table->integer('promo_price')->nullable()->after('price');
            }
            if (! Schema::hasColumn('products', 'weight')) {
                $table->decimal('weight', 10, 2)->nullable()->after('promo_price');
            }
            if (! Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('technical_specs');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'guest_session_id')) {
                $table->string('guest_session_id')->nullable()->after('user_id');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'order_id')) {
                $table->foreignId('order_id')->constrained()->cascadeOnDelete()->after('id');
            }
            if (! Schema::hasColumn('order_items', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete()->after('order_id');
            }
            if (! Schema::hasColumn('order_items', 'product_name')) {
                $table->string('product_name')->after('product_id');
            }
            if (! Schema::hasColumn('order_items', 'unit_price')) {
                $table->integer('unit_price')->after('product_name');
            }
            if (! Schema::hasColumn('order_items', 'quantity')) {
                $table->integer('quantity')->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        // Intentionally left minimal for safety in existing projects.
    }
};
