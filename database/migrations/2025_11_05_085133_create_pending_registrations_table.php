<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('pending_registrations', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->unique();
        $table->string('password');               // sudah di-hash
        $table->string('token', 64)->unique();    // pelengkap/keamanan
        $table->timestamps();
    });
}
public function down(){ Schema::dropIfExists('pending_registrations'); }

};
