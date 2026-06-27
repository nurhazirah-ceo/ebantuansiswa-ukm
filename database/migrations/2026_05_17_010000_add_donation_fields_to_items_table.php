<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'kategori_bantuan')) {
                $table->string('kategori_bantuan')->nullable()->after('kategori');
            }

            if (! Schema::hasColumn('items', 'harga')) {
                $table->decimal('harga', 10, 2)->default(0)->after('kategori_bantuan');
            }

            if (! Schema::hasColumn('items', 'imej')) {
                $table->string('imej')->nullable()->after('harga');
            }

            if (! Schema::hasColumn('items', 'susunan')) {
                $table->unsignedInteger('susunan')->default(0)->after('status');
            }
        });

        DB::table('items')
            ->where('kategori', 'keperluan')
            ->where(function ($query) {
                $query->whereNull('kategori_bantuan')
                    ->orWhere('kategori_bantuan', '');
            })
            ->update(['kategori_bantuan' => 'keperluan_asas']);

        foreach (['pembelajaran', 'sukan'] as $kategori) {
            DB::table('items')
                ->where('kategori', $kategori)
                ->where(function ($query) {
                    $query->whereNull('kategori_bantuan')
                        ->orWhere('kategori_bantuan', '');
                })
                ->update(['kategori_bantuan' => $kategori]);
        }
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            foreach (['susunan', 'imej', 'harga', 'kategori_bantuan'] as $column) {
                if (Schema::hasColumn('items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
