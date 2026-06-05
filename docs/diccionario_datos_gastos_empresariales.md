# Documento de Diccionario de Datos  
## Sistema de Gestión de Gastos Empresariales

---

## 1. Convenciones y Consideraciones

### 1.1 Motor de base de datos

El sistema utilizará **MySQL o MariaDB** como motor relacional principal.

El diseño se mantiene simple, claro y directamente implementable, evitando normalización innecesaria o estructuras demasiado abstractas.

### 1.2 Tipos de datos estándar

| Tipo | Uso recomendado |
|---|---|
| `INT` | Identificadores primarios y foráneos |
| `VARCHAR(n)` | Textos cortos como nombres, claves, correos, códigos |
| `TEXT` | Observaciones, descripciones largas o comentarios |
| `DECIMAL(12,2)` | Importes monetarios |
| `DATE` | Fechas sin hora |
| `DATETIME` | Fecha y hora de operaciones |
| `TINYINT` | Valores pequeños, banderas o estados simples |
| `BOOLEAN` | Campos lógicos como activo/inactivo |
| `CHAR(36)` | UUID de CFDI |
| `YEAR` | Año presupuestal |

### 1.3 Convenciones de nombres

- Las tablas se nombran en **minúsculas**, en plural y con guion bajo.
- Las llaves primarias usan el formato: `id_tabla`.
- Las llaves foráneas usan el formato: `id_tabla_referenciada`.
- Los campos de auditoría usan nombres estándar:
  - `creado_en`
  - `creado_por`
  - `actualizado_en`
  - `actualizado_por`
  - `eliminado_en`

Ejemplo:

| Elemento | Convención |
|---|---|
| Tabla | `gastos_cabecera` |
| PK | `id_gasto_cabecera` |
| FK | `id_area`, `id_usuario` |
| Índice | `idx_gastos_area_fecha` |

### 1.4 Manejo de PK y FK

- Cada tabla principal debe tener una llave primaria autoincremental.
- Las relaciones se controlan mediante llaves foráneas.
- Toda FK debe tener índice.
- Las FK no deben eliminar información histórica crítica.
- En catálogos, se recomienda usar **desactivación lógica**, no eliminación física.

### 1.5 Soft delete

Se recomienda usar soft delete en tablas administrativas y operativas importantes.

Campo estándar:

| Campo | Tipo | Descripción |
|---|---|---|
| `eliminado_en` | `DATETIME` | Fecha en que el registro fue desactivado lógicamente |

Cuando `eliminado_en` sea `NULL`, el registro se considera activo.

### 1.6 Auditoría

Cada tabla importante debe registrar quién creó y quién modificó la información.

Campos estándar:

| Campo | Tipo | Descripción |
|---|---|---|
| `creado_en` | `DATETIME` | Fecha y hora de creación |
| `creado_por` | `INT` | Usuario que creó el registro |
| `actualizado_en` | `DATETIME` | Fecha y hora de última modificación |
| `actualizado_por` | `INT` | Usuario que modificó el registro |

Además, los movimientos críticos del gasto se registran en la tabla `bitacora_movimientos`.

### 1.7 Manejo de datos sensibles

El sistema manejará información sensible y administrativa, por lo que los siguientes campos deben protegerse:

| Tipo de dato | Tratamiento |
|---|---|
| Contraseña de usuario | Hash seguro, nunca texto plano |
| RFC de proveedor | Cifrado obligatorio |
| RFC emisor/receptor del CFDI | Cifrado obligatorio |
| Ruta del archivo XML | Protección por permisos del servidor |
| XML CFDI | Almacenamiento fuera de carpeta pública |
| Datos fiscales extraídos | Cifrado cuando contengan RFC u otros datos sensibles |

No se debe guardar la contraseña original. Solo debe almacenarse `password_hash`.

---

## 2. Organización y Seguridad del Modelo

El modelo se organiza en cinco grupos principales.

### 2.1 Seguridad y organización

| Tabla | Propósito |
|---|---|
| `usuarios` | Usuarios que acceden al sistema |
| `roles` | Perfiles de acceso |
| `areas` | Áreas internas de la empresa |
| `centros_costos` | Centros de costos asociados a áreas |

### 2.2 Control presupuestal

| Tabla | Propósito |
|---|---|
| `presupuestos` | Presupuesto mensual por área |

### 2.3 Catálogos

| Tabla | Propósito |
|---|---|
| `estatus_gasto` | Estados del flujo de gasto |
| `categorias_gasto` | Clasificación general del gasto |
| `conceptos_deducibilidad` | Clasificación fiscal simple del detalle |
| `proveedores` | Proveedores relacionados con CFDI o gastos |

### 2.4 Gestión de gastos

| Tabla | Propósito |
|---|---|
| `gastos_cabecera` | Comprobación general del gasto |
| `gastos_detalle` | Conceptos o partidas del gasto |
| `facturas_cfdi` | CFDI/XML asociados a la cabecera |

### 2.5 Auditoría

| Tabla | Propósito |
|---|---|
| `bitacora_movimientos` | Historial de acciones críticas |

---

# 3. Diccionario de Datos por Tabla

---

## 3.1 Tabla: `roles`

### Descripción general

Representa los perfiles de acceso del sistema. Sirve para controlar permisos generales según el tipo de usuario.

Roles mínimos recomendados:

- Administrador
- Capturista
- Jefe de área
- Cuentas por pagar

### Relaciones

- Un rol puede estar asignado a muchos usuarios.
- Se relaciona con `usuarios`.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_rol` |
| Única | `nombre` |
| Índices | `idx_roles_activo` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_rol` | `INT` | Identificador del rol | PK, autoincremental |
| `nombre` | `VARCHAR(50)` | Nombre del rol | Único, obligatorio |
| `descripcion` | `VARCHAR(255)` | Descripción breve del rol | Opcional |
| `activo` | `BOOLEAN` | Indica si el rol está disponible | Obligatorio |
| `creado_en` | `DATETIME` | Fecha de creación | Auditoría |
| `actualizado_en` | `DATETIME` | Fecha de última actualización | Auditoría |
| `eliminado_en` | `DATETIME` | Baja lógica del registro | Soft delete |

---

## 3.2 Tabla: `usuarios`

### Descripción general

Representa a los usuarios internos que acceden al sistema. Cada usuario pertenece a un área y tiene un rol.

### Relaciones

- Pertenece a un rol.
- Pertenece a un área.
- Puede crear gastos.
- Puede aprobar gastos si tiene rol de jefe de área.
- Puede generar movimientos de bitácora.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_usuario` |
| FK | `id_rol`, `id_area` |
| Única | `correo` |
| Índices | `idx_usuarios_area`, `idx_usuarios_rol`, `idx_usuarios_activo` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_usuario` | `INT` | Identificador del usuario | PK, autoincremental |
| `id_rol` | `INT` | Rol asignado al usuario | FK a `roles` |
| `id_area` | `INT` | Área a la que pertenece | FK a `areas` |
| `nombre` | `VARCHAR(100)` | Nombre completo del usuario | Obligatorio |
| `correo` | `VARCHAR(120)` | Correo de acceso | Único, obligatorio |
| `password_hash` | `VARCHAR(255)` | Contraseña protegida | Hash obligatorio |
| `activo` | `BOOLEAN` | Indica si puede acceder al sistema | Obligatorio |
| `ultimo_acceso` | `DATETIME` | Última fecha de inicio de sesión | Opcional |
| `creado_en` | `DATETIME` | Fecha de creación | Auditoría |
| `creado_por` | `INT` | Usuario que creó el registro | FK a `usuarios` |
| `actualizado_en` | `DATETIME` | Fecha de modificación | Auditoría |
| `actualizado_por` | `INT` | Usuario que modificó el registro | FK a `usuarios` |
| `eliminado_en` | `DATETIME` | Baja lógica del usuario | Soft delete |

---

## 3.3 Tabla: `areas`

### Descripción general

Representa las áreas internas de la empresa, por ejemplo Administración, Ventas, Operaciones, Tecnología o Recursos Humanos.

Cada área puede tener un jefe responsable de aprobar gastos.

### Relaciones

- Un área tiene muchos usuarios.
- Un área tiene muchos centros de costos.
- Un área tiene presupuestos mensuales.
- Un área puede tener un jefe de autorización.
- Un gasto pertenece a un área.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_area` |
| FK | `id_jefe_area` |
| Única | `nombre` |
| Índices | `idx_areas_jefe`, `idx_areas_activo` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_area` | `INT` | Identificador del área | PK, autoincremental |
| `id_jefe_area` | `INT` | Usuario responsable de aprobar gastos | FK a `usuarios`, opcional al inicio |
| `nombre` | `VARCHAR(100)` | Nombre del área | Único, obligatorio |
| `descripcion` | `VARCHAR(255)` | Descripción del área | Opcional |
| `activo` | `BOOLEAN` | Indica si el área está disponible | Obligatorio |
| `creado_en` | `DATETIME` | Fecha de creación | Auditoría |
| `creado_por` | `INT` | Usuario que creó el registro | FK a `usuarios` |
| `actualizado_en` | `DATETIME` | Fecha de modificación | Auditoría |
| `actualizado_por` | `INT` | Usuario que modificó el registro | FK a `usuarios` |
| `eliminado_en` | `DATETIME` | Baja lógica del área | Soft delete |

---

## 3.4 Tabla: `centros_costos`

### Descripción general

Representa unidades de control presupuestal asociadas a un área. Sirve para clasificar el origen financiero del gasto.

### Relaciones

- Pertenece a un área.
- Puede tener muchos gastos.
- Se usa para reportes por centro de costos.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_centro_costo` |
| FK | `id_area` |
| Única compuesta | `id_area`, `codigo` |
| Índices | `idx_centros_area`, `idx_centros_activo` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_centro_costo` | `INT` | Identificador del centro de costo | PK, autoincremental |
| `id_area` | `INT` | Área a la que pertenece | FK a `areas` |
| `codigo` | `VARCHAR(30)` | Código interno del centro de costo | Obligatorio |
| `nombre` | `VARCHAR(100)` | Nombre del centro de costo | Obligatorio |
| `descripcion` | `VARCHAR(255)` | Descripción breve | Opcional |
| `activo` | `BOOLEAN` | Indica si está disponible para captura | Obligatorio |
| `creado_en` | `DATETIME` | Fecha de creación | Auditoría |
| `creado_por` | `INT` | Usuario que creó el registro | FK a `usuarios` |
| `actualizado_en` | `DATETIME` | Fecha de modificación | Auditoría |
| `actualizado_por` | `INT` | Usuario que modificó el registro | FK a `usuarios` |
| `eliminado_en` | `DATETIME` | Baja lógica | Soft delete |

---

## 3.5 Tabla: `presupuestos`

### Descripción general

Almacena el presupuesto mensual asignado a cada área. Su función es permitir comparativos entre presupuesto asignado y gasto real aprobado.

### Relaciones

- Pertenece a un área.
- Se consulta desde el flujo de aprobación.
- Se usa en reportes operativos.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_presupuesto` |
| FK | `id_area` |
| Única compuesta | `id_area`, `anio`, `mes` |
| Índices | `idx_presupuesto_area_periodo` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_presupuesto` | `INT` | Identificador del presupuesto | PK, autoincremental |
| `id_area` | `INT` | Área del presupuesto | FK a `areas` |
| `anio` | `YEAR` | Año presupuestal | Obligatorio |
| `mes` | `TINYINT` | Mes presupuestal, de 1 a 12 | Obligatorio |
| `monto_presupuestado` | `DECIMAL(12,2)` | Presupuesto asignado al área | Obligatorio |
| `observaciones` | `TEXT` | Comentarios administrativos | Opcional |
| `activo` | `BOOLEAN` | Indica si el presupuesto está vigente | Obligatorio |
| `creado_en` | `DATETIME` | Fecha de creación | Auditoría |
| `creado_por` | `INT` | Usuario que creó el registro | FK a `usuarios` |
| `actualizado_en` | `DATETIME` | Fecha de modificación | Auditoría |
| `actualizado_por` | `INT` | Usuario que modificó el registro | FK a `usuarios` |

---

## 3.6 Tabla: `estatus_gasto`

### Descripción general

Catálogo simple de estados del flujo de gasto.

Estados mínimos:

- Borrador
- Pendiente de aprobación
- Aprobado
- Rechazado

### Relaciones

- Se relaciona con `gastos_cabecera`.
- Se usa para controlar el flujo operativo.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_estatus_gasto` |
| Única | `clave` |
| Índices | `idx_estatus_activo` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_estatus_gasto` | `INT` | Identificador del estado | PK, autoincremental |
| `clave` | `VARCHAR(30)` | Clave técnica del estado | Única, obligatoria |
| `nombre` | `VARCHAR(80)` | Nombre visible del estado | Obligatorio |
| `descripcion` | `VARCHAR(255)` | Descripción del estado | Opcional |
| `orden_flujo` | `TINYINT` | Orden lógico del estado | Obligatorio |
| `activo` | `BOOLEAN` | Indica si el estado está vigente | Obligatorio |

---

## 3.7 Tabla: `categorias_gasto`

### Descripción general

Catálogo para clasificar los gastos por tipo operativo, por ejemplo viáticos, transporte, alimentos, materiales, servicios o mantenimiento.

### Relaciones

- Se relaciona con `gastos_detalle`.
- Permite reportes por categoría.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_categoria_gasto` |
| Única | `nombre` |
| Índices | `idx_categoria_activo` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_categoria_gasto` | `INT` | Identificador de la categoría | PK, autoincremental |
| `nombre` | `VARCHAR(100)` | Nombre de la categoría | Único, obligatorio |
| `descripcion` | `VARCHAR(255)` | Descripción de uso | Opcional |
| `activo` | `BOOLEAN` | Indica si puede usarse en captura | Obligatorio |
| `creado_en` | `DATETIME` | Fecha de creación | Auditoría |
| `creado_por` | `INT` | Usuario que creó el registro | FK a `usuarios` |
| `actualizado_en` | `DATETIME` | Fecha de modificación | Auditoría |
| `actualizado_por` | `INT` | Usuario que modificó el registro | FK a `usuarios` |
| `eliminado_en` | `DATETIME` | Baja lógica | Soft delete |

---

## 3.8 Tabla: `conceptos_deducibilidad`

### Descripción general

Catálogo que define la clasificación fiscal básica de cada detalle del gasto.

Valores recomendados:

- Deducible
- No deducible
- Parcialmente deducible

Se mantiene como catálogo para evitar textos libres y permitir reportes claros.

### Relaciones

- Se relaciona con `gastos_detalle`.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_concepto_deducibilidad` |
| Única | `clave` |
| Índices | `idx_deducibilidad_activo` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_concepto_deducibilidad` | `INT` | Identificador del concepto | PK, autoincremental |
| `clave` | `VARCHAR(30)` | Clave técnica | Única, obligatoria |
| `nombre` | `VARCHAR(80)` | Nombre visible | Obligatorio |
| `descripcion` | `VARCHAR(255)` | Explicación del criterio | Opcional |
| `activo` | `BOOLEAN` | Indica si puede usarse | Obligatorio |

---

## 3.9 Tabla: `proveedores`

### Descripción general

Almacena proveedores relacionados con CFDI o gastos. Se mantiene como tabla separada para evitar repetir datos fiscales en cada comprobación.

### Relaciones

- Puede estar asociado a muchos CFDI.
- Puede estar asociado a muchas cabeceras de gasto.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_proveedor` |
| Índices | `idx_proveedor_rfc`, `idx_proveedor_nombre`, `idx_proveedor_activo` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_proveedor` | `INT` | Identificador del proveedor | PK, autoincremental |
| `nombre` | `VARCHAR(150)` | Nombre o razón social | Obligatorio |
| `rfc` | `VARCHAR(255)` | RFC del proveedor | Cifrado obligatorio |
| `correo` | `VARCHAR(120)` | Correo de contacto | Opcional |
| `telefono` | `VARCHAR(30)` | Teléfono de contacto | Opcional |
| `activo` | `BOOLEAN` | Indica si está disponible | Obligatorio |
| `creado_en` | `DATETIME` | Fecha de creación | Auditoría |
| `creado_por` | `INT` | Usuario que creó el registro | FK a `usuarios` |
| `actualizado_en` | `DATETIME` | Fecha de modificación | Auditoría |
| `actualizado_por` | `INT` | Usuario que modificó el registro | FK a `usuarios` |
| `eliminado_en` | `DATETIME` | Baja lógica | Soft delete |

---

## 3.10 Tabla: `gastos_cabecera`

### Descripción general

Representa la comprobación general del gasto. Es la entidad principal del flujo.

Una cabecera puede tener varios detalles y uno o varios CFDI/XML asociados.

### Relaciones

- Pertenece a un usuario capturista.
- Pertenece a un área.
- Pertenece a un centro de costos.
- Tiene un estado.
- Puede estar asociada a un proveedor principal.
- Tiene muchos detalles.
- Tiene uno o varios CFDI.
- Genera movimientos de bitácora.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_gasto_cabecera` |
| FK | `id_usuario`, `id_area`, `id_centro_costo`, `id_estatus_gasto`, `id_proveedor` |
| Índices | `idx_gasto_area_fecha`, `idx_gasto_estatus`, `idx_gasto_usuario`, `idx_gasto_centro_costo`, `idx_gasto_periodo` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_gasto_cabecera` | `INT` | Identificador de la comprobación | PK, autoincremental |
| `folio` | `VARCHAR(30)` | Folio interno del gasto | Único, obligatorio |
| `id_usuario` | `INT` | Usuario capturista | FK a `usuarios` |
| `id_area` | `INT` | Área responsable del gasto | FK a `areas` |
| `id_centro_costo` | `INT` | Centro de costo asignado | FK a `centros_costos` |
| `id_estatus_gasto` | `INT` | Estado actual del gasto | FK a `estatus_gasto` |
| `id_proveedor` | `INT` | Proveedor principal | FK a `proveedores`, opcional |
| `fecha_gasto` | `DATE` | Fecha del gasto o comprobación | Obligatorio |
| `concepto_general` | `VARCHAR(255)` | Descripción general del gasto | Obligatorio |
| `subtotal` | `DECIMAL(12,2)` | Suma de subtotales de detalles | Calculado |
| `iva` | `DECIMAL(12,2)` | Suma de IVA de detalles | Calculado |
| `total` | `DECIMAL(12,2)` | Total general calculado | Calculado |
| `total_deducible` | `DECIMAL(12,2)` | Suma de importes deducibles | Calculado |
| `total_no_deducible` | `DECIMAL(12,2)` | Suma de importes no deducibles | Calculado |
| `observaciones` | `TEXT` | Comentarios del capturista | Opcional |
| `observaciones_aprobacion` | `TEXT` | Comentarios del jefe de área | Opcional |
| `fecha_envio_aprobacion` | `DATETIME` | Fecha en que se envió a aprobación | Opcional |
| `fecha_aprobacion` | `DATETIME` | Fecha de aprobación | Opcional |
| `aprobado_por` | `INT` | Usuario que aprobó o rechazó | FK a `usuarios`, opcional |
| `creado_en` | `DATETIME` | Fecha de creación | Auditoría |
| `creado_por` | `INT` | Usuario que creó el registro | FK a `usuarios` |
| `actualizado_en` | `DATETIME` | Fecha de modificación | Auditoría |
| `actualizado_por` | `INT` | Usuario que modificó el registro | FK a `usuarios` |
| `eliminado_en` | `DATETIME` | Baja lógica | Soft delete |

---

## 3.11 Tabla: `gastos_detalle`

### Descripción general

Representa las partidas o conceptos específicos de una comprobación de gasto.

Cada detalle permite clasificar categoría, deducibilidad, importe deducible e importe no deducible.

### Relaciones

- Pertenece a una cabecera de gasto.
- Pertenece a una categoría.
- Pertenece a un concepto de deducibilidad.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_gasto_detalle` |
| FK | `id_gasto_cabecera`, `id_categoria_gasto`, `id_concepto_deducibilidad` |
| Índices | `idx_detalle_cabecera`, `idx_detalle_categoria`, `idx_detalle_deducibilidad` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_gasto_detalle` | `INT` | Identificador del detalle | PK, autoincremental |
| `id_gasto_cabecera` | `INT` | Comprobación a la que pertenece | FK a `gastos_cabecera` |
| `id_categoria_gasto` | `INT` | Categoría del gasto | FK a `categorias_gasto` |
| `id_concepto_deducibilidad` | `INT` | Clasificación de deducibilidad | FK a `conceptos_deducibilidad` |
| `descripcion` | `VARCHAR(255)` | Descripción del concepto | Obligatorio |
| `cantidad` | `DECIMAL(10,2)` | Cantidad del concepto | Obligatorio |
| `precio_unitario` | `DECIMAL(12,2)` | Precio unitario | Obligatorio |
| `subtotal` | `DECIMAL(12,2)` | Subtotal del detalle | Calculado o capturado |
| `iva` | `DECIMAL(12,2)` | IVA del detalle | Opcional |
| `total` | `DECIMAL(12,2)` | Total del detalle | Obligatorio |
| `importe_deducible` | `DECIMAL(12,2)` | Parte deducible del detalle | Obligatorio |
| `importe_no_deducible` | `DECIMAL(12,2)` | Parte no deducible del detalle | Obligatorio |
| `observaciones` | `TEXT` | Comentarios sobre el detalle | Opcional |
| `creado_en` | `DATETIME` | Fecha de creación | Auditoría |
| `actualizado_en` | `DATETIME` | Fecha de modificación | Auditoría |

---

## 3.12 Tabla: `facturas_cfdi`

### Descripción general

Almacena la información de los CFDI/XML asociados a una cabecera de gasto.

Una cabecera puede tener uno o varios CFDI. Los detalles pueden ser capturados manualmente o derivados del XML, pero el XML no se relaciona directamente con cada detalle para evitar sobreingeniería.

### Relaciones

- Pertenece a `gastos_cabecera`.
- Puede estar relacionado con `proveedores`.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_factura_cfdi` |
| FK | `id_gasto_cabecera`, `id_proveedor` |
| Única recomendada | `uuid` |
| Índices | `idx_cfdi_gasto`, `idx_cfdi_proveedor`, `idx_cfdi_fecha`, `idx_cfdi_uuid` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_factura_cfdi` | `INT` | Identificador del CFDI | PK, autoincremental |
| `id_gasto_cabecera` | `INT` | Cabecera asociada | FK a `gastos_cabecera` |
| `id_proveedor` | `INT` | Proveedor emisor | FK a `proveedores`, opcional |
| `uuid` | `CHAR(36)` | UUID fiscal del CFDI | Único cuando exista |
| `serie` | `VARCHAR(30)` | Serie del CFDI | Opcional |
| `folio` | `VARCHAR(30)` | Folio del CFDI | Opcional |
| `fecha_emision` | `DATETIME` | Fecha de emisión del CFDI | Opcional |
| `rfc_emisor` | `VARCHAR(255)` | RFC del emisor | Cifrado obligatorio |
| `nombre_emisor` | `VARCHAR(150)` | Nombre del emisor | Opcional |
| `rfc_receptor` | `VARCHAR(255)` | RFC del receptor | Cifrado obligatorio |
| `nombre_receptor` | `VARCHAR(150)` | Nombre del receptor | Opcional |
| `subtotal` | `DECIMAL(12,2)` | Subtotal del CFDI | Opcional |
| `iva` | `DECIMAL(12,2)` | IVA del CFDI | Opcional |
| `total` | `DECIMAL(12,2)` | Total del CFDI | Opcional |
| `moneda` | `VARCHAR(10)` | Moneda del CFDI | Opcional |
| `ruta_xml` | `VARCHAR(255)` | Ruta interna del archivo XML | Protegida, no pública |
| `nombre_archivo` | `VARCHAR(150)` | Nombre original del archivo | Obligatorio |
| `hash_archivo` | `VARCHAR(255)` | Huella del archivo para integridad | Recomendado |
| `procesado` | `BOOLEAN` | Indica si se extrajeron datos del XML | Obligatorio |
| `creado_en` | `DATETIME` | Fecha de carga | Auditoría |
| `creado_por` | `INT` | Usuario que cargó el XML | FK a `usuarios` |
| `eliminado_en` | `DATETIME` | Baja lógica | Soft delete |

---

## 3.13 Tabla: `bitacora_movimientos`

### Descripción general

Registra eventos relevantes del sistema, especialmente cambios en el ciclo de vida del gasto.

Esta tabla no debe editarse desde la interfaz funcional.

### Relaciones

- Puede relacionarse con una cabecera de gasto.
- Registra al usuario que realizó la acción.
- Sirve como soporte de auditoría.

### Llaves e índices

| Tipo | Campo |
|---|---|
| PK | `id_bitacora` |
| FK | `id_usuario`, `id_gasto_cabecera` |
| Índices | `idx_bitacora_usuario`, `idx_bitacora_gasto`, `idx_bitacora_fecha`, `idx_bitacora_accion` |

### Columnas

| Nombre | Tipo | Descripción | Atributos |
|---|---|---|---|
| `id_bitacora` | `INT` | Identificador del movimiento | PK, autoincremental |
| `id_usuario` | `INT` | Usuario que realizó la acción | FK a `usuarios` |
| `id_gasto_cabecera` | `INT` | Gasto relacionado | FK a `gastos_cabecera`, opcional |
| `accion` | `VARCHAR(80)` | Acción realizada | Obligatorio |
| `descripcion` | `TEXT` | Descripción del movimiento | Obligatorio |
| `estatus_anterior` | `VARCHAR(50)` | Estado previo del gasto | Opcional |
| `estatus_nuevo` | `VARCHAR(50)` | Nuevo estado del gasto | Opcional |
| `fecha_movimiento` | `DATETIME` | Fecha y hora del evento | Obligatorio |
| `ip_origen` | `VARCHAR(45)` | IP desde donde se realizó la acción | Opcional |
| `user_agent` | `VARCHAR(255)` | Información del navegador | Opcional |

---

# 4. Relaciones Principales del Modelo

| Relación | Cardinalidad |
|---|---|
| Un rol tiene muchos usuarios | 1:N |
| Un área tiene muchos usuarios | 1:N |
| Un área tiene muchos centros de costos | 1:N |
| Un área tiene muchos presupuestos mensuales | 1:N |
| Un usuario registra muchas cabeceras de gasto | 1:N |
| Una cabecera tiene muchos detalles | 1:N |
| Una cabecera tiene uno o varios CFDI/XML | 1:N |
| Un proveedor puede estar en muchos CFDI | 1:N |
| Una categoría puede estar en muchos detalles | 1:N |
| Un concepto de deducibilidad puede estar en muchos detalles | 1:N |
| Una cabecera genera muchos movimientos de bitácora | 1:N |

---

# 5. Criterio de Deducibilidad

La deducibilidad se maneja a nivel de detalle, no a nivel de cabecera.

Esto permite que una comprobación tenga partidas deducibles, no deducibles o parcialmente deducibles.

## Ejemplo conceptual

| Detalle | Concepto deducibilidad | Total | Deducible | No deducible |
|---|---|---:|---:|---:|
| Hospedaje | Deducible | 2,000.00 | 2,000.00 | 0.00 |
| Consumo no autorizado | No deducible | 500.00 | 0.00 | 500.00 |
| Alimentos | Parcialmente deducible | 1,000.00 | 700.00 | 300.00 |

La cabecera solo almacena los acumulados:

- `total`
- `total_deducible`
- `total_no_deducible`

---

# 6. Reglas de Integridad y Validaciones

## 6.1 Reglas generales

- Todo gasto debe tener una cabecera.
- Toda cabecera debe tener al menos un detalle antes de enviarse a aprobación.
- Toda cabecera debe pertenecer a un usuario, área y centro de costos.
- El centro de costos seleccionado debe pertenecer al área de la cabecera.
- El usuario capturista solo debe registrar gastos de su área.
- Un jefe de área solo debe aprobar gastos de su propia área.

## 6.2 Reglas de CFDI/XML

- Una cabecera puede tener uno o varios CFDI/XML.
- El XML debe almacenarse fuera de rutas públicas.
- El sistema solo extrae datos clave del XML.
- No se valida el estatus SAT.
- No se valida cancelación del CFDI.
- No se realiza timbrado ni emisión de CFDI.
- El UUID debe ser único cuando exista.
- El archivo XML debe tener extensión y tipo permitido.
- Se debe limitar el tamaño máximo del archivo.

## 6.3 Reglas de totales

- El total de cabecera debe calcularse desde `gastos_detalle`.
- `subtotal`, `iva`, `total`, `total_deducible` y `total_no_deducible` no deben depender de captura manual en cabecera.
- La suma de `importe_deducible` e `importe_no_deducible` debe coincidir con el total del detalle.
- El total de la cabecera debe coincidir con la suma de los detalles.
- Los importes no pueden ser negativos.

## 6.4 Reglas de flujo de aprobación

Estados mínimos:

1. Borrador  
2. Pendiente de aprobación  
3. Aprobado  
4. Rechazado  

Reglas:

- Solo los gastos en borrador pueden editarse libremente.
- Solo los gastos en borrador pueden enviarse a aprobación.
- Solo los gastos pendientes pueden aprobarse o rechazarse.
- Todo rechazo debe incluir observaciones.
- Un gasto rechazado puede corregirse y reenviarse.
- Un gasto aprobado no debe modificarse sin dejar registro en bitácora.

## 6.5 Reglas de presupuesto

- Cada área debe tener máximo un presupuesto por mes y año.
- El presupuesto se consulta de forma informativa en la aprobación.
- El gasto real se calcula con gastos aprobados.
- No se recomienda bloquear automáticamente gastos por exceder presupuesto, salvo decisión posterior del negocio.

## 6.6 Reglas de auditoría

Deben registrarse en `bitacora_movimientos` al menos estas acciones:

- Creación de gasto.
- Carga de XML.
- Edición de gasto.
- Envío a aprobación.
- Aprobación.
- Rechazo.
- Corrección.
- Cambio de estado.
- Eliminación lógica.
- Cambios en catálogos relevantes.

## 6.7 Reglas de seguridad

- Las contraseñas deben almacenarse con hash seguro.
- Los RFC deben cifrarse.
- El XML debe protegerse por permisos del sistema operativo.
- No debe permitirse acceso público directo a CFDI/XML.
- Las operaciones críticas deben validar sesión, rol y permisos.
- La bitácora no debe modificarse desde pantallas funcionales.

---

# 7. Índices Recomendados

## 7.1 Índices por llaves foráneas

Toda FK debe tener índice:

| Tabla | Índice recomendado |
|---|---|
| `usuarios` | `id_rol`, `id_area` |
| `areas` | `id_jefe_area` |
| `centros_costos` | `id_area` |
| `presupuestos` | `id_area` |
| `gastos_cabecera` | `id_usuario`, `id_area`, `id_centro_costo`, `id_estatus_gasto`, `id_proveedor` |
| `gastos_detalle` | `id_gasto_cabecera`, `id_categoria_gasto`, `id_concepto_deducibilidad` |
| `facturas_cfdi` | `id_gasto_cabecera`, `id_proveedor` |
| `bitacora_movimientos` | `id_usuario`, `id_gasto_cabecera` |

## 7.2 Índices para consultas frecuentes

| Consulta frecuente | Índice recomendado |
|---|---|
| Gastos por área y fecha | `idx_gasto_area_fecha` |
| Gastos por estado | `idx_gasto_estatus` |
| Gastos por usuario capturista | `idx_gasto_usuario` |
| Gastos por centro de costo | `idx_gasto_centro_costo` |
| Presupuesto por área, año y mes | `idx_presupuesto_area_periodo` |
| CFDI por UUID | `idx_cfdi_uuid` |
| CFDI por proveedor | `idx_cfdi_proveedor` |
| Bitácora por gasto | `idx_bitacora_gasto` |
| Bitácora por fecha | `idx_bitacora_fecha` |

## 7.3 Índices únicos recomendados

| Tabla | Campo |
|---|---|
| `roles` | `nombre` |
| `usuarios` | `correo` |
| `areas` | `nombre` |
| `centros_costos` | `id_area`, `codigo` |
| `presupuestos` | `id_area`, `anio`, `mes` |
| `estatus_gasto` | `clave` |
| `conceptos_deducibilidad` | `clave` |
| `facturas_cfdi` | `uuid` |
| `gastos_cabecera` | `folio` |

## 7.4 Criterio anti sobreingeniería para índices

No se recomienda crear índices para todos los campos.

Evitar índices en:

- Campos `TEXT`.
- Observaciones.
- Descripciones largas.
- Campos booleanos con baja variación, salvo que se combinen con otros filtros.
- Campos que no se usen en búsquedas, filtros o relaciones.

---

# 8. Resumen del Modelo Propuesto

El modelo queda compuesto por una estructura principal de **gastos cabecera–detalle**, donde la cabecera representa la comprobación general y el detalle representa las partidas específicas.

Los CFDI/XML se relacionan con la cabecera, permitiendo uno o varios comprobantes por gasto sin complicar la relación con cada detalle.

La deducibilidad se controla en cada detalle mediante un catálogo simple y dos importes:

- `importe_deducible`
- `importe_no_deducible`

Con esto, el sistema puede generar reportes financieros útiles sin caer en una estructura fiscal demasiado compleja.

El diseño cumple con la frase guía:

> “Tan simple como sea posible, pero suficientemente estructurado para crecer.”


---
# Arbol de directorios de backend 

```
proyecto/
├── api/                               ← Carpeta raíz del backend (punto de entrada para el dominio)
│   ├── .htaccess                      ← Reglas de redirección para el router (Apache)
│   ├── index.php                      ← Bootstrap inicial de la API (carga config, router, etc.)
│   ├── config/                        ← Configuración de la aplicación
│   │   ├── app.php                    ← Configuración general (zona horaria, debug, entorno)
│   │   ├── database.php               ← Configuración de conexión a MySQL/MariaDB
│   │   ├── cors.php                   ← Configuración de políticas CORS para el frontend
│   │   └── storage.php                ← Rutas de almacenamiento (XML, logs, temp)
│   ├── core/                          ← Núcleo reutilizable del framework interno
│   │   ├── Router.php                 ← Clase para interpretar rutas y delegar a controladores
│   │   ├── Controller.php             ← Clase base para controladores (carga servicios, respuestas)
│   │   ├── Model.php                  ← Clase base para interactuar con repositorios/DB
│   │   ├── Middleware.php             ← Clase base para middlewares (auth, roles, csrf)
│   │   ├── Request.php                ← Abstracción de la solicitud HTTP (validación básica)
│   │   └── Response.php               ← Manejador de respuestas JSON (códigos, headers)
│   ├── middleware/                    ← Capa de interceptación de peticiones
│   │   ├── AuthMiddleware.php         ← Verifica sesión y token de autenticación
│   │   ├── RoleMiddleware.php         ← Valida roles y permisos específicos (RBAC)
│   │   ├── CsrfMiddleware.php         ← Protección CSRF para acciones POST/PUT/DELETE
│   │   ├── LogMiddleware.php          ← Registra solicitudes críticas en bitácora
│   │   └── CorsMiddleware.php         ← Aplica reglas CORS configuradas
│   ├── modules/                       ← Módulos organizados por feature del negocio
│   │   ├── Auth/                      ← Módulo de autenticación y sesión
│   │   │   ├── Controllers/           ← Controladores del módulo (LoginController, LogoutController)
│   │   │   ├── Services/              ← Reglas de negocio (autenticación, generación de token)
│   │   │   ├── Repositories/          ← Consultas específicas a la tabla de usuarios
│   │   │   └── Routes.php             ← Rutas exclusivas del módulo (login, logout, refresh)
│   │   ├── User/                      ← Módulo de gestión de usuarios
│   │   │   ├── Controllers/           ← ABM de usuarios, cambio de contraseña
│   │   │   ├── Services/              ← Lógica de activación, desactivación, asignación de área
│   │   │   ├── Repositories/          ← Acceso a tabla usuarios y roles
│   │   │   └── Routes.php             ← Rutas protegidas por rol de administrador
│   │   ├── Area/                      ← Módulo de áreas y organización
│   │   │   ├── Controllers/           ← CRUD de áreas, asignación de jefes
│   │   │   ├── Services/              ← Validación de usuarios jefes de área
│   │   │   ├── Repositories/          ← Consultas de áreas, centros de costos relacionados
│   │   │   └── Routes.php             ← Rutas para administración de áreas
│   │   ├── CostCenter/                ← Módulo de centros de costos
│   │   │   ├── Controllers/           ← CRUD de centros de costos
│   │   │   ├── Services/              ← Relación con áreas y activación para captura
│   │   │   ├── Repositories/          ← Filtros por área y activos
│   │   │   └── Routes.php             ← Rutas protegidas
│   │   ├── Budget/                    ← Módulo de presupuestos mensuales
│   │   │   ├── Controllers/           ← Asignación y consulta de presupuestos por área
│   │   │   ├── Services/              ← Cálculo de consumo presupuestal
│   │   │   ├── Repositories/          ← Acceso a tabla presupuestos
│   │   │   └── Routes.php             ← Rutas para administración y consulta
│   │   ├── Expense/                   ← Módulo principal de gastos
│   │   │   ├── Controllers/           ← Creación, edición, consulta de gastos (cabecera)
│   │   │   ├── Services/              ← Flujo completo: borrador, envío, aprobación, rechazo
│   │   │   ├── Repositories/          ← Acceso a cabeceras, detalles y estados
│   │   │   └── Routes.php             ← Rutas de captura, consulta y acciones masivas
│   │   ├── ExpenseLine/               ← Módulo de líneas/detalle de gasto
│   │   │   ├── Controllers/           ← ABM de líneas asociadas a una cabecera
│   │   │   ├── Services/              ← Cálculo de deducibles y totales
│   │   │   ├── Repositories/          ← Acceso a tabla gastos_detalle
│   │   │   └── Routes.php             ← Rutas anidadas bajo un gasto
│   │   ├── Document/                  ← Módulo de documentos y XML CFDI
│   │   │   ├── Controllers/           ← Carga de archivos, extracción de datos, eliminación
│   │   │   ├── Services/              ← Parseo seguro de XML, limpieza de entidades externas
│   │   │   ├── Repositories/          ← Acceso a tabla facturas_cfdi
│   │   │   └── Routes.php             ← Rutas para subida y consulta de metadatos
│   │   ├── Approval/                  ← Módulo de flujo de autorización
│   │   │   ├── Controllers/           ← Bandeja de pendientes, aprobación y rechazo
│   │   │   ├── Services/              ← Validación de reglas de área y presupuesto
│   │   │   ├── Repositories/          ← Consultas específicas para jefe de área
│   │   │   └── Routes.php             ← Rutas para obtener y resolver solicitudes
│   │   ├── Report/                    ← Módulo de reportes operativos
│   │   │   ├── Controllers/           ← Generación de reportes filtrados
│   │   │   ├── Services/              ← Construcción de datasets (presupuesto vs real)
│   │   │   ├── Repositories/          ← Consultas complejas de gasto acumulado
│   │   │   └── Routes.php             ← Rutas de exportación (CSV/JSON) y tableros
│   │   ├── Audit/                     ← Módulo de auditoría y bitácora
│   │   │   ├── Controllers/           ← Consulta de movimientos (solo lectura)
│   │   │   ├── Services/              ← Registro central de eventos críticos
│   │   │   ├── Repositories/          ← Acceso a bitacora_movimientos
│   │   │   └── Routes.php             ← Rutas para administrador y cuentas por pagar
│   │   └── Catalog/                   ← Módulo de catálogos (categorías, deducibilidad, estatus)
│   │       ├── Controllers/           ← CRUD de catálogos simples
│   │       ├── Services/              ← Carga inicial y activación de valores por defecto
│   │       ├── Repositories/          ← Acceso a tablas catálogo
│   │       └── Routes.php             ← Rutas protegidas para administradores
│   ├── routes/                        ← Agregador central de rutas
│   │   └── api.php                    ← Importa rutas de todos los módulos (Auth, User, Area, etc.)
│   ├── storage/                       ← Almacenamiento privado (no público)
│   │   ├── xml/                       ← Archivos XML CFDI cargados por usuarios
│   │   ├── logs/                      ← Logs de errores del sistema y auditoría técnica
│   │   └── temp/                      ← Archivos temporales (procesamiento XML)
│   ├── tests/                         ← Pruebas unitarias e integración
│   │   ├── Unit/                      ← Pruebas de servicios y utilidades aisladas
│   │   ├── Integration/               ← Pruebas de endpoints reales (API)
│   │   ├── Fixtures/                  ← Datos de prueba (XML de ejemplo, JSON)
│   │   └── bootstrap.php              ← Configuración inicial para ejecutar tests
│   ├── utils/                         ← Utilidades transversales
│   │   ├── JwtHelper.php              ← Generación y validación de tokens (autenticación local)
│   │   ├── HashHelper.php             ← Wrapper seguro para password_hash y verificación
│   │   ├── XmlHelper.php              ← Funciones seguras de lectura y extracción de CFDI
│   │   ├── FileHelper.php             ← Manejo de rutas, validación de tipos y tamaños
│   │   └── DateHelper.php             ← Conversión de zonas horarias, formatos normalizados
│   ├── docs/                          ← Documentación técnica para frontend y equipos
│   │   ├── swagger.yaml               ← Especificación OpenAPI 3.0 del contrato completo
│   │   ├── swagger-ui/                ← Archivos estáticos para visualizar swagger.yaml
│   │   └── postman_collection.json    ← Colección de Postman para pruebas manuales
│   ├── database/                      ← Capa de persistencia y estructura
│   │   ├── migrations/                ← Scripts de creación y evolución de tablas
│   │   │   ├── 20250101000001_create_roles_table.php
│   │   │   ├── 20250101000002_create_usuarios_table.php
│   │   │   └── ...                    ← Resto de migraciones ordenadas por timestamp
│   │   ├── seeders/                   ← Datos iniciales (roles por defecto, estatus, catálogos)
│   │   │   ├── RolesSeeder.php        ← Carga de roles mínimos (admin, capturista, jefe, cuentas)
│   │   │   ├── StatusSeeder.php       ← Carga de estatus de gasto
│   │   │   └── CatalogSeeder.php      ← Carga de categorías y deducibilidad
│   │   └── connection.php             ← Instancia de PDO (Singleton) usando config/database.php
│   └── .env.example                   ← Plantilla de variables de entorno (DB, JWT, storage, debug)
├── web/                               ← Carpeta raíz del frontend (estática, no se detalla por requerimiento)
└── vendor/                            ← Dependencias de Composer (autoload, librerías externas)
    ├── composer.json                  ← Definición de dependencias PHP (sin framework)
    └── composer.lock                  ← Versiones exactas bloqueadas
```

---
# Arbol de directorios frontend
```
empresa-gastos/                                      ← carpeta raíz publicada en dominio/subdominio
├── web/                                             ← frontend completo de la aplicación
│   ├── index.html                                   ← punto de entrada principal del frontend
│   ├── login.html                                   ← pantalla pública de autenticación
│   ├── dashboard.html                               ← pantalla inicial después de autenticación
│   ├── .htaccess                                    ← reglas de rutas, seguridad y acceso frontend
│   │
│   ├── assets/                                      ← recursos estáticos generales
│   │   ├── images/                                  ← imágenes del sistema
│   │   │   ├── logo/                                ← logotipos institucionales
│   │   │   ├── backgrounds/                         ← fondos visuales
│   │   │   ├── icons/                               ← iconografía personalizada
│   │   │   └── modules/                             ← imágenes específicas por módulo
│   │   │
│   │   ├── fonts/                                   ← tipografías locales
│   │   ├── uploads/                                 ← archivos temporales frontend
│   │   │   └── xml-preview/                         ← vista previa temporal de XML cargados
│   │   │
│   │   └── vendors/                                 ← librerías de terceros
│   │       ├── jquery/                              ← librería jQuery
│   │       ├── bootstrap/                           ← Bootstrap CSS y JS
│   │       ├── datatables/                          ← DataTables y extensiones
│   │       ├── sweetalert/                          ← alertas visuales
│   │       ├── toastr/                              ← notificaciones toast
│   │       ├── select2/                             ← selects enriquecidos
│   │       ├── moment/                              ← manejo de fechas
│   │       └── chartjs/                             ← gráficas para reportes
│   │
│   ├── styles/                                      ← estilos exclusivos de presentación
│   │   ├── main.css                                 ← estilos globales principales
│   │   ├── reset.css                                ← normalización visual
│   │   ├── variables.css                            ← variables CSS reutilizables
│   │   ├── layout.css                               ← estructura visual general
│   │   ├── typography.css                           ← estilos tipográficos
│   │   ├── utilities.css                            ← clases utilitarias visuales
│   │   ├── animations.css                           ← animaciones visuales
│   │   │
│   │   ├── themes/                                  ← temas visuales del sistema
│   │   │   ├── default.css                          ← tema principal
│   │   │   └── dark.css                             ← tema oscuro opcional
│   │   │
│   │   ├── components/                              ← estilos por componente reutilizable
│   │   │   ├── navbar.css                           ← estilos barra superior
│   │   │   ├── sidebar.css                          ← estilos menú lateral
│   │   │   ├── cards.css                            ← estilos tarjetas Bootstrap
│   │   │   ├── forms.css                            ← estilos formularios
│   │   │   ├── tables.css                           ← estilos tablas y DataTables
│   │   │   ├── modals.css                           ← estilos ventanas modales
│   │   │   ├── buttons.css                          ← estilos botones
│   │   │   ├── alerts.css                           ← estilos alertas visuales
│   │   │   └── badges.css                           ← estilos indicadores y etiquetas
│   │   │
│   │   └── pages/                                   ← estilos específicos por pantalla
│   │       ├── login.css                            ← estilos login
│   │       ├── dashboard.css                        ← estilos dashboard
│   │       ├── gastos.css                           ← estilos módulo gastos
│   │       ├── aprobaciones.css                     ← estilos bandeja aprobación
│   │       ├── reportes.css                         ← estilos reportes
│   │       ├── administracion.css                   ← estilos administración
│   │       └── catalogos.css                        ← estilos catálogos
│   │
│   ├── scripts/                                     ← lógica JavaScript del frontend
│   │   ├── app.js                                   ← inicialización general de la aplicación
│   │   ├── bootstrap.js                             ← configuración inicial Bootstrap
│   │   ├── datatables.js                            ← configuración global DataTables
│   │   ├── session.js                               ← manejo global de sesión
│   │   ├── auth.js                                  ← control frontend autenticación
│   │   ├── permissions.js                           ← validaciones visuales por rol
│   │   ├── notifications.js                         ← manejo frontend de notificaciones
│   │   ├── events.js                                ← registro centralizado de eventos jQuery
│   │   │
│   │   ├── config/                                  ← configuración técnica frontend
│   │   │   ├── api.config.js                        ← URL base y configuración API
│   │   │   ├── app.config.js                        ← configuración general frontend
│   │   │   ├── datatable.config.js                  ← configuración común DataTables
│   │   │   ├── routes.config.js                     ← rutas internas frontend
│   │   │   └── environment.config.js                ← variables por ambiente
│   │   │
│   │   ├── router/                                  ← navegación y control de rutas
│   │   │   ├── router.js                            ← controlador de navegación
│   │   │   ├── guards.js                            ← validación acceso por sesión/rol
│   │   │   └── route-map.js                         ← mapa centralizado de rutas
│   │   │
│   │   ├── services/                                ← consumo API vía AJAX jQuery
│   │   │   ├── http.service.js                      ← wrapper AJAX reutilizable
│   │   │   ├── auth.service.js                      ← consumo API autenticación
│   │   │   ├── usuarios.service.js                  ← consumo API usuarios
│   │   │   ├── roles.service.js                     ← consumo API roles
│   │   │   ├── areas.service.js                     ← consumo API áreas
│   │   │   ├── centros-costos.service.js            ← consumo API centros costos
│   │   │   ├── presupuestos.service.js              ← consumo API presupuestos
│   │   │   ├── gastos.service.js                    ← consumo API gastos
│   │   │   ├── gastos-detalle.service.js            ← consumo API detalle gastos
│   │   │   ├── cfdi.service.js                      ← consumo API XML/CFDI
│   │   │   ├── aprobaciones.service.js              ← consumo API aprobaciones
│   │   │   ├── reportes.service.js                  ← consumo API reportes
│   │   │   ├── catalogos.service.js                 ← consumo API catálogos
│   │   │   ├── bitacora.service.js                  ← consumo API auditoría
│   │   │   └── notifications.service.js             ← consumo API notificaciones
│   │   │
│   │   ├── interfaces/                              ← contratos visuales/documentales frontend
│   │   │   ├── api-response.interface.js            ← estructura estándar respuestas API
│   │   │   ├── datatable.interface.js               ← configuración estándar DataTables
│   │   │   ├── modal.interface.js                   ← contratos para modales
│   │   │   └── form.interface.js                    ← estructura estándar formularios
│   │   │
│   │   ├── types/                                   ← estructuras de datos reutilizables
│   │   │   ├── usuario.type.js                      ← estructura usuario
│   │   │   ├── rol.type.js                          ← estructura rol
│   │   │   ├── area.type.js                         ← estructura área
│   │   │   ├── presupuesto.type.js                  ← estructura presupuesto
│   │   │   ├── gasto.type.js                        ← estructura gasto
│   │   │   ├── gasto-detalle.type.js                ← estructura detalle gasto
│   │   │   ├── cfdi.type.js                         ← estructura CFDI/XML
│   │   │   └── reporte.type.js                      ← estructura reportes
│   │   │
│   │   ├── context/                                 ← contexto global frontend
│   │   │   ├── auth.context.js                      ← usuario autenticado actual
│   │   │   ├── session.context.js                   ← contexto de sesión
│   │   │   ├── permissions.context.js               ← permisos del usuario
│   │   │   ├── notifications.context.js             ← estado de notificaciones
│   │   │   └── filters.context.js                   ← filtros globales de búsqueda
│   │   │
│   │   ├── helpers/                                 ← utilitarios reutilizables
│   │   │   ├── ajax.helper.js                       ← utilidades AJAX
│   │   │   ├── storage.helper.js                    ← manejo localStorage/sessionStorage
│   │   │   ├── form.helper.js                       ← utilidades formularios
│   │   │   ├── validation.helper.js                 ← validaciones frontend
│   │   │   ├── modal.helper.js                      ← manejo dinámico de modales
│   │   │   ├── datatable.helper.js                  ← helpers DataTables
│   │   │   ├── currency.helper.js                   ← formateo monetario
│   │   │   ├── date.helper.js                       ← manejo de fechas
│   │   │   ├── xml.helper.js                        ← lectura básica frontend XML
│   │   │   ├── notification.helper.js               ← manejo visual de alertas
│   │   │   └── permissions.helper.js                ← validaciones frontend de permisos
│   │   │
│   │   ├── components/                              ← componentes JS reutilizables
│   │   │   ├── navbar/                              ← lógica barra navegación
│   │   │   │   ├── navbar.component.js              ← comportamiento navbar
│   │   │   │   └── navbar.events.js                 ← eventos navbar
│   │   │   │
│   │   │   ├── sidebar/                             ← lógica menú lateral
│   │   │   │   ├── sidebar.component.js             ← comportamiento sidebar
│   │   │   │   └── sidebar.menu.js                  ← construcción dinámica menú
│   │   │   │
│   │   │   ├── datatables/                          ← componentes reutilizables DataTables
│   │   │   │   ├── gastos-table.component.js        ← tabla gastos
│   │   │   │   ├── usuarios-table.component.js      ← tabla usuarios
│   │   │   │   ├── presupuestos-table.component.js  ← tabla presupuestos
│   │   │   │   └── reportes-table.component.js      ← tabla reportes
│   │   │   │
│   │   │   ├── forms/                               ← componentes formularios
│   │   │   │   ├── gasto-form.component.js          ← formulario gasto
│   │   │   │   ├── login-form.component.js          ← formulario login
│   │   │   │   ├── usuario-form.component.js        ← formulario usuario
│   │   │   │   └── presupuesto-form.component.js    ← formulario presupuesto
│   │   │   │
│   │   │   ├── modals/                              ← ventanas modales reutilizables
│   │   │   │   ├── confirm-modal.component.js       ← modal confirmación
│   │   │   │   ├── alert-modal.component.js         ← modal alertas
│   │   │   │   ├── loader-modal.component.js        ← modal carga
│   │   │   │   └── xml-preview-modal.component.js   ← modal vista previa XML
│   │   │   │
│   │   │   ├── cards/                               ← tarjetas Bootstrap reutilizables
│   │   │   │   ├── presupuesto-card.component.js    ← tarjeta resumen presupuesto
│   │   │   │   ├── gasto-card.component.js          ← tarjeta gasto
│   │   │   │   └── report-card.component.js         ← tarjeta indicadores
│   │   │   │
│   │   │   └── notifications/                       ← componentes notificaciones
│   │   │       ├── toast.component.js               ← toast reutilizable
│   │   │       └── alert.component.js               ← alertas Bootstrap
│   │   │
│   │   └── pages/                                   ← lógica específica por pantalla
│   │       ├── login/                               ← lógica módulo login
│   │       │   ├── login.page.js                    ← controlador login
│   │       │   └── login.events.js                  ← eventos login
│   │       │
│   │       ├── dashboard/                           ← lógica dashboard principal
│   │       │   ├── dashboard.page.js                ← controlador dashboard
│   │       │   └── dashboard.widgets.js             ← widgets resumen
│   │       │
│   │       ├── gastos/                              ← lógica módulo gastos
│   │       │   ├── gastos-list.page.js              ← listado gastos
│   │       │   ├── gasto-create.page.js             ← captura gasto
│   │       │   ├── gasto-detail.page.js             ← detalle gasto
│   │       │   ├── gasto-edit.page.js               ← edición gasto
│   │       │   ├── gasto-upload-xml.page.js         ← carga XML
│   │       │   └── gastos.events.js                 ← eventos módulo gastos
│   │       │
│   │       ├── aprobaciones/                        ← lógica aprobaciones
│   │       │   ├── aprobaciones-list.page.js        ← bandeja aprobación
│   │       │   ├── aprobacion-detail.page.js        ← detalle aprobación
│   │       │   └── aprobaciones.events.js           ← eventos aprobación
│   │       │
│   │       ├── reportes/                            ← lógica reportes
│   │       │   ├── reportes.page.js                 ← reportes generales
│   │       │   ├── presupuesto-report.page.js       ← comparativo presupuesto
│   │       │   └── export-report.page.js            ← exportaciones
│   │       │
│   │       ├── administracion/                      ← lógica administración
│   │       │   ├── usuarios.page.js                 ← administración usuarios
│   │       │   ├── roles.page.js                    ← administración roles
│   │       │   ├── areas.page.js                    ← administración áreas
│   │       │   ├── centros-costos.page.js           ← administración centros costos
│   │       │   ├── presupuestos.page.js             ← administración presupuestos
│   │       │   └── administracion.events.js         ← eventos administración
│   │       │
│   │       ├── catalogos/                           ← lógica catálogos
│   │       │   ├── categorias.page.js               ← catálogo categorías gasto
│   │       │   ├── deducibilidad.page.js            ← catálogo deducibilidad
│   │       │   ├── proveedores.page.js              ← catálogo proveedores
│   │       │   └── catalogos.events.js              ← eventos catálogos
│   │       │
│   │       ├── auditoria/                           ← lógica auditoría
│   │       │   ├── bitacora.page.js                 ← consulta bitácora
│   │       │   └── historial.page.js                ← historial gastos
│   │       │
│   │       └── shared/                              ← lógica compartida entre páginas
│   │           ├── layout.page.js                   ← estructura principal aplicación
│   │           ├── sidebar.page.js                  ← interacción sidebar
│   │           └── header.page.js                   ← interacción header
│   │
│   ├── components/                                  ← componentes HTML reutilizables
│   │   ├── layout/                                  ← estructuras globales HTML
│   │   │   ├── header.html                          ← encabezado principal
│   │   │   ├── sidebar.html                         ← menú lateral
│   │   │   ├── footer.html                          ← pie de página
│   │   │   └── layout.html                          ← plantilla general
│   │   │
│   │   ├── tables/                                  ← tablas reutilizables
│   │   │   ├── gastos-table.html                    ← tabla gastos
│   │   │   ├── usuarios-table.html                  ← tabla usuarios
│   │   │   └── reportes-table.html                  ← tabla reportes
│   │   │
│   │   ├── forms/                                   ← formularios reutilizables
│   │   │   ├── gasto-form.html                      ← formulario captura gasto
│   │   │   ├── login-form.html                      ← formulario login
│   │   │   └── usuario-form.html                    ← formulario usuarios
│   │   │
│   │   ├── modals/                                  ← ventanas modales HTML
│   │   │   ├── confirm-modal.html                   ← modal confirmación
│   │   │   ├── alert-modal.html                     ← modal alertas
│   │   │   ├── xml-preview-modal.html               ← modal vista XML
│   │   │   └── loader-modal.html                    ← modal carga
│   │   │
│   │   └── widgets/                                 ← widgets reutilizables
│   │       ├── budget-summary.html                  ← resumen presupuesto
│   │       ├── approval-status.html                 ← indicador estatus
│   │       └── notification-widget.html             ← widget notificaciones
│   │
│   ├── pages/                                       ← pantallas HTML completas
│   │   ├── auth/                                    ← pantallas autenticación
│   │   │   ├── login.html                           ← inicio de sesión
│   │   │   └── recover-password.html                ← recuperación contraseña
│   │   │
│   │   ├── dashboard/                               ← pantallas dashboard
│   │   │   └── dashboard.html                       ← tablero principal
│   │   │
│   │   ├── gastos/                                  ← pantallas gastos
│   │   │   ├── gastos-list.html                     ← listado gastos
│   │   │   ├── gasto-create.html                    ← captura gasto
│   │   │   ├── gasto-detail.html                    ← detalle gasto
│   │   │   ├── gasto-edit.html                      ← edición gasto
│   │   │   └── gasto-upload-xml.html                ← carga XML
│   │   │
│   │   ├── aprobaciones/                            ← pantallas aprobación
│   │   │   ├── aprobaciones-list.html               ← bandeja aprobaciones
│   │   │   └── aprobacion-detail.html               ← detalle aprobación
│   │   │
│   │   ├── reportes/                                ← pantallas reportes
│   │   │   ├── reportes.html                        ← reportes generales
│   │   │   ├── presupuesto-vs-real.html             ← comparativo presupuesto
│   │   │   └── exportaciones.html                   ← exportación reportes
│   │   │
│   │   ├── administracion/                          ← pantallas administración
│   │   │   ├── usuarios.html                        ← administración usuarios
│   │   │   ├── roles.html                           ← administración roles
│   │   │   ├── areas.html                           ← administración áreas
│   │   │   ├── centros-costos.html                  ← administración centros costos
│   │   │   └── presupuestos.html                    ← administración presupuestos
│   │   │
│   │   ├── catalogos/                               ← pantallas catálogos
│   │   │   ├── categorias.html                      ← catálogo categorías
│   │   │   ├── deducibilidad.html                   ← catálogo deducibilidad
│   │   │   ├── proveedores.html                     ← catálogo proveedores
│   │   │   └── estatus-gasto.html                   ← catálogo estados gasto
│   │   │
│   │   ├── auditoria/                               ← pantallas auditoría
│   │   │   ├── bitacora.html                        ← consulta bitácora
│   │   │   └── historial-gastos.html                ← historial gastos
│   │   │
│   │   ├── errors/                                  ← pantallas de error
│   │   │   ├── 401.html                             ← acceso no autorizado
│   │   │   ├── 403.html                             ← acceso prohibido
│   │   │   ├── 404.html                             ← recurso no encontrado
│   │   │   └── 500.html                             ← error interno
│   │   │
│   │   └── shared/                                  ← plantillas reutilizables HTML
│   │       ├── empty-state.html                     ← estado vacío
│   │       ├── loading.html                         ← loader global
│   │       └── no-results.html                      ← mensaje sin resultados
│   │
│   ├── storage/                                     ← almacenamiento controlado frontend
│   │   ├── cache/                                   ← cache temporal frontend
│   │   ├── exports/                                 ← exportaciones descargadas
│   │   └── temp/                                    ← archivos temporales
│   │
│   ├── docs/                                        ← documentación frontend
│   │   ├── architecture/                            ← diagramas y arquitectura
│   │   │   ├── frontend-architecture.md             ← explicación estructura frontend
│   │   │   ├── routing-flow.md                      ← flujo navegación
│   │   │   └── modules-map.md                       ← mapa módulos frontend
│   │   │
│   │   ├── api/                                     ← documentación integración API
│   │   │   ├── endpoints.md                         ← endpoints consumidos
│   │   │   ├── auth-flow.md                         ← flujo autenticación
│   │   │   └── response-structure.md                ← estructura respuestas API
│   │   │
│   │   ├── conventions/                             ← convenciones proyecto
│   │   │   ├── naming-conventions.md                ← convención nombres
│   │   │   ├── folder-structure.md                  ← guía estructura carpetas
│   │   │   └── coding-standards.md                  ← estándares frontend
│   │   │
│   │   └── onboarding/                              ← documentación nuevos desarrolladores
│   │       ├── installation.md                      ← instalación frontend
│   │       ├── environment.md                       ← configuración ambiente
│   │       └── workflow.md                          ← flujo trabajo desarrollo
│   │
│   └── tests/                                       ← pruebas frontend
│       ├── manual/                                  ← casos pruebas manuales
│       │   ├── auth-tests.md                        ← pruebas autenticación
│       │   ├── gastos-tests.md                      ← pruebas gastos
│       │   └── aprobaciones-tests.md                ← pruebas aprobaciones
│       │
│       ├── mocks/                                   ← respuestas mock API
│       │   ├── auth.mock.json                       ← mock autenticación
│       │   ├── gastos.mock.json                     ← mock gastos
│       │   └── reportes.mock.json                   ← mock reportes
│       │
│       └── datasets/                                ← datasets pruebas DataTables
│           ├── gastos.dataset.json                  ← dataset gastos
│           └── usuarios.dataset.json                ← dataset usuarios
│
├── api/                                             ← backend PHP/API REST separado del frontend
│
├── database/                                        ← scripts SQL y respaldos controlados
│   ├── schema/                                      ← definición modelo relacional
│   ├── seeds/                                       ← catálogos iniciales
│   ├── migrations/                                  ← control cambios BD
│   └── backups/                                     ← respaldos controlados
│
├── shared/                                          ← recursos compartidos entre frontend y backend
│   ├── contracts/                                   ← contratos API y estructuras JSON
│   ├── constants/                                   ← constantes globales
│   └── enums/                                       ← enumeraciones compartidas
│
├── logs/                                            ← logs aplicación e integración
│   ├── frontend/                                    ← logs frontend
│   └── backend/                                     ← logs backend
│
├── scripts/                                         ← scripts operativos despliegue/mantenimiento
│   ├── deploy/                                      ← scripts despliegue
│   ├── backup/                                      ← scripts respaldo
│   └── maintenance/                                 ← scripts mantenimiento
│
├── config/                                          ← configuración global proyecto
│   ├── environments/                                ← variables por ambiente
│   ├── apache/                                      ← configuración Apache
│   └── security/                                    ← políticas y configuraciones seguridad
│
├── docs/                                            ← documentación general del sistema
│   ├── arquitectura/                                ← diagramas y arquitectura global
│   ├── frontend/                                    ← documentación frontend
│   ├── backend/                                     ← documentación backend
│   ├── database/                                    ← documentación base datos
│   └── despliegue/                                  ← documentación instalación
│
├── .gitignore                                       ← exclusiones control de versiones
├── README.md                                        ← descripción general del proyecto
└── LICENSE                                          ← licencia interna o institucional
```