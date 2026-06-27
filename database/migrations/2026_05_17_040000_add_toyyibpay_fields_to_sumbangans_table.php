<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sumbangans', function (Blueprint $table) {
            if (! Schema::hasColumn('sumbangans', 'toyyibpay_bill_code')) {
                $table->string('toyyibpay_bill_code')->nullable();
            }

            if (! Schema::hasColumn('sumbangans', 'payment_reference')) {
                $table->string('payment_reference')->nullable();
            }

            if (! Schema::hasColumn('sumbangans', 'payment_status')) {
                $table->string('payment_status')->nullable();
            }

            if (! Schema::hasColumn('sumbangans', 'payment_payload')) {
                $table->json('payment_payload')->nullable();
            }

            if (! Schema::hasColumn('sumbangans', 'paid_at')) {
                $table->timestamp('paid_at')->nullable();
            }

            if (! Schema::hasColumn('sumbangans', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sumbangans', function (Blueprint $table) {
            $columns = [
                'toyyibpay_bill_code',
                'payment_reference',
                'payment_status',
                'payment_payload',
                'paid_at',
                'cancelled_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('sumbangans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
