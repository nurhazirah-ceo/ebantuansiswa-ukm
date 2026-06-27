<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sumbangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('no_sumbangan')->nullable()->unique();
            $table->unsignedInteger('jumlah_unit')->default(0);
            $table->decimal('jumlah_keseluruhan', 12, 2)->default(0);
            $table->string('status')->default('selesai');
            $table->string('kaedah_sumbangan')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sumbangans');
    }
};
