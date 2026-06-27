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
        Schema::create('permohonans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('no_kelompok');
            $table->date('tarikh_mohon');
            $table->string('jenis_bantuan');
            $table->string('status');
            $table->text('catatan')->nullable();

            // Keperluan Asas
            $table->string('pakej')->nullable();
            $table->integer('jumlah_ahli')->nullable();

            // Pembelajaran
            $table->string('nama_group')->nullable();
            $table->integer('bilangan_ahli')->nullable();

            // Peralatan Sukan
            $table->string('kategori')->nullable();
            $table->string('organisasi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonans');
    }
};