<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->string('sku')->unique();
            $table->json('options');                         // {"color":"Coklat","size_ml":35}
            $table->decimal('price', 12, 2)->nullable();     // override harga jika perlu
            $table->integer('stock')->default(0);
            $table->unsignedInteger('weight_gram')->nullable();
            $table->unsignedInteger('volume_ml')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['product_id','is_active']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('product_variants');
    }
};
