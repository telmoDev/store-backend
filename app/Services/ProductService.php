<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function listProducts(array $filters)
    {
        $search = $filters['search'] ?? null;
        $sortField = $filters['sort'] ?? 'id';
        $sortDir = 'ASC';

        if (str_starts_with($sortField, '-')) {
            $sortDir = 'DESC';
            $sortField = substr($sortField, 1);
        }

        // Validate sort field
        $allowedSorts = ['name', 'price', 'stock', 'sku', 'created_at', 'id'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'id';
        }

        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;
        $offset = ($page - 1) * $perPage;

        // Call Stored Procedure
        $results = DB::select("CALL sp_get_products(?, ?, ?, ?, ?)", [
            $search,
            $sortField,
            $sortDir,
            $perPage,
            $offset
        ]);

        // Get total count for pagination using Stored Procedure
        $countResult = DB::select("CALL sp_get_products_count(?)", [$search])[0];
        $total = $countResult->total;
        
        $products = Product::hydrate($results);

        return new LengthAwarePaginator(
            $products,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
