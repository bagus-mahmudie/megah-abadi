<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_ratings_summary', function (Blueprint $table) {
            $table->foreignId('product_id')->primary()->constrained('products')->cascadeOnDelete();
            $table->decimal('avg_rating', 3, 1)->default(0);  // 4.9
            $table->unsignedInteger('total_reviews')->default(0);
            $table->unsignedInteger('count_5')->default(0);
            $table->unsignedInteger('count_4')->default(0);
            $table->unsignedInteger('count_3')->default(0);
            $table->unsignedInteger('count_2')->default(0);
            $table->unsignedInteger('count_1')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('product_ratings_summary');
    }
};
