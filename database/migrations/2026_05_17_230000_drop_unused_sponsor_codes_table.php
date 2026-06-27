<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sponsor_codes');
    }

    public function down(): void
    {
        // Intentionally left blank. The retired table is not restored.
    }
};
