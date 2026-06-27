<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sumbangans', function (Blueprint $table) {
            if (! Schema::hasColumn('sumbangans', 'donor_snapshot')) {
                $table->json('donor_snapshot')->nullable()->after('catatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sumbangans', function (Blueprint $table) {
            if (Schema::hasColumn('sumbangans', 'donor_snapshot')) {
                $table->dropColumn('donor_snapshot');
            }
        });
    }
};
