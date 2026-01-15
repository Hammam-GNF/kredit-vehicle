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
        Schema::create('jadwal_angsuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kontrak_id')->constrained('kontrak')->cascadeOnDelete();

            $table->unsignedInteger('angsuran_ke');
            $table->decimal('angsuran_per_bulan', 15, 2);
            $table->date('tanggal_jatuh_tempo');

            $table->enum('status_pembayaran', ['PAID', 'UNPAID'])->default('UNPAID');
            $table->timestamps();

            $table->unique(['kontrak_id', 'angsuran_ke']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_angsuran');
    }
};
