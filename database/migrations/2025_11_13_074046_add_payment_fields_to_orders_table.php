<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_snap_token')->nullable()->after('payment_transaction_id');
            $table->string('payment_redirect_url')->nullable()->after('payment_snap_token');
            $table->timestamp('payment_expired_at')->nullable()->after('payment_redirect_url');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_snap_token',
                'payment_redirect_url',
                'payment_expired_at',
            ]);
        });
    }
};
