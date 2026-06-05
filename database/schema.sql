-- =============================================================================
-- Sistema de Gestión de Gastos Empresariales
-- Script maestro de esquema de base de datos
-- Motor: MySQL 8+ / MariaDB 10.5+
-- Charset: utf8mb4 | Engine: InnoDB
-- =============================================================================
-- Ejecución: mysql -u root -p < database/schema.sql
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Base de datos
-- -----------------------------------------------------------------------------
DROP DATABASE IF EXISTS gastos_empresariales;

CREATE DATABASE gastos_empresariales
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gastos_empresariales;

-- =============================================================================
-- FASE 1: Tablas sin dependencias circulares
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Tabla: roles
-- Perfiles de acceso del sistema (Administrador, Capturista, Jefe de área, etc.)
-- -----------------------------------------------------------------------------
CREATE TABLE roles (
    id_rol          INT             NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(50)     NOT NULL,
    descripcion     VARCHAR(255)    NULL,
    activo          BOOLEAN         NOT NULL DEFAULT TRUE,
    creado_en       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en  DATETIME        NULL ON UPDATE CURRENT_TIMESTAMP,
    eliminado_en    DATETIME        NULL COMMENT 'Soft delete: NULL = registro activo',

    PRIMARY KEY (id_rol),
    UNIQUE KEY uk_roles_nombre (nombre),
    KEY idx_roles_activo (activo)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de perfiles de acceso (RBAC)';

-- -----------------------------------------------------------------------------
-- Tabla: areas
-- Unidades funcionales de la empresa. FK a usuarios se agrega en FASE 3
-- (dependencia circular areas <-> usuarios).
-- -----------------------------------------------------------------------------
CREATE TABLE areas (
    id_area         INT             NOT NULL AUTO_INCREMENT,
    id_jefe_area    INT             NULL COMMENT 'Usuario jefe de autorización; FK en FASE 3',
    nombre          VARCHAR(100)    NOT NULL,
    descripcion     VARCHAR(255)    NULL,
    activo          BOOLEAN         NOT NULL DEFAULT TRUE,
    creado_en       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creado_por      INT             NULL COMMENT 'FK a usuarios; se agrega en FASE 3',
    actualizado_en  DATETIME        NULL ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por INT             NULL COMMENT 'FK a usuarios; se agrega en FASE 3',
    eliminado_en    DATETIME        NULL COMMENT 'Soft delete: NULL = registro activo',

    PRIMARY KEY (id_area),
    UNIQUE KEY uk_areas_nombre (nombre),
    KEY idx_areas_jefe (id_jefe_area),
    KEY idx_areas_activo (activo),
    KEY idx_areas_creado_por (creado_por),
    KEY idx_areas_actualizado_por (actualizado_por)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Áreas internas de la organización';

-- -----------------------------------------------------------------------------
-- Tabla: usuarios
-- Usuarios internos del sistema. FKs de auditoría a sí misma se agregan en FASE 3.
-- -----------------------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario      INT             NOT NULL AUTO_INCREMENT,
    id_rol          INT             NOT NULL,
    id_area         INT             NOT NULL,
    nombre          VARCHAR(100)    NOT NULL,
    correo          VARCHAR(120)    NOT NULL,
    password_hash   VARCHAR(255)    NOT NULL COMMENT 'Hash seguro; nunca texto plano',
    activo          BOOLEAN         NOT NULL DEFAULT TRUE,
    ultimo_acceso   DATETIME        NULL,
    creado_en       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creado_por      INT             NULL COMMENT 'FK a usuarios; se agrega en FASE 3',
    actualizado_en  DATETIME        NULL ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por INT             NULL COMMENT 'FK a usuarios; se agrega en FASE 3',
    eliminado_en    DATETIME        NULL COMMENT 'Soft delete: NULL = registro activo',

    PRIMARY KEY (id_usuario),
    UNIQUE KEY uk_usuarios_correo (correo),
    KEY idx_usuarios_area (id_area),
    KEY idx_usuarios_rol (id_rol),
    KEY idx_usuarios_activo (activo),
    KEY idx_usuarios_creado_por (creado_por),
    KEY idx_usuarios_actualizado_por (actualizado_por),

    CONSTRAINT fk_usuarios_rol
        FOREIGN KEY (id_rol) REFERENCES roles (id_rol)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_usuarios_area
        FOREIGN KEY (id_area) REFERENCES areas (id_area)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Usuarios internos con acceso al sistema';

-- -----------------------------------------------------------------------------
-- Tabla: estatus_gasto
-- Catálogo de estados del flujo de gasto (Borrador, Pendiente, Aprobado, etc.)
-- -----------------------------------------------------------------------------
CREATE TABLE estatus_gasto (
    id_estatus_gasto INT            NOT NULL AUTO_INCREMENT,
    clave            VARCHAR(30)    NOT NULL,
    nombre           VARCHAR(80)    NOT NULL,
    descripcion      VARCHAR(255)   NULL,
    orden_flujo      TINYINT        NOT NULL,
    activo           BOOLEAN        NOT NULL DEFAULT TRUE,

    PRIMARY KEY (id_estatus_gasto),
    UNIQUE KEY uk_estatus_gasto_clave (clave),
    KEY idx_estatus_activo (activo)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Estados del ciclo de vida del gasto';

-- -----------------------------------------------------------------------------
-- Tabla: conceptos_deducibilidad
-- Clasificación fiscal básica del detalle (Deducible, No deducible, etc.)
-- -----------------------------------------------------------------------------
CREATE TABLE conceptos_deducibilidad (
    id_concepto_deducibilidad INT          NOT NULL AUTO_INCREMENT,
    clave                     VARCHAR(30)  NOT NULL,
    nombre                    VARCHAR(80)  NOT NULL,
    descripcion               VARCHAR(255) NULL,
    activo                    BOOLEAN      NOT NULL DEFAULT TRUE,

    PRIMARY KEY (id_concepto_deducibilidad),
    UNIQUE KEY uk_conceptos_deducibilidad_clave (clave),
    KEY idx_deducibilidad_activo (activo)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de clasificación fiscal de detalles';

-- =============================================================================
-- FASE 2: Resolución de dependencia circular areas <-> usuarios
-- =============================================================================

ALTER TABLE areas
    ADD CONSTRAINT fk_areas_jefe_area
        FOREIGN KEY (id_jefe_area) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,

    ADD CONSTRAINT fk_areas_creado_por
        FOREIGN KEY (creado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,

    ADD CONSTRAINT fk_areas_actualizado_por
        FOREIGN KEY (actualizado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE usuarios
    ADD CONSTRAINT fk_usuarios_creado_por
        FOREIGN KEY (creado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,

    ADD CONSTRAINT fk_usuarios_actualizado_por
        FOREIGN KEY (actualizado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL;

-- =============================================================================
-- FASE 3: Tablas dependientes de areas y usuarios
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Tabla: centros_costos
-- Unidades de control presupuestal asociadas a un área
-- -----------------------------------------------------------------------------
CREATE TABLE centros_costos (
    id_centro_costo INT             NOT NULL AUTO_INCREMENT,
    id_area         INT             NOT NULL,
    codigo          VARCHAR(30)     NOT NULL,
    nombre          VARCHAR(100)    NOT NULL,
    descripcion     VARCHAR(255)    NULL,
    activo          BOOLEAN         NOT NULL DEFAULT TRUE,
    creado_en       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creado_por      INT             NULL,
    actualizado_en  DATETIME        NULL ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por INT             NULL,
    eliminado_en    DATETIME        NULL COMMENT 'Soft delete: NULL = registro activo',

    PRIMARY KEY (id_centro_costo),
    UNIQUE KEY uk_centros_costos_area_codigo (id_area, codigo),
    KEY idx_centros_area (id_area),
    KEY idx_centros_activo (activo),
    KEY idx_centros_creado_por (creado_por),
    KEY idx_centros_actualizado_por (actualizado_por),

    CONSTRAINT fk_centros_costos_area
        FOREIGN KEY (id_area) REFERENCES areas (id_area)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_centros_costos_creado_por
        FOREIGN KEY (creado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,

    CONSTRAINT fk_centros_costos_actualizado_por
        FOREIGN KEY (actualizado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Centros de costo vinculados a áreas';

-- -----------------------------------------------------------------------------
-- Tabla: presupuestos
-- Presupuesto mensual asignado por área (sin soft delete según diccionario)
-- -----------------------------------------------------------------------------
CREATE TABLE presupuestos (
    id_presupuesto      INT             NOT NULL AUTO_INCREMENT,
    id_area             INT             NOT NULL,
    anio                YEAR            NOT NULL,
    mes                 TINYINT         NOT NULL COMMENT 'Mes calendario: 1-12',
    monto_presupuestado DECIMAL(12, 2)  NOT NULL,
    observaciones       TEXT            NULL,
    activo              BOOLEAN         NOT NULL DEFAULT TRUE,
    creado_en           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creado_por          INT             NULL,
    actualizado_en      DATETIME        NULL ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por     INT             NULL,

    PRIMARY KEY (id_presupuesto),
    -- UNIQUE cubre también la consulta idx_presupuesto_area_periodo del diccionario
    UNIQUE KEY uk_presupuestos_area_anio_mes (id_area, anio, mes),
    KEY idx_presupuestos_creado_por (creado_por),
    KEY idx_presupuestos_actualizado_por (actualizado_por),

    CONSTRAINT fk_presupuestos_area
        FOREIGN KEY (id_area) REFERENCES areas (id_area)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_presupuestos_creado_por
        FOREIGN KEY (creado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,

    CONSTRAINT fk_presupuestos_actualizado_por
        FOREIGN KEY (actualizado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,

    CONSTRAINT chk_presupuestos_mes
        CHECK (mes BETWEEN 1 AND 12)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Presupuesto mensual por área';

-- -----------------------------------------------------------------------------
-- Tabla: categorias_gasto
-- Clasificación operativa del gasto (viáticos, transporte, etc.)
-- -----------------------------------------------------------------------------
CREATE TABLE categorias_gasto (
    id_categoria_gasto INT            NOT NULL AUTO_INCREMENT,
    nombre             VARCHAR(100)   NOT NULL,
    descripcion        VARCHAR(255)   NULL,
    activo             BOOLEAN        NOT NULL DEFAULT TRUE,
    creado_en          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creado_por         INT            NULL,
    actualizado_en     DATETIME       NULL ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por    INT            NULL,
    eliminado_en       DATETIME       NULL COMMENT 'Soft delete: NULL = registro activo',

    PRIMARY KEY (id_categoria_gasto),
    UNIQUE KEY uk_categorias_gasto_nombre (nombre),
    KEY idx_categoria_activo (activo),
    KEY idx_categorias_gasto_creado_por (creado_por),
    KEY idx_categorias_gasto_actualizado_por (actualizado_por),

    CONSTRAINT fk_categorias_gasto_creado_por
        FOREIGN KEY (creado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,

    CONSTRAINT fk_categorias_gasto_actualizado_por
        FOREIGN KEY (actualizado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de categorías operativas de gasto';

-- -----------------------------------------------------------------------------
-- Tabla: proveedores
-- Proveedores asociados a CFDI o gastos (RFC almacenado cifrado en aplicación)
-- -----------------------------------------------------------------------------
CREATE TABLE proveedores (
    id_proveedor    INT             NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(150)    NOT NULL,
    rfc             VARCHAR(255)    NOT NULL COMMENT 'RFC cifrado por la capa de aplicación',
    correo          VARCHAR(120)    NULL,
    telefono        VARCHAR(30)     NULL,
    activo          BOOLEAN         NOT NULL DEFAULT TRUE,
    creado_en       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creado_por      INT             NULL,
    actualizado_en  DATETIME        NULL ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por INT             NULL,
    eliminado_en    DATETIME        NULL COMMENT 'Soft delete: NULL = registro activo',

    PRIMARY KEY (id_proveedor),
    KEY idx_proveedor_rfc (rfc),
    KEY idx_proveedor_nombre (nombre),
    KEY idx_proveedor_activo (activo),
    KEY idx_proveedores_creado_por (creado_por),
    KEY idx_proveedores_actualizado_por (actualizado_por),

    CONSTRAINT fk_proveedores_creado_por
        FOREIGN KEY (creado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,

    CONSTRAINT fk_proveedores_actualizado_por
        FOREIGN KEY (actualizado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de proveedores';

-- =============================================================================
-- FASE 4: Tablas operativas de gastos
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Tabla: gastos_cabecera
-- Comprobación general del gasto (entidad principal del flujo)
-- -----------------------------------------------------------------------------
CREATE TABLE gastos_cabecera (
    id_gasto_cabecera       INT             NOT NULL AUTO_INCREMENT,
    folio                   VARCHAR(30)     NOT NULL,
    id_usuario              INT             NOT NULL COMMENT 'Usuario capturista',
    id_area                 INT             NOT NULL,
    id_centro_costo         INT             NOT NULL,
    id_estatus_gasto        INT             NOT NULL,
    id_proveedor            INT             NULL COMMENT 'Proveedor principal; opcional',
    fecha_gasto             DATE            NOT NULL,
    concepto_general        VARCHAR(255)    NOT NULL,
    subtotal                DECIMAL(12, 2)  NOT NULL DEFAULT 0.00 COMMENT 'Calculado desde detalles',
    iva                     DECIMAL(12, 2)  NOT NULL DEFAULT 0.00 COMMENT 'Calculado desde detalles',
    total                   DECIMAL(12, 2)  NOT NULL DEFAULT 0.00 COMMENT 'Calculado desde detalles',
    total_deducible         DECIMAL(12, 2)  NOT NULL DEFAULT 0.00 COMMENT 'Calculado desde detalles',
    total_no_deducible      DECIMAL(12, 2)  NOT NULL DEFAULT 0.00 COMMENT 'Calculado desde detalles',
    observaciones           TEXT            NULL,
    observaciones_aprobacion TEXT           NULL,
    fecha_envio_aprobacion  DATETIME        NULL,
    fecha_aprobacion        DATETIME        NULL,
    aprobado_por            INT             NULL COMMENT 'Usuario que aprobó o rechazó',
    creado_en               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creado_por              INT             NULL,
    actualizado_en          DATETIME        NULL ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por         INT             NULL,
    eliminado_en            DATETIME        NULL COMMENT 'Soft delete: NULL = registro activo',

    PRIMARY KEY (id_gasto_cabecera),
    UNIQUE KEY uk_gastos_cabecera_folio (folio),
    KEY idx_gasto_area_fecha (id_area, fecha_gasto),
    KEY idx_gasto_estatus (id_estatus_gasto),
    KEY idx_gasto_usuario (id_usuario),
    KEY idx_gasto_centro_costo (id_centro_costo),
    KEY idx_gasto_periodo (fecha_gasto),
    KEY idx_gastos_cabecera_proveedor (id_proveedor),
    KEY idx_gastos_cabecera_aprobado_por (aprobado_por),
    KEY idx_gastos_cabecera_creado_por (creado_por),
    KEY idx_gastos_cabecera_actualizado_por (actualizado_por),

    CONSTRAINT fk_gastos_cabecera_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_gastos_cabecera_area
        FOREIGN KEY (id_area) REFERENCES areas (id_area)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_gastos_cabecera_centro_costo
        FOREIGN KEY (id_centro_costo) REFERENCES centros_costos (id_centro_costo)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_gastos_cabecera_estatus
        FOREIGN KEY (id_estatus_gasto) REFERENCES estatus_gasto (id_estatus_gasto)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_gastos_cabecera_proveedor
        FOREIGN KEY (id_proveedor) REFERENCES proveedores (id_proveedor)
        ON UPDATE CASCADE ON DELETE SET NULL,

    CONSTRAINT fk_gastos_cabecera_aprobado_por
        FOREIGN KEY (aprobado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,

    CONSTRAINT fk_gastos_cabecera_creado_por
        FOREIGN KEY (creado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,

    CONSTRAINT fk_gastos_cabecera_actualizado_por
        FOREIGN KEY (actualizado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Cabecera de comprobación de gasto';

-- -----------------------------------------------------------------------------
-- Tabla: gastos_detalle
-- Partidas o conceptos específicos de una comprobación
-- -----------------------------------------------------------------------------
CREATE TABLE gastos_detalle (
    id_gasto_detalle          INT             NOT NULL AUTO_INCREMENT,
    id_gasto_cabecera         INT             NOT NULL,
    id_categoria_gasto        INT             NOT NULL,
    id_concepto_deducibilidad INT             NOT NULL,
    descripcion               VARCHAR(255)    NOT NULL,
    cantidad                  DECIMAL(10, 2)  NOT NULL,
    precio_unitario           DECIMAL(12, 2)  NOT NULL,
    subtotal                  DECIMAL(12, 2)  NOT NULL DEFAULT 0.00,
    iva                       DECIMAL(12, 2)  NULL,
    total                     DECIMAL(12, 2)  NOT NULL,
    importe_deducible         DECIMAL(12, 2)  NOT NULL,
    importe_no_deducible      DECIMAL(12, 2)  NOT NULL,
    observaciones             TEXT            NULL,
    creado_en                 DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en            DATETIME        NULL ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id_gasto_detalle),
    KEY idx_detalle_cabecera (id_gasto_cabecera),
    KEY idx_detalle_categoria (id_categoria_gasto),
    KEY idx_detalle_deducibilidad (id_concepto_deducibilidad),

    CONSTRAINT fk_gastos_detalle_cabecera
        FOREIGN KEY (id_gasto_cabecera) REFERENCES gastos_cabecera (id_gasto_cabecera)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_gastos_detalle_categoria
        FOREIGN KEY (id_categoria_gasto) REFERENCES categorias_gasto (id_categoria_gasto)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_gastos_detalle_deducibilidad
        FOREIGN KEY (id_concepto_deducibilidad) REFERENCES conceptos_deducibilidad (id_concepto_deducibilidad)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Líneas de detalle de un gasto';

-- -----------------------------------------------------------------------------
-- Tabla: facturas_cfdi
-- CFDI/XML asociados a la cabecera de gasto
-- -----------------------------------------------------------------------------
CREATE TABLE facturas_cfdi (
    id_factura_cfdi  INT             NOT NULL AUTO_INCREMENT,
    id_gasto_cabecera INT            NOT NULL,
    id_proveedor     INT             NULL,
    uuid             CHAR(36)        NULL COMMENT 'UUID fiscal; único cuando existe',
    serie            VARCHAR(30)     NULL,
    folio            VARCHAR(30)     NULL,
    fecha_emision    DATETIME        NULL,
    rfc_emisor       VARCHAR(255)    NULL COMMENT 'RFC cifrado por la capa de aplicación',
    nombre_emisor    VARCHAR(150)    NULL,
    rfc_receptor     VARCHAR(255)    NULL COMMENT 'RFC cifrado por la capa de aplicación',
    nombre_receptor  VARCHAR(150)    NULL,
    subtotal         DECIMAL(12, 2)  NULL,
    iva              DECIMAL(12, 2)  NULL,
    total            DECIMAL(12, 2)  NULL,
    moneda           VARCHAR(10)     NULL,
    ruta_xml         VARCHAR(255)    NULL COMMENT 'Ruta interna; fuera de carpeta pública',
    nombre_archivo   VARCHAR(150)    NOT NULL,
    hash_archivo     VARCHAR(255)    NULL COMMENT 'Huella del archivo para integridad',
    procesado        BOOLEAN         NOT NULL DEFAULT FALSE,
    creado_en        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creado_por       INT             NULL,
    eliminado_en     DATETIME        NULL COMMENT 'Soft delete: NULL = registro activo',

    PRIMARY KEY (id_factura_cfdi),
    -- UNIQUE en uuid cumple también idx_cfdi_uuid del diccionario
    UNIQUE KEY uk_facturas_cfdi_uuid (uuid),
    KEY idx_cfdi_gasto (id_gasto_cabecera),
    KEY idx_cfdi_proveedor (id_proveedor),
    KEY idx_cfdi_fecha (fecha_emision),
    KEY idx_facturas_cfdi_creado_por (creado_por),

    CONSTRAINT fk_facturas_cfdi_gasto
        FOREIGN KEY (id_gasto_cabecera) REFERENCES gastos_cabecera (id_gasto_cabecera)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_facturas_cfdi_proveedor
        FOREIGN KEY (id_proveedor) REFERENCES proveedores (id_proveedor)
        ON UPDATE CASCADE ON DELETE SET NULL,

    CONSTRAINT fk_facturas_cfdi_creado_por
        FOREIGN KEY (creado_por) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Facturas CFDI/XML vinculadas a gastos';

-- -----------------------------------------------------------------------------
-- Tabla: bitacora_movimientos
-- Historial inmutable de acciones críticas (solo inserción desde aplicación)
-- -----------------------------------------------------------------------------
CREATE TABLE bitacora_movimientos (
    id_bitacora        INT             NOT NULL AUTO_INCREMENT,
    id_usuario         INT             NOT NULL,
    id_gasto_cabecera  INT             NULL COMMENT 'Gasto relacionado; opcional',
    accion             VARCHAR(80)     NOT NULL,
    descripcion        TEXT            NOT NULL,
    estatus_anterior   VARCHAR(50)     NULL,
    estatus_nuevo      VARCHAR(50)     NULL,
    fecha_movimiento   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_origen          VARCHAR(45)     NULL,
    user_agent         VARCHAR(255)    NULL,

    PRIMARY KEY (id_bitacora),
    KEY idx_bitacora_usuario (id_usuario),
    KEY idx_bitacora_gasto (id_gasto_cabecera),
    KEY idx_bitacora_fecha (fecha_movimiento),
    KEY idx_bitacora_accion (accion),

    CONSTRAINT fk_bitacora_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_bitacora_gasto
        FOREIGN KEY (id_gasto_cabecera) REFERENCES gastos_cabecera (id_gasto_cabecera)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Bitácora de auditoría de movimientos críticos';

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Fin del esquema: 13 tablas creadas
-- =============================================================================
