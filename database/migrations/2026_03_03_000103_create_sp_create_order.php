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
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_create_order;");
        DB::unprepared("
            CREATE PROCEDURE sp_create_order(
                IN p_customer_name VARCHAR(255),
                IN p_customer_email VARCHAR(255),
                IN p_customer_phone VARCHAR(50),
                IN p_customer_address TEXT,
                IN p_items_json JSON,
                OUT p_order_id INT,
                OUT p_error_message VARCHAR(255)
            )
            sp_create_order: BEGIN
                DECLARE v_item_count INT;
                DECLARE v_idx INT DEFAULT 0;
                DECLARE v_product_id INT;
                DECLARE v_quantity INT;
                DECLARE v_stock INT;
                DECLARE v_price DECIMAL(10,2);
                DECLARE v_subtotal DECIMAL(10,2) DEFAULT 0;
                DECLARE v_discount DECIMAL(10,2) DEFAULT 0;
                DECLARE v_iva DECIMAL(10,2);
                DECLARE v_total DECIMAL(10,2);
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET p_error_message = 'Error en la transacción de base de datos';
                    SET p_order_id = NULL;
                END;

                START TRANSACTION;

                SET v_item_count = JSON_LENGTH(p_items_json);
                IF v_item_count = 0 THEN
                    SET p_error_message = 'Debe incluir al menos un producto';
                    ROLLBACK;
                ELSE
                    -- Loop for validation and subtotal calculation
                    WHILE v_idx < v_item_count DO
                        SET v_product_id = JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].product_id'));
                        SET v_quantity = JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].quantity'));

                        SELECT stock, price INTO v_stock, v_price FROM products WHERE id = v_product_id FOR UPDATE;

                        IF v_stock < v_quantity THEN
                            SET p_error_message = CONCAT('Stock insuficiente para el producto ID: ', v_product_id);
                            SET p_order_id = NULL;
                            ROLLBACK;
                            LEAVE sp_create_order;
                        END IF;

                        SET v_subtotal = v_subtotal + (v_price * v_quantity);
                        SET v_idx = v_idx + 1;
                    END WHILE;

                    -- Calculations
                    IF v_subtotal > 100 THEN
                        SET v_discount = v_subtotal * 0.10;
                    END IF;

                    SET v_iva = (v_subtotal - v_discount) * 0.12;
                    SET v_total = (v_subtotal - v_discount) + v_iva;

                    -- Insert Order
                    INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total_amount, status, created_at, updated_at)
                    VALUES (p_customer_name, p_customer_email, p_customer_phone, p_customer_address, v_total, 'pending', NOW(), NOW());
                    
                    SET p_order_id = LAST_INSERT_ID();

                    -- Insert Details and Update Stock
                    SET v_idx = 0;
                    WHILE v_idx < v_item_count DO
                        SET v_product_id = JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].product_id'));
                        SET v_quantity = JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].quantity'));
                        
                        SELECT price INTO v_price FROM products WHERE id = v_product_id;

                        INSERT INTO order_details (order_id, product_id, quantity, price, created_at, updated_at)
                        VALUES (p_order_id, v_product_id, v_quantity, v_price, NOW(), NOW());

                        UPDATE products SET stock = stock - v_quantity WHERE id = v_product_id;

                        SET v_idx = v_idx + 1;
                    END WHILE;

                    SET p_error_message = NULL;
                    COMMIT;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_create_order;");
    }
};
