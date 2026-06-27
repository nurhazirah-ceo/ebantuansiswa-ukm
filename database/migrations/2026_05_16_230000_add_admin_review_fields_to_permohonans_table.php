<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (! Schema::hasColumn('permohonans', 'admin_catatan')) {
                $table->text('admin_catatan')->nullable()->after('catatan');
            }

            if (! Schema::hasColumn('permohonans', 'admin_review_date')) {
                $table->timestamp('admin_review_date')->nullable()->after('admin_catatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (Schema::hasColumn('permohonans', 'admin_review_date')) {
                $table->dropColumn('admin_review_date');
            }

            if (Schema::hasColumn('permohonans', 'admin_catatan')) {
                $table->dropColumn('admin_catatan');
            }
        });
    }
};
