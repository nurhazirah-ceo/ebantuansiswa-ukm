<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // contoh: RAMADHAN20
            $table->decimal('bonus_amount', 10, 2); // contoh: 20.00
            $table->decimal('min_amount', 10, 2); // minimum sumbangan
            $table->boolean('is_active')->default(true);
            $table->timestamp('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_codes');
    }
};
