<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a 'returned' value to orders.status.
 *
 * A parcel refused at the door costs two courier fees; an order cancelled
 * before dispatch costs nothing. Recording both as 'cancelled' made the
 * return-rate figure on the dashboard overstate what returns really cost.
 *
 * Existing rows are untouched — this only widens the allowed values.
 */
return new class extends Migration
{
    private const WITH_RETURNED = "'pending','confirmed','shipped','delivered','cancelled','returned'";

    private const WITHOUT_RETURNED = "'pending','confirmed','shipped','delivered','cancelled'";

    public function up(): void
    {
        DB::statement(
            "ALTER TABLE `orders` MODIFY `status` ENUM(".self::WITH_RETURNED.") NOT NULL DEFAULT 'pending'"
        );
    }

    public function down(): void
    {
        // Anything already marked returned becomes cancelled again, otherwise
        // the column could not be narrowed back.
        DB::table('orders')->where('status', 'returned')->update(['status' => 'cancelled']);

        DB::statement(
            "ALTER TABLE `orders` MODIFY `status` ENUM(".self::WITHOUT_RETURNED.") NOT NULL DEFAULT 'pending'"
        );
    }
};
