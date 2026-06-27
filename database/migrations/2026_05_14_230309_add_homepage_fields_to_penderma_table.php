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
        Schema::table('donors', function (Blueprint $table) {
            $table->string('logo')->nullable();
            $table->string('homepage_label')->nullable();
            $table->integer('homepage_order')->default(0);
            $table->boolean('show_on_homepage')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            $table->dropColumn([
                'logo',
                'homepage_label',
                'homepage_order',
                'show_on_homepage',
            ]);
        });
    }
};