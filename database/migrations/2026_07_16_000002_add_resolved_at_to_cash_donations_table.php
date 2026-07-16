<?php

use App\Models\CashDonation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cash_donations', 'resolved_at')) {
            Schema::table('cash_donations', function (Blueprint $table) {
                $table->dateTime('resolved_at')->nullable()->after('paid_at');
            });
        }

        DB::table('cash_donations')
            ->where('payment_status', CashDonation::STATUS_SUCCESS)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => DB::raw('COALESCE(paid_at, updated_at, created_at)'),
            ]);

        DB::table('cash_donations')
            ->where('payment_status', CashDonation::STATUS_FAILED)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => DB::raw('COALESCE(updated_at, created_at)'),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('cash_donations', 'resolved_at')) {
            Schema::table('cash_donations', function (Blueprint $table) {
                $table->dropColumn('resolved_at');
            });
        }
    }
};
