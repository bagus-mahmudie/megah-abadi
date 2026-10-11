<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // optional, supaya tahu order ini berasal dari cart mana
            $table->foreignId('cart_id')
                ->nullable()
                ->constrained('carts')
                ->nullOnDelete();

            $table->string('code')->unique(); // mis: MA-20251113-0001

            // summary
            $table->unsignedInteger('total_items')->default(0);
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->char('currency', 3)->default('IDR');

            // status order & payment
            $table->enum('status', [
                'pending',      // baru dibuat, menunggu pembayaran
                'awaiting_shipment', // sudah bayar, belum dikirim
                'shipped',
                'completed',
                'cancelled',
            ])->default('pending');

            $table->enum('payment_status', [
                'unpaid',
                'paid',
                'failed',
                'refunded',
            ])->default('unpaid');

            $table->string('payment_provider')->nullable(); // 'midtrans'
            $table->string('payment_transaction_id')->nullable(); // order_id Midtrans
            $table->json('payment_payload')->nullable(); // simpan payload response Midtrans

            // info shipping provider
            $table->string('shipping_provider')->nullable();      // 'biteship'
            $table->string('shipping_courier_code')->nullable();  // mis: 'jne-reg'
            $table->string('shipping_courier_name')->nullable();  // mis: 'JNE Reguler'
            $table->string('shipping_etd')->nullable();           // estimasi waktu (H+? dsb)

            // alamat penerima (disimpan snapshot di order)
            $table->string('receiver_name');
            $table->string('receiver_phone', 32)->nullable();
            $table->string('receiver_email')->nullable();

            $table->string('receiver_address_line1');
            $table->string('receiver_address_line2')->nullable();
            $table->string('receiver_postal_code', 16)->nullable();

            // detail area (bisa diisi dari location Biteship)
            $table->string('receiver_country_code', 3)->default('ID');
            $table->string('receiver_province')->nullable();
            $table->string('receiver_city')->nullable();
            $table->string('receiver_city_id')->nullable();
            $table->string('receiver_district')->nullable();
            $table->string('receiver_district_id')->nullable();

            $table->decimal('receiver_latitude', 10, 7)->nullable();
            $table->decimal('receiver_longitude', 10, 7)->nullable();

            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
