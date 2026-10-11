<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->onDelete('cascade');

            // referensi dari Biteship
            $table->string('biteship_order_id')->unique(); // id order Biteship
            $table->string('courier_tracking_code')->nullable(); // tracking code Biteship
            $table->string('waybill_id')->nullable(); // nomor resi (kalau berbeda)
            $table->string('status')->default('pending'); // pending, confirmed, picked_up, in_transit, delivered, cancelled, returned, dll
            $table->string('raw_status')->nullable();     // status mentah dari Biteship

            $table->string('courier_company')->nullable(); // JNE, J&T, dll
            $table->string('courier_service')->nullable(); // REG, YES, dll

            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->string('label_url')->nullable(); // kalau Biteship ada shipping label
            $table->json('raw_payload')->nullable(); // simpan data order/tracking Biteship mentah

            $table->timestamps();

            $table->index(['courier_tracking_code']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
