<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            if (! Schema::hasColumn('donors', 'alt_phone')) {
                $table->string('alt_phone', 30)->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            if (Schema::hasColumn('donors', 'alt_phone')) {
                $table->dropColumn('alt_phone');
            }
        });
    }
};
