<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_get_orders;");
        DB::unprepared("
            CREATE PROCEDURE sp_get_orders(
                IN p_desde DATE,
                IN p_hasta DATE,
                IN p_min_total DECIMAL(10,2)
            )
            BEGIN
                SET @query = 'SELECT * FROM orders WHERE 1=1';
                
                IF p_desde IS NOT NULL THEN
                    SET @query = CONCAT(@query, ' AND created_at >= ''', p_desde, ' 00:00:00''');
                END IF;

                IF p_hasta IS NOT NULL THEN
                    SET @query = CONCAT(@query, ' AND created_at <= ''', p_hasta, ' 23:59:59''');
                END IF;

                IF p_min_total IS NOT NULL THEN
                    SET @query = CONCAT(@query, ' AND total_amount >= ', p_min_total);
                END IF;

                SET @query = CONCAT(@query, ' ORDER BY created_at DESC');

                PREPARE stmt FROM @query;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_get_orders;");
    }
};
