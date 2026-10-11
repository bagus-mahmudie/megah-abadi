<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('image_url');     // path atau URL
            $table->string('alt')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete(); // jika gambar spesifik varian
            $table->timestamps();

            $table->index(['product_id','position']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('product_images');
    }
};
