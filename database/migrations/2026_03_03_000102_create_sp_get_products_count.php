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
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_get_products_count;");
        DB::unprepared("
            CREATE PROCEDURE sp_get_products_count(
                IN p_search VARCHAR(255)
            )
            BEGIN
                SET @query = 'SELECT COUNT(*) as total FROM products WHERE 1=1';
                
                IF p_search IS NOT NULL AND p_search != '' THEN
                    SET @query = CONCAT(@query, ' AND (name LIKE ''%', p_search, '%'' OR sku LIKE ''%', p_search, '%'' )');
                END IF;

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
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_get_products_count;");
    }
};
