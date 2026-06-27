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
    Schema::create('permohonan_pelajar', function (Blueprint $table) {

        $table->id();

        $table->foreignId('permohonan_id')
              ->constrained('permohonans')
              ->onDelete('cascade');

        $table->string('nama_penuh');
        $table->string('no_matrik');
        $table->string('email_ukm');
        $table->string('no_telefon');
        $table->string('fakulti');
        $table->string('kursus');

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('permohonan_pelajar');
}
};
