<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'donors_homepage_order_unique';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->ensureNoDuplicateVisibleHomepageOrders();

        Schema::table('donors', function (Blueprint $table) {
            $table->integer('homepage_order')->nullable()->default(null)->change();
        });

        DB::table('donors')
            ->where('homepage_order', 0)
            ->orWhere('show_on_homepage', false)
            ->update(['homepage_order' => null]);

        if (! Schema::hasIndex('donors', self::INDEX_NAME)) {
            Schema::table('donors', function (Blueprint $table) {
                $table->unique('homepage_order', self::INDEX_NAME);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('donors', self::INDEX_NAME)) {
            Schema::table('donors', function (Blueprint $table) {
                $table->dropUnique(self::INDEX_NAME);
            });
        }

        DB::table('donors')
            ->whereNull('homepage_order')
            ->update(['homepage_order' => 0]);

        Schema::table('donors', function (Blueprint $table) {
            $table->integer('homepage_order')->default(0)->nullable(false)->change();
        });
    }

    private function ensureNoDuplicateVisibleHomepageOrders(): void
    {
        $duplicates = DB::table('donors')
            ->select([
                'homepage_order',
                DB::raw('COUNT(*) as donor_count'),
                DB::raw('GROUP_CONCAT(id) as donor_ids'),
            ])
            ->where('show_on_homepage', true)
            ->where('homepage_order', '>', 0)
            ->groupBy('homepage_order')
            ->having('donor_count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            return;
        }

        $details = $duplicates
            ->map(fn ($row) => "#{$row->homepage_order} (donor IDs: {$row->donor_ids})")
            ->implode('; ');

        throw new RuntimeException(
            "Cannot add unique donor homepage ranking index while duplicate visible rankings exist: {$details}"
        );
    }
};
