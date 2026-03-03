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
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_get_products;");
        DB::unprepared("
            CREATE PROCEDURE sp_get_products(
                IN p_search VARCHAR(255),
                IN p_sort_field VARCHAR(50),
                IN p_sort_dir VARCHAR(4),
                IN p_limit INT,
                IN p_offset INT
            )
            BEGIN
                SET @query = 'SELECT * FROM products WHERE 1=1';
                SET @search = NULL;
                
                IF p_search IS NOT NULL AND p_search != '' THEN
                    SET @query = CONCAT(@query, ' AND (name LIKE ? OR sku LIKE ? )');
                    SET @search = CONCAT('%', p_search, '%');
                END IF;

                IF p_sort_field IS NOT NULL AND p_sort_field != '' THEN
                    SET @query = CONCAT(@query, ' ORDER BY ', p_sort_field, ' ', p_sort_dir);
                ELSE
                    SET @query = CONCAT(@query, ' ORDER BY id ASC');
                END IF;

                SET @query = CONCAT(@query, ' LIMIT ', p_limit, ' OFFSET ', p_offset);

                PREPARE stmt FROM @query;
                
                IF @search IS NOT NULL THEN
                    EXECUTE stmt USING @search, @search;
                ELSE
                    EXECUTE stmt;
                END IF;
                
                DEALLOCATE PREPARE stmt;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_get_products;");
    }
};
