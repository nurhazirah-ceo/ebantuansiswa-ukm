<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_bantuans', function (Blueprint $table) {

            $table->id();

            $table->foreignId('permohonan_id')
                  ->constrained('permohonans')
                  ->onDelete('cascade');

            $table->string('jenis_bantuan');

            $table->json('data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_bantuans');
    }
};