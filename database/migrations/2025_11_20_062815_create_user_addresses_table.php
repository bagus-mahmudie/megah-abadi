<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // label bebas: "Rumah", "Kantor", dll
            $table->string('label')->nullable();

            // pakai nama field yang sama dengan tabel orders
            $table->string('receiver_name');
            $table->string('receiver_phone', 32)->nullable();
            $table->string('receiver_email')->nullable();

            $table->string('receiver_address_line1');   // nama jalan, gedung, no rumah
            $table->string('receiver_address_line2')->nullable(); // patokan dll

            $table->string('receiver_postal_code', 16)->nullable();
            $table->string('receiver_country_code', 3)->default('ID');

            $table->string('receiver_province')->nullable();
            $table->string('receiver_city')->nullable();
            $table->string('receiver_city_id')->nullable();
            $table->string('receiver_district')->nullable();
            $table->string('receiver_district_id')->nullable();

            $table->decimal('receiver_latitude', 10, 7)->nullable();
            $table->decimal('receiver_longitude', 10, 7)->nullable();

            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
