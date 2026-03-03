<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Exception;

class OrderService
{
    public function listOrders(array $filters = [])
    {
        $desde = $filters['desde'] ?? null;
        $hasta = $filters['hasta'] ?? null;
        $minTotal = $filters['minTotal'] ?? null;

        $results = DB::select("CALL sp_get_orders(?, ?, ?)", [$desde, $hasta, $minTotal]);
        return Order::hydrate($results);
    }

    public function createOrder(array $data)
    {
        $itemsJson = json_encode($data['products']);

        // Define output variables
        DB::statement("SET @p_order_id = 0");
        DB::statement("SET @p_error_message = ''");

        // Call Stored Procedure
        DB::statement("CALL sp_create_order(?, ?, ?, ?, ?, @p_order_id, @p_error_message)", [
            $data['customer_name'],
            $data['customer_email'],
            $data['customer_phone'],
            $data['customer_address'],
            $itemsJson
        ]);

        // Fetch results from variables
        $result = DB::select("SELECT @p_order_id as order_id, @p_error_message as error_message")[0];

        if ($result->error_message) {
            throw new Exception($result->error_message);
        }

        return Order::with('details.product')->find($result->order_id);
    }
}
