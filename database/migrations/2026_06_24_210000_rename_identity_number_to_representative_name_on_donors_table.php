<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            if (Schema::hasColumn('donors', 'identity_number') && ! Schema::hasColumn('donors', 'representative_name')) {
                $table->renameColumn('identity_number', 'representative_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            if (Schema::hasColumn('donors', 'representative_name') && ! Schema::hasColumn('donors', 'identity_number')) {
                $table->renameColumn('representative_name', 'identity_number');
            }
        });
    }
};
