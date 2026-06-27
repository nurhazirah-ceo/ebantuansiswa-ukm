<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sumbangan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sumbangan_id')->constrained('sumbangans')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->string('nama_item');
            $table->string('kategori_bantuan');
            $table->decimal('harga_unit', 10, 2)->default(0);
            $table->unsignedInteger('kuantiti')->default(1);
            $table->decimal('jumlah', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sumbangan_items');
    }
};
