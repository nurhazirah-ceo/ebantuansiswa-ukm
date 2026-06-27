<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (! Schema::hasColumn('permohonans', 'tarikh_agihan')) {
                $table->timestamp('tarikh_agihan')->nullable()->after('status_agihan');
            }

            if (! Schema::hasColumn('permohonans', 'catatan_agihan')) {
                $table->text('catatan_agihan')->nullable()->after('tarikh_agihan');
            }

            if (! Schema::hasColumn('permohonans', 'diagih_oleh')) {
                $table->foreignId('diagih_oleh')
                    ->nullable()
                    ->after('catatan_agihan')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (Schema::hasColumn('permohonans', 'diagih_oleh')) {
                $table->dropConstrainedForeignId('diagih_oleh');
            }

            if (Schema::hasColumn('permohonans', 'catatan_agihan')) {
                $table->dropColumn('catatan_agihan');
            }

            if (Schema::hasColumn('permohonans', 'tarikh_agihan')) {
                $table->dropColumn('tarikh_agihan');
            }
        });
    }
};
