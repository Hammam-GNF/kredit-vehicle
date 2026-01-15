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
        Schema::create('kontrak', function (Blueprint $table) {
            $table->id();
            $table->string('kontrak_no')->unique();
            $table->string('client_name');

            $table->decimal('otr', 15, 2);
            $table->decimal('dp', 15, 2);
            $table->decimal('pokok_utang', 15, 2);

            $table->unsignedInteger('jangka_waktu');
            $table->decimal('bunga', 5, 2);

            $table->decimal('total_utang', 15, 2);
            $table->decimal('angsuran_per_bulan', 15, 2);
            
            $table->date('start_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrak');
    }
};
