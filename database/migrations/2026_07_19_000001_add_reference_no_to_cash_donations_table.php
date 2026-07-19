<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cash_donations', 'reference_no')) {
            Schema::table('cash_donations', function (Blueprint $table) {
                $table->string('reference_no')->nullable()->index();
            });
        }

        DB::table('cash_donations')
            ->whereNull('reference_no')
            ->select(['id', 'created_at'])
            ->chunkById(100, function ($cashDonations) {
                foreach ($cashDonations as $cashDonation) {
                    $createdAt = $cashDonation->created_at
                        ? Carbon::parse($cashDonation->created_at)
                        : now();

                    DB::table('cash_donations')
                        ->where('id', $cashDonation->id)
                        ->whereNull('reference_no')
                        ->update([
                            'reference_no' => sprintf('TAB/%s/%06d', $createdAt->format('Ymd'), $cashDonation->id),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('cash_donations', 'reference_no')) {
            Schema::table('cash_donations', function (Blueprint $table) {
                $table->dropIndex('cash_donations_reference_no_index');
            });

            Schema::table('cash_donations', function (Blueprint $table) {
                $table->dropColumn('reference_no');
            });
        }
    }
};
