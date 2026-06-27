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
    Schema::create('addresses', function (Blueprint $table) {
        $table->id();

        // Link ke donors table
        $table->foreignId('donor_id')
              ->constrained()
              ->onDelete('cascade');

        // Maklumat alamat
        $table->string('address_line_1');
        $table->string('address_line_2')->nullable();
        $table->string('city');
        $table->string('postcode', 10);
        $table->string('state');
        $table->string('country')->default('Malaysia');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
