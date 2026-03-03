# Reporte de Corrección de Errores (Bugfix)

Este documento detalla dos problemas técnicos identificados y sus respectivas soluciones.

## 1. Vulnerabilidad a Inyección SQL (Dynamic SQL)

### Fragmento Vulnerable
En el archivo `2026_03_03_000101_create_sp_get_products.php`, se concatenaba el parámetro `p_search` directamente en la cadena de consulta:

```sql
-- VULNERABLE
IF p_search IS NOT NULL AND p_search != '' THEN
    SET @query = CONCAT(@query, ' AND (name LIKE ''%', p_search, '%'' OR sku LIKE ''%', p_search, '%'' )');
END IF;
-- ...
PREPARE stmt FROM @query;
EXECUTE stmt; -- Sin placeholders
```

### Riesgo
Un atacante podría enviar un valor como `') OR 1=1 --` en el campo de búsqueda para alterar la lógica de la consulta y extraer datos no autorizados.

### Versión Corregida
Se utiliza el símbolo `?` como placeholder en la consulta dinámica y se envían los valores de forma segura mediante la cláusula `USING`.

```sql
-- CORREGIDO
IF p_search IS NOT NULL AND p_search != '' THEN
    SET @query = CONCAT(@query, ' AND (name LIKE ? OR sku LIKE ? )');
    SET @search_term = CONCAT('%', p_search, '%');
END IF;
-- ...
PREPARE stmt FROM @query;
IF p_search IS NOT NULL AND p_search != '' THEN
    EXECUTE stmt USING @search_term, @search_term;
ELSE
    EXECUTE stmt;
END IF;
```

---

## 2. Problema de Consultas N+1 en Listado de Pedidos

### Detección
El problema ocurre cuando se carga una lista de N pedidos y, al transformarlos a JSON, el sistema realiza una consulta adicional por cada pedido para obtener sus detalles (N consultas extra).

Podemos detectarlo usando herramientas como **Laravel Telescope** o el **Query Log**, observando múltiples consultas similares a:
`SELECT * FROM order_details WHERE order_id = ?`

### Solución (Eager Loading)
Se soluciona utilizando `Eager Loading` para cargar todas las relaciones necesarias en una sola consulta (o dos, dependiendo de la relación) antes de iterar sobre los resultados.

**En el Controlador:**
```php
public function index(Request $request) {
    $orders = $this->orderService->listOrders($request->all());
    // Carga recursiva de detalles y productos asociados de una sola vez
    return OrderResource::collection($orders->load('details.product'));
}
```

**Nota Importante:** También se corrigió una discrepancia en `OrderResource.php` donde se intentaba acceder a la relación `products` en lugar de `details`, lo que causaba que el eager loading no se aprovechara correctamente.
