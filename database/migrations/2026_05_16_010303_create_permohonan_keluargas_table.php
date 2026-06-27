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
        Schema::create('permohonan_keluarga', function (Blueprint $table) {

            $table->id();

            $table->foreignId('permohonan_id')
                  ->constrained('permohonans')
                  ->onDelete('cascade');

            // penjaga / tanggungan
            $table->string('jenis');

            $table->string('nama');
            $table->string('no_kp')->nullable();
            $table->string('hubungan');

            $table->string('telefon')->nullable();
            $table->string('pekerjaan')->nullable();

            $table->integer('umur')->nullable();

            $table->string('status')->nullable();
            $table->string('kesihatan')->nullable();

            $table->decimal('pendapatan', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_keluarga');
    }
};