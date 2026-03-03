# Decisiones Técnicas y Arquitectura

Este documento detalla las justificaciones detrás de las decisiones técnicas tomadas durante el desarrollo de la API de la tienda.

## 1. Lógica en Procedimientos Almacenados (SPs)
Se optó por mover la lógica de negocio pesada (filtros, validaciones de stock, cálculos de impuestos) directamente al motor de base de datos MySQL.
- **Justificación**: Garantiza la integridad de los datos independientemente del cliente que acceda a la DB y reduce la latencia al evitar múltiples viajes de ida y vuelta (round-trips) entre el servidor de aplicaciones y la DB.
- **Implementación**: Uso de SQL dinámico para filtros opcionales en `sp_get_orders` y `sp_get_products`.

## 2. Refactorización a Migraciones Individuales
Originalmente, todos los SPs residían en un solo archivo. Se separaron en archivos individuales (`2026_03_03_00010x_...`).
- **Justificación**: Mejora el control de versiones de la base de datos. Permite modificar o revertir un procedimiento específico sin afectar a los demás.

## 3. Transaccionalidad y Consistencia de Stock
La creación de pedidos se maneja íntegramente dentro de `sp_create_order` usando una transacción SQL explícita.
- **Validación Atómica**: El stock se verifica y se descuenta en el mismo proceso. Si un producto no tiene stock, se hace un `ROLLBACK` automático.
- **Cálculos Centralizados**: El descuento del 10% y el IVA del 12% se calculan en la DB para asegurar que el "Total" sea siempre consistente con los detalles del pedido.

## 4. Uso de JSON para Parámetros Complejos
Para la creación de pedidos, los productos se pasan como un objeto JSON (`p_items_json`).
- **Ventaja**: Evita la necesidad de múltiples llamadas o el envío de largas listas de parámetros individuales, permitiendo una única llamada al SP para procesar todo el carrito de compras.

## 5. Capa de Servicios (Service Layer)
Laravel se utiliza como un orquestador delgado (lean).
- **Rol**: El `OrderService` y `ProductService` se encargan de preparar los datos (como codificar JSON o formatear fechas) y ejecutar los SPs, hidratando luego los resultados en modelos de Eloquent para mantener la compatibilidad con el ecosistema de Laravel (Resources, Pagination).

## 6. Pruebas Automáticas con Base de Datos 'Testing'
Se configuraron pruebas que interactúan directamente con los SPs en una base de datos de pruebas.
- **Enfoque**: Se re-ejecutan las migraciones de SPs en el `setUp` de los tests para asegurar que la lógica de la DB sea probada rigurosamente en un entorno limpio.
