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
        $table->foreignId('category_id')->constrained()->restrictOnDelete();
        $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description');
        $table->integer('price'); // Prix en FCFA (Entier)
        $table->integer('stock')->default(0);
        $table->json('images')->nullable(); 
        $table->json('technical_specs')->nullable(); 
        $table->boolean('is_published')->default(true);
        $table->timestamps();
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
