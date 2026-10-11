<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();

            $table->string('name');                  // "RESIN BENING 500 Gram + KATALIS 35 ML"
            $table->string('brand')->nullable();
            $table->string('sku')->nullable()->unique();

            $table->longText('description_html')->nullable(); // Deskripsi Produk
            $table->longText('spec_html')->nullable();        // Spesifikasi Produk

            $table->decimal('base_price', 12, 2)->default(0); // harga normal
            $table->decimal('discount_percent', 5, 2)->nullable(); // 30.00 untuk "30% off"
            $table->char('currency', 3)->default('IDR');

            $table->unsignedInteger('favorite_count')->default(0); // Favorit (5,4RB)
            $table->decimal('average_rating', 3, 1)->default(0);   // 4.9
            $table->unsignedInteger('rating_count')->default(0);   // jumlah ulasan

            $table->unsignedInteger('min_qty')->default(1);
            $table->unsignedInteger('max_qty')->nullable();
            $table->unsignedInteger('step_qty')->default(1);

            $table->unsignedInteger('weight_gram')->nullable(); // 45 gram
            $table->unsignedInteger('volume_ml')->nullable();   // 35 ml

            $table->enum('status', ['draft','active','archived'])->default('active');
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete(); // kalau multi-seller

            $table->timestamps();

            $table->index(['category_id','status']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('products');
    }
};
