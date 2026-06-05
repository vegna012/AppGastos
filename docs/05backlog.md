# Backlog Formal - Sistema de Gestión de Gastos Empresariales
## Desarrollo Asistido por IA

---

## 1. Resumen ejecutivo del backlog

**Visión breve del proyecto:**
Construir una aplicación web on-premise de gestión de gastos empresariales (PHP MVC clásico, MySQL, almacenamiento local de XML) que permita capturar, aprobar, consultar y reportar gastos con control por áreas, centros de costos y presupuestos mensuales.

**Criterio de priorización usado:**
1. **Fundación técnica:** Sin base mínima (ruteo, DB, sesión), no se construye nada.
2. **Seguridad y Autenticación:** Ninguna funcionalidad sin control de acceso.
3. **Estructura organizacional (Catálogos):** Los gastos dependen de áreas, centros y roles.
4. **Caso de uso núcleo (Borrador de gasto):** Acción más básica del capturista.
5. **Flujo completo:** Aprobación, rechazo y ciclo de vida.
6. **Elementos de valor añadido:** XML, presupuestos, reportes, auditoría.
7. **Endurecimiento:** Refinamientos, manejo de errores, validaciones profundas.

**Estrategia de construcción incremental:**
Cada incremento es un "slice vertical" pequeño y utilizable de principio a fin (UI → API → DB). Primero el esqueleto (API, DB, autenticación), luego los órganos (catálogos, CRUDs), finalmente los músculos (flujo de negocio, reglas complejas). Mayoría de ítems en **XS, S o M** para implementación en 1-2 prompts por IA.

**Lógica general del orden:**
Fase 0 (Base) → Fase 1 (Seguridad) → Fase 2 (Estructura) → Fase 3 (Core: Gasto en Borrador) → Fase 4 (Flujo Aprobación) → Fase 5 (XML) → Fase 6 (Presupuesto) → Fase 7 (Reportes/Auditoría) → Fase 8 (Hardening).

---

## 2. Supuestos de trabajo

1. **Entorno de desarrollo:** Apache, PHP >=8.0, MySQL/MariaDB, mod_rewrite habilitado.
2. **Autenticación:** Sesiones nativas de PHP + AuthMiddleware (NO JWT inicial para evitar complejidad).
3. **Frontend:** HTML/JS que consume API REST del backend. Items asumen creación de endpoints + consumo.
4. **Cifrado de RFC:** `openssl_encrypt` con clave de entorno. Inicialmente texto plano en desarrollo, cifrado en Hardening.
5. **RBAC simple:** Basado en `id_rol` (Admin=1, Jefe=2, Capturista=3, Cuentas=4).
6. **Presupuesto:** Informativo (no bloquea aprobación), según documento de diseño.

---

## 3. Huecos, ambigüedades o contradicciones detectadas

| Tipo | Descripción | Impacto | Solución/Recomendación |
| :--- | :--- | :--- | :--- |
| **Funcional** | Rol "Cuentas por pagar": ¿puede editar o anular gasto aprobado? El diseño dice "solo revisión". | Alto | **Supuesto:** No edita. Solo consulta y reportes. Anulación requiere otro flujo. |
| **Técnico** | `JwtHelper.php` en árbol vs sesiones en diseño. JWT añade complejidad innecesaria. | Medio | **Supuesto:** Sesiones PHP para primera fase. JWT solo si dominio cruzado (contrario a "on-premise"). |
| **Datos** | Totales en `gastos_cabecera` (subtotal, iva, total, deducible) son calculados desde detalle. | Bajo | Consistente. Se agregará validación a criterios de aceptación. |
| **Flujo** | ¿Puede enviarse a aprobación un gasto sin detalles? El diccionario dice "debe tener al menos un detalle". | Alto | **Supuesto:** Validación backend que `count(detalles) > 0` antes de cambiar a "Pendiente". |
| **Seguridad** | No se especifica política de bloqueo por intentos fallidos de login. | Medio | **Supuesto:** 5 intentos fallidos → bloqueo 15 min, con retraso progresivo. |
| **Operación** | ¿Administrador puede borrar bitácora? El diseño dice "no editable desde interfaz funcional". | Bajo | **Supuesto:** Administrador solo lectura en `bitacora_movimientos`. |

---

## 4. Backlog inicial priorizado

| ID | Nombre corto | Tipo | Objetivo | Descripción breve | Valor | Dependencias | Alcance incluido | Alcance excluido | Riesgos | Criterios de aceptación | Pruebas manuales | Prioridad | Tamaño | Fase | Orden |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **FND-01** | Setup Base de Datos | Foundation | Crear esquema inicial y tablas | Script SQL maestro que cree DB y todas las tablas (sin seeders). | Habilita todo | Ninguna | Creación de tablas, PKs, FKs, índices básicos. | Seeders, triggers, stored procedures. | Errores de sintaxis SQL. | `mysql> show tables` → 13 tablas. Relaciones FK creadas. | Conectar cliente SQL y verificar estructura. | Crítica | M | 0: Base | 1 |
| **FND-02** | Estructura Backend (MVC) | Foundation | Router, controlador base y core | `index.php`, `Router.php`, `Controller.php`, `Request.php`, `Response.php` básicos (JSON). | Permite peticiones API | FND-01 | Endpoint `/api/health` → `{"status":"ok"}`. Autoloading Composer. | Middlewares, servicios complejos. | Rutas mal configuradas. | `GET /api/health` → 200 OK. | `curl localhost/api/health`. | Crítica | M | 0: Base | 2 |
| **FND-03** | Frontend Base | Foundation | Estructura de carpetas y layout base | `index.html`, `login.html`, `dashboard.html` vacíos con Bootstrap/jQuery, apuntando a API local. | Interfaz visual inicial | Ninguna | Sidebar/Header estáticos, carga de librerías, `api.config.js`. | Lógica de negocio, tablas dinámicas. | Rutas de assets incorrectas. | Al abrir `index.html` se ve el layout. | Abrir en navegador. | Alta | S | 0: Base | 3 |
| **CTL-01** | Seeders Iniciales | Foundation | Cargar datos mínimos para operar | Roles (Admin, Jefe, Capturista, Cuentas), Estatus (Borrador, Pendiente, Aprobado, Rechazado), 1 Área y 1 Admin. | Evita empezar desde cero | FND-01 | Scripts SQL o PHP para poblar tablas. | Datos de prueba masivos. | Contraseña admin predecible. | Al reinstalar DB, existe `admin@empresa.com` / `admin123`. | Ejecutar seeder y revisar DB. | Crítica | XS | 0: Base | 4 |
| **SEG-01** | Autenticación (Login/Session) | Full Slice | Endpoint de login y protección básica | `POST /api/auth/login` valida credenciales, inicia sesión PHP. `GET /api/auth/me`. | Control de acceso | FND-01, FND-02 | Login con correo/pass, retorno usuario/rol. Middleware auth. Logout. | Recuperación contraseña, frontend sesión. | Passwords en texto plano. | Login correcto → 200 y sesión. Login incorrecto → 401. | Postman: login, luego endpoint protegido. | Crítica | M | 1: Seguridad | 5 |
| **SEG-02** | Frontend Login | Full Slice | Formulario de login y redirección | Conectar `login.html` a `auth.service.js`, enviar credenciales, guardar sesión, redirigir a `dashboard.html`. | Usabilidad inicial | SEG-01, FND-03 | Mostrar error en UI, redirigir según rol (a dashboard genérico). | Manejo de tokens, "Recuérdame". | XSS en mensajes de error. | Login con admin → redirige a dashboard. | Navegador, login correcto/incorrecto. | Crítica | S | 1: Seguridad | 6 |
| **SEG-03** | RBAC y Middleware de Roles | Backend | Validar permisos por rol en rutas API | `RoleMiddleware` chequea `$_SESSION['usuario']['id_rol']` contra lista de roles permitidos. | Seguridad granular | SEG-01 | Proteger rutas: admin (solo 1), jefe (1,2), etc. | Permisos a nivel de registro (ej. solo su área). | IDs de rol incorrectos. | Admin accede a `/api/admin/...`, Capturista recibe 403. | Modificar rol en DB y probar acceso. | Crítica | S | 1: Seguridad | 7 |
| **CAT-01** | Catálogo Usuarios (CRUD Admin) | Full Slice | ABM de usuarios para administrador | CRUD completo (API+UI) para `usuarios`. Alta, edición, desactivación lógica. Asignación rol y área. | Gestión de personal | SEG-03, FND-01, FND-03 | Listado DataTable, formulario con selects (rol, área), hash de contraseña. | Asignación "Jefe de área" (campo en `areas`). | Exposición de campos cifrados. | Admin crea usuario "Juan" (rol Capturista). Aparece en listado. | Login Admin → Usuarios → Crear. | Crítica | M | 2: Catálogos | 8 |
| **CAT-02** | Catálogo Áreas (CRUD Admin) | Full Slice | ABM de áreas y asignación de jefe | CRUD para `areas`. Campo `id_jefe_area` (select de usuarios rol "Jefe"). | Base organizacional | CAT-01, FND-01 | Crear, editar, desactivar áreas. Listado. | Centros de costos (otro catálogo). | Asignar usuario inactivo como jefe. | Admin crea área "Ventas" y asigna jefe. | UI de administración. | Crítica | S | 2: Catálogos | 9 |
| **CAT-03** | Catálogo Centros de Costos | Full Slice | ABM de centros de costos vinculados a área | CRUD para `centros_costos`. Selector de área padre. Código + Nombre. | Clasificación financiera | CAT-02, FND-01 | Crear centros (ej. VENTAS-001). Validación unicidad por área. | Relación con presupuestos. | Centros huérfanos (soft delete). | Admin crea centro "SOPORTE-01" bajo "Tecnología". | UI. | Alta | S | 2: Catálogos | 10 |
| **GTO-01** | Crear Gasto (Borrador) - Backend | Backend | Endpoint para guardar cabecera + detalles | `POST /api/expenses`. Crea `gastos_cabecera` y `gastos_detalle`. Estado "Borrador". | Base del core | FND-01, FND-02 | Validar centro_costo pertenezca al área del usuario. Recalcular totales. | Envío a aprobación, XML, presupuesto. | Cálculo de totales incorrecto. | Enviar JSON con cabecera+1 detalle → DB con totales correctos. | Postman con sesión Capturista. | Crítica | M | 3: Core | 11 |
| **GTO-02** | Listado de Gastos (Capturista) | Full Slice | DataTable para "Mis Gastos" | UI + API `GET /api/expenses` con filtro por `id_usuario` (y opcional rol). | Visualización | GTO-01, FND-03 | Tabla con folio, fecha, total, estado. Botones editar/ver según estado. | Filtros complejos (fechas, áreas). | Performance con muchos registros. | Capturista ve solo sus gastos. | UI de gastos. | Alta | M | 3: Core | 12 |
| **GTO-03** | Editar Gasto (Borrador) | Full Slice | Permitir edición de gastos en estado "Borrador" | Endpoint `PUT /api/expenses/{id}`. UI formulario precargado. Solo si estado = Borrador. | Corrección temprana | GTO-01, GTO-02 | Modificar cabecera, añadir/editar/eliminar detalles. Recalcular. | Cambiar área/centro después de enviado. | Edición concurrente. | Capturista crea gasto, modifica monto → montos actualizados. | UI: editar, cambiar concepto, guardar. | Alta | M | 3: Core | 13 |
| **GTO-04** | Envío a Aprobación | Full Slice | Cambiar estado "Borrador" a "Pendiente" | `POST /api/expenses/{id}/submit`. Valida detalles >0. Registra bitácora. UI botón "Enviar". | Inicia flujo | GTO-01, AUD-01 | Cambio estado, registro `fecha_envio_aprobacion`. | Notificaciones (otro item). | Envío de gasto vacío. | Capturista envía gasto → estado Pendiente. | UI: click "Enviar", verificar cambio. | Alta | S | 4: Flujo | 14 |
| **APR-01** | Bandeja de Aprobación (Jefe) | Full Slice | Mostrar gastos "Pendientes" del área del jefe | API `GET /api/approval/pending` filtra por `id_area` del jefe (rol 2). UI DataTable. | Visibilidad para autorizar | SEG-03, GTO-04 | Ver detalle del gasto (modal o página). | Acción de aprobar/rechazar. | Fuga de información (ver otra área). | Jefe de Ventas ve gastos de Ventas pendientes. | Login como Jefe. | Crítica | S | 4: Flujo | 15 |
| **APR-02** | Aprobación/Rechazo de Gasto | Full Slice | Endpoint para aprobar o rechazar | `POST /api/approval/{id}/approve` y `/reject`. Rechazo requiere observaciones. Cambia estado. | Cierre del ciclo | APR-01, AUD-01 | Validar que usuario sea jefe del área del gasto. Registra `fecha_aprobacion`. | Flujo de corrección. | Rechazo sin motivo. | Jefe rechaza gasto con motivo → estado Rechazado. | UI de aprobación. | Crítica | M | 4: Flujo | 16 |
| **APR-03** | Corrección de Gasto Rechazado | Full Slice | Permitir al capturista editar gasto "Rechazado" y reenviar | Endpoint para editar (similar GTO-03) en estado Rechazado. Botón "Reenviar" (cambia a Pendiente). | Iteración rápida | GTO-03, APR-02 | Mostrar historial observaciones. Nuevo envío. | Notificación al jefe. | Ciclo infinito rechazo-corrección. | Capturista corrige gasto rechazado y envía → Pendiente. | UI. | Alta | S | 4: Flujo | 17 |
| **XML-01** | Carga de Archivo XML (Backend) | Backend | Subir XML y guardarlo en disco | `POST /api/expenses/{id}/xml`. Valida extensión/tamaño, guarda en `/storage/xml/`, registra en `facturas_cfdi` con `procesado=0`. | Almacenamiento evidencia | FND-02, GTO-01 | Manejo de errores (archivo inválido, duplicado). | Extracción de datos del XML. | Ataques XXE. | Subir XML válido → Archivo en disco y registro en DB. | Postman con `multipart/form-data`. | Alta | M | 5: XML | 18 |
| **XML-02** | Extracción de Datos del XML | Backend | Leer XML y poblar campos de `facturas_cfdi` y sugerir proveedor | Servicio `XmlHelper::extract()`. Actualiza CFDI (UUID, RFCs, totales). Busca o crea proveedor por RFC. | Automatización | XML-01, CAT-03 | Extraer: Comprobante, Emisor, Receptor, Conceptos (Totales). | Validación SAT, timbrado, deducibilidad automática. | XML corrupto o no CFDI. | Subir XML → campos `uuid`, `rfc_emisor`, `total` se llenan. | Subir XML de prueba. | Alta | M | 5: XML | 19 |
| **XML-03** | Vista Previa y Asociación XML en UI | Frontend | Modal para ver datos extraídos del XML y asociar al gasto | En UI de gasto, listar XMLs cargados. Modal "Vista Previa" muestra datos extraídos (UUID, Monto). | Transparencia | XML-02, GTO-02 | Mostrar si el XML ya fue procesado. | Edición de datos extraídos. | UI lenta con XMLs grandes. | Usuario ve en detalle: "Factura XML: ABC123 - $1,500.00". | UI de gasto. | Media | S | 5: XML | 20 |
| **PRES-01** | Gestión de Presupuestos (CRUD) | Full Slice | Admin asigna presupuesto mensual a área | CRUD para `presupuestos` (área, año, mes, monto). UI con selects. | Control financiero | CAT-02, FND-01 | Validación unicidad (área, año, mes). | Cálculo gasto real vs presupuesto. | Asignar presupuesto a área inactiva. | Admin asigna $10,000 a Ventas para Mayo 2025. | UI administración. | Alta | S | 6: Presupuesto | 21 |
| **PRES-02** | Consulta de Presupuesto en Aprobación | Backend | Mostrar presupuesto disponible al Jefe de Área | En `GET /api/approval/pending` o detalle, agregar `budget_used` y `budget_total` del mes actual. | Información para decisión | PRES-01, APR-01 | Sumar gastos APROBADOS del área/mes actual. | Bloqueo automático por exceder presupuesto. | Performance en suma. | Jefe ve: "Presupuesto: $10,000 / Gastado: $3,000". | UI aprobación. | Alta | M | 6: Presupuesto | 22 |
| **AUD-01** | Registro en Bitácora (Service) | Backend | Servicio central para registrar acciones en `bitacora_movimientos` | Clase `AuditService::log($accion, $desc, $id_gasto)`. Llamar en login, creación, envío, aprobación, rechazo. | Trazabilidad total | FND-01, SEG-01 | Registrar IP, User Agent, fecha. No reemplazar registros. | Vista de bitácora en UI. | Sobrecarga de escritura. | Al aprobar un gasto, aparece nuevo registro en `bitacora_movimientos`. | Realizar acción, revisar DB. | Crítica | S | 7: Auditoría | 23 |
| **RPT-01** | Reporte de Gastos (API + UI Básica) | Full Slice | Endpoint y tabla para listar gastos con filtros (fecha, área, estado) | `GET /api/reports/expenses` con parámetros. UI simple para Cuentas por Pagar y Admin. | Visibilidad operativa | GTO-04, CAT-02 | Filtros combinados. Exportación CSV básica. | Gráficas, comparativa presupuestal. | SQL Injection en filtros. | Cuentas por Pagar filtra gastos de Abril 2025 → Tabla actualizada. | UI reportes. | Alta | M | 7: Reportes | 24 |
| **RPT-02** | Reporte Presupuesto vs Real | Full Slice | Comparativa presupuesto asignado vs gasto aprobado por área/mes | `GET /api/reports/budget-vs-actual`. Tabla: Área, Mes, Presupuesto, Gasto, Diferencia, %. | Medición desempeño | PRES-02, RPT-01 | Mostrar porcentaje de consumo. | Drill-down a gastos específicos. | Discrepancias por redondeo. | Reporte muestra Ventas: $10k / $9k (90%). | UI reportes. | Media | M | 7: Reportes | 25 |
| **HARD-01** | Cifrado de RFC en DB | Backend | Aplicar cifrado a campos RFC en `proveedores` y `facturas_cfdi` | `EncryptionHelper`. Migrar datos existentes. Modificar repositorios para desencriptar al leer. | Cumplimiento datos sensibles | CAT-03, XML-02 | Usar clave desde `.env`. | Cifrado de otros campos (ej. correo). | Pérdida de clave de cifrado. | En DB, `rfc_emisor` se ve cifrado. API retorna descifrado. | Ver DB directa vs API. | Media | L | 8: Hardening | 26 |
| **HARD-02** | Manejo de Sesión y Timeout | Backend | Implementar expiración de sesión por inactividad | Configurar `session.gc_maxlifetime`. Middleware verifica `last_activity` y cierra sesión si >30 min. | Seguridad | SEG-01 | Refresh automático de actividad en cada request. | "Recuérdame" (persistente). | Usuario pierde trabajo no guardado. | Usuario inactivo 31 min → próxima request redirige a login. | Navegador, esperar. | Alta | S | 8: Hardening | 27 |

---

## 5. Agrupación por fases

| Fase | Nombre | Objetivo | Items incluidos | Razón |
| :--- | :--- | :--- | :--- | :--- |
| **0** | Base Técnica | Cimientos sobre los que todo se sostiene | FND-01, FND-02, FND-03, CTL-01 | Sin DB, router y frontend base no hay aplicación visible. |
| **1** | Seguridad y Acceso | Control de quién entra y qué ve | SEG-01, SEG-02, SEG-03 | No se construye nada privado sin saber quién es el usuario. |
| **2** | Catálogos Estructurales | Definir la organización (Usuarios, Áreas, Costos) | CAT-01, CAT-02, CAT-03 | Los gastos dependen de estos datos. |
| **3** | Core del Gasto (Borrador) | Funcionalidad principal de captura | GTO-01, GTO-02, GTO-03, GTO-04 | El capturista debe poder hacer su trabajo base. |
| **4** | Flujo de Aprobación | Corazón del negocio (autorización) | APR-01, APR-02, APR-03 | Habilita al Jefe de Área a revisar y cerrar el ciclo. |
| **5** | Manejo de XML | Evidencia fiscal y automatización | XML-01, XML-02, XML-03 | Añade valor real (carga de facturas). Construcción posterior al gasto base. |
| **6** | Presupuesto | Regla financiera informativa | PRES-01, PRES-02 | Añade control y visibilidad. Depende de Áreas y Gastos aprobados. |
| **7** | Reportes y Auditoría | Visibilidad y trazabilidad | AUD-01, RPT-01, RPT-02 | Cierran el ciclo para roles secundarios (Cuentas por Pagar, Admin). |
| **8** | Hardening | Seguridad y robustez | HARD-01, HARD-02 | Se aplican sobre lo ya construido para proteger datos y sesiones. |

---

## 6. Secuencia recomendada de ejecución

1. **FND-01** (Setup Base de Datos)
2. **FND-02** (Backend MVC)
3. **CTL-01** (Seeders) → *Permite tener admin para probar SEG-01*
4. **SEG-01** (Auth Backend)
5. **FND-03** (Frontend Base) → *Necesario para SEG-02*
6. **SEG-02** (Frontend Login)
7. **SEG-03** (RBAC)
8. **CAT-01** (Usuarios) → *Necesario para crear jefes y capturistas*
9. **CAT-02** (Áreas) → *Necesario para estructura*
10. **CAT-03** (Centros Costos) → *Necesario para GTO-01*
11. **GTO-01** (Crear Gasto Backend) → *Primer valor real del negocio*
12. Continuar con el orden de la tabla.

**Primer item ideal:** `FND-01` (Setup Base de Datos)

**Items que no conviene adelantar:** `PRES-01` sin gastos aprobados; `XML-01` sin gastos en borrador.

---

## 7. Identificación de items demasiado grandes

El backlog actual los ha dividido correctamente. Items que requieren vigilancia:

| ID | Nombre | Riesgo de grandeza | Mitigación aplicada |
| :--- | :--- | :--- | :--- |
| **CAT-01** | Usuarios | CRUD completo + roles + áreas + contraseña puede ser L | Limitado a ABM básico. Perfil de usuario, cambio contraseña y recuperación quedan excluidos (futuro). |
| **RPT-01** | Reporte de Gastos | Gráficas + múltiples formatos + filtros avanzados | Limitado a DataTable + CSV. Gráficas y formatos complejos excluidos. |
| **HARD-01** | Cifrado de RFC | Migrar datos + modificar todos los repositorios | Esfuerzo L planificado como bloque único. No se intenta dividir. |

---

## 8. Backlog listo para primeros 10 items (formato resumido)

| Orden | ID | Nombre | Por qué está al inicio | Qué desbloquea | Tamaño |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **1** | **FND-01** | Setup Base de Datos | Sin tablas, nada funciona | Toda interacción con datos | M |
| **2** | **FND-02** | Estructura Backend (MVC) | El esqueleto de la API | Creación de endpoints | M |
| **3** | **CTL-01** | Seeders Iniciales | Datos de prueba (admin) para autenticación | Pruebas de login | XS |
| **4** | **SEG-01** | Autenticación (Login/Session) | La puerta de entrada al sistema | Protección de endpoints | M |
| **5** | **FND-03** | Frontend Base | La interfaz donde el usuario trabajará | Conexión visual con API | S |
| **6** | **SEG-02** | Frontend Login | Completar "Entrar al sistema" | Construcción del Dashboard | S |
| **7** | **SEG-03** | RBAC y Middleware de Roles | Control de acceso por rol | Seguridad granular | S |
| **8** | **CAT-01** | Catálogo Usuarios | Crear distintos roles (jefes, capturistas) | Poblar sistema con operadores reales | M |
| **9** | **CAT-02** | Catálogo Áreas | Estructurar los gastos | Asignar jefes y centros de costos | S |
| **10** | **CAT-03** | Catálogo Centros de Costos | Clasificación financiera | Creación de gastos (GTO-01) | S |

**Primer incremento real de desarrollo:**
**ID: FND-01 (Setup Base de Datos)**
Este debe ser el primer prompt para la IA desarrolladora. Consiste únicamente en ejecutar el script SQL maestro que deje la base de datos vacía pero completamente estructurada. Una vez verificado, se procede a FND-02.

---

## 9. Instrucciones para usar este backlog

1. **Seleccionar el siguiente item** por orden de prioridad (empezar por Fase 0, orden sugerido).
2. **Generar prompt para IA desarrolladora** incluyendo:
   - Contexto del proyecto (resumen ejecutivo + supuestos)
   - El item específico (ID, nombre, objetivo, descripción)
   - Criterios de aceptación
   - Pruebas manuales sugeridas
   - Dependencias ya construidas
3. **Verificar cumplimiento** de criterios de aceptación antes de marcar como completado.
4. **Actualizar backlog** marcando items completados y ajustando prioridades si cambian requisitos.

---

*Documento generado para gobernar desarrollo asistido por IA.*
*Última actualización: 2026-06-05*