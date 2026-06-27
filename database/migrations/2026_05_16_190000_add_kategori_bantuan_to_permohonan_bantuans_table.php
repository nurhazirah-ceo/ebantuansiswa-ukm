<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('permohonan_bantuans', 'kategori_bantuan')) {
            Schema::table('permohonan_bantuans', function (Blueprint $table) {
                $table->string('kategori_bantuan')->nullable()->after('jenis_bantuan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('permohonan_bantuans', 'kategori_bantuan')) {
            Schema::table('permohonan_bantuans', function (Blueprint $table) {
                $table->dropColumn('kategori_bantuan');
            });
        }
    }
};
