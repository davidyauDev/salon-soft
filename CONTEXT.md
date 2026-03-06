# CONTEXT

## Producto
Sistema de gestion para salon de belleza (ventas, servicios, clientes, inventario/productos).

## Principios UI/UX (acordado)
- Mantener estilo actual del sistema (no introducir estilos ajenos).
- Modales: bordes redondeados, fondos claros, botones primarios morados.
- Tablas: header gris claro, filas con bordes suaves, chips para categorias/servicios.
- Inputs: bordes suaves, placeholder claro, iconos inline.
- Evitar cambios que rompan la consistencia visual existente.

## Decisiones recientes
- Inventario se reemplazo por modulo Productos en /inventario.
- Productos usa: listado con buscador, botones Categorias y + Crear Producto, modales de categoria/producto.
- Ventas redisenado con tabs Servicios/Productos y drawer lateral "Nueva venta".
- Servicios ya no muestra Atenciones; todo registro de servicios se hace desde Ventas.
- Modal de pago incluye "Emitir comprobante" (solo UI por ahora).
- Cliente en Ventas: buscador con sugerencias y modal rapido de creacion.

## Estado actual (clave)
- Productos: implementado con tabla, modales y creacion de stock inicial.
- Ventas: implementado con tablas por tab, drawer, modal de pago.
- Servicios: solo catalogo.

## Pendientes
- Confirmar flujo completo de venta (productos y servicios) con datos reales.
- Opcional: agregar consumos en ventas de servicios.
- Opcional: añadir creacion de cliente desde drawer con mas validaciones.
- Revisar exportar (solo UI por ahora).

## Notas tecnicas
- API base: VITE_API_URL o http://localhost:8000
- Ventas producto: POST /api/sales
- Ventas servicio: POST /api/service-records (consumptions opcional)
- Productos: /api/items, /api/inventory

## Comandos utiles
- Backend: php artisan migrate
- Backend: php artisan db:seed --class=SampleDataSeeder
- Frontend: npm run dev
