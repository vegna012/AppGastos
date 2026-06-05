# Documento de Diseño  
## Sistema de Gestión de Gastos Empresariales

---

## 1. Visión general de la aplicación

El **Sistema de Gestión de Gastos Empresariales** será una aplicación web **on-premise**, instalada y operada dentro de la infraestructura tecnológica de una sola empresa. Su operación no dependerá de servicios externos ni de plataformas en la nube para ejecutar el flujo principal de registro, control, aprobación y consulta de gastos.

El alcance organizacional se limita a una empresa con áreas internas, centros de costos, usuarios capturistas, jefes de área, personal de Cuentas por pagar y administradores de la aplicación. El sistema debe permitir que cada gasto quede asociado a un área, centro de costos, usuario responsable, factura CFDI en formato XML y estado dentro del flujo de autorización.

El objetivo principal es garantizar trazabilidad y control financiero sobre el ciclo completo del gasto:

- Captura.
- Carga de XML.
- Extracción de datos clave.
- Envío a aprobación.
- Autorización o rechazo.
- Corrección cuando aplique.
- Consulta histórica.
- Generación de reportes operativos.

Esta lógica se basa en la necesidad de relacionar gastos con áreas, centros de costos, presupuestos y flujos de autorización documentados.

La información de gastos, XML asociados, bitácoras de operación y evidencia de aprobación deberá conservarse por un periodo mínimo de **5 años**, permitiendo su recuperación ante revisiones internas, auditorías o consultas operativas.

El diseño prioriza una solución simple, mantenible y controlada, evitando frameworks full-stack complejos. La aplicación deberá construirse con una arquitectura **PHP MVC clásica**, separación clara de responsabilidades, base de datos relacional y almacenamiento local de archivos XML.

---

## 2. Alcance funcional

### Incluido

- Hacer un registro manual de gastos.
- Carga de factura en formato XML.
- Extracción de datos clave del XML.
- Flujo de aprobación por área.
- Control de presupuestos.
- Datos de auditoría básica.
- Reportes operativos.

### Fuera de alcance

- Integración con ERPs.
- Validación de RFC.
- Validación de conceptos fiscales.
- Validación de factura no cancelada.
- Validación de comprobantes de pago.
- Consulta automática al SAT.
- Generación de pólizas contables.
- Pago automático a proveedores.
- Contabilidad electrónica.
- Timbrado, cancelación o emisión de CFDI.

---

## 3. Contexto organizacional

### 3.1 Estructura organizacional

La aplicación operará sobre una estructura organizacional interna compuesta por áreas y centros de costos.

- **Áreas:** representan unidades funcionales de la empresa, como Administración, Ventas, Operaciones, Tecnología, Recursos Humanos u otras definidas por la organización.
- **Centros de costos:** representan unidades de control presupuestal asociadas a un área específica.
- Cada gasto deberá pertenecer obligatoriamente a un área y a un centro de costos.
- Cada área tendrá un solo jefe de autorización responsable de aprobar o rechazar los gastos enviados por los usuarios capturistas de su área.
- Un usuario capturista solo podrá registrar gastos correspondientes a su propia área.
- Un jefe de área solo podrá visualizar y resolver gastos asignados a su área.
- Cuentas por pagar podrá consultar gastos aprobados para revisión operativa y generación de reportes.
- El administrador de la aplicación podrá configurar áreas, centros de costos, usuarios, roles y presupuestos.

La relación entre gastos, áreas y centros de costos es la base para el control por responsabilidad, permitiendo identificar qué unidad organizacional origina cada gasto y contra qué presupuesto debe compararse.

### 3.2 Control presupuestal

El sistema manejará un presupuesto mensual por área.

- Cada área tendrá un presupuesto definido por mes.
- Los gastos registrados y aprobados deberán acumularse contra el presupuesto mensual correspondiente.
- El sistema deberá mostrar comparativos entre presupuesto asignado y gasto real.
- Los reportes operativos deberán permitir visualizar:
  - Presupuesto mensual por área.
  - Gasto real acumulado.
  - Diferencia entre presupuestado y real.
  - Porcentaje de consumo presupuestal.
- El control presupuestal será informativo y operativo dentro del flujo de gasto.
- La comparación presupuestado vs real servirá como referencia para autorización y seguimiento financiero.

---

## 4. Roles del sistema

### Usuario capturista

**Qué puede hacer:**

- Registrar gastos manualmente.
- Cargar factura CFDI en formato XML.
- Consultar los gastos que haya registrado.
- Editar gastos en estado borrador.
- Corregir gastos rechazados.
- Enviar gastos a aprobación.

**Qué puede visualizar:**

- Sus propios gastos.
- Estado de sus gastos.
- Datos extraídos del XML cargado.
- Comentarios de rechazo o validación.

**Qué administra:**

- No administra catálogos ni configuración del sistema.
- Solo gestiona la captura y corrección de sus propios gastos.

### Jefe de área

**Qué puede hacer:**

- Consultar gastos pendientes de aprobación de su área.
- Aprobar gastos.
- Rechazar gastos.
- Agregar observaciones de aprobación o rechazo.
- Revisar presupuesto mensual del área.
- Consultar comparativos presupuestado vs real de su área.

**Qué puede visualizar:**

- Gastos registrados por usuarios de su área.
- XML asociado al gasto.
- Datos clave extraídos del XML.
- Historial de aprobación de gastos de su área.
- Reportes operativos de su área.

**Qué administra:**

- No administra usuarios, roles ni catálogos globales.
- Solo gestiona decisiones de aprobación sobre gastos de su área.

### Cuentas por pagar

**Qué puede hacer:**

- Consultar gastos aprobados.
- Validar operativamente la información registrada.
- Consultar XML asociado a cada gasto.
- Generar reportes operativos.
- Consultar historial de gastos aprobados, rechazados y pendientes.

**Qué puede visualizar:**

- Gastos de todas las áreas.
- Estados de los gastos.
- Datos de auditoría básica.
- Reportes por área, centro de costos, fecha, proveedor, monto y estado.

**Qué administra:**

- No administra configuración general.
- No aprueba gastos por área.
- Administra únicamente la revisión operativa desde la perspectiva de cuentas por pagar.

### Administrador de toda la aplicación

**Qué puede hacer:**

- Crear, modificar y desactivar usuarios.
- Asignar roles o perfiles.
- Configurar áreas.
- Configurar centros de costos.
- Asignar usuarios a áreas.
- Definir jefe de autorización por área.
- Registrar y modificar presupuestos mensuales por área.
- Administrar catálogos requeridos por la aplicación.
- Consultar la bitácora general.
- Generar reportes globales.

**Qué puede visualizar:**

- Información completa de la aplicación.
- Gastos de todas las áreas.
- Presupuestos globales.
- Historial de operaciones.
- Datos de auditoría básica.

**Qué administra:**

- Usuarios.
- Roles o perfiles.
- Áreas.
- Centros de costos.
- Presupuestos.
- Catálogos.
- Parámetros operativos del sistema.

---

## 5. Arquitectura general

### 5.1 Enfoque arquitectónico

La aplicación utilizará un patrón **MVC clásico en PHP**, separando la presentación, la lógica de negocio y el acceso a datos.

- **Modelo:** representa entidades, reglas de persistencia y repositorios.
- **Vista:** contiene las interfaces HTML renderizadas para usuarios finales.
- **Controlador:** recibe solicitudes, valida permisos, coordina servicios y retorna vistas o respuestas.
- **Servicios:** concentran reglas de negocio como procesamiento de XML, flujo de aprobación, control presupuestal y registro de auditoría.
- **Repositorios:** encapsulan consultas y operaciones contra la base de datos.

No se utilizarán frameworks full-stack. El diseño debe mantenerse simple, explícito y controlado, con dependencias mínimas y estructura de carpetas clara.

### 5.2 Componentes principales

#### Web frontend

- HTML5 / CSS3.
- Bootstrap.
- JavaScript / jQuery.
- Formularios para captura de gastos.
- Pantallas de consulta, aprobación, historial y reportes.
- Validaciones de interfaz como apoyo, sin sustituir validación backend.

#### API backend

- PHP.
- Controladores.
- Servicios.
- Modelos / Repositorios.
- Procesamiento de solicitudes HTTP.
- Validación de permisos por rol.
- Gestión de estados del gasto.
- Extracción de datos clave desde XML.
- Registro de bitácora.

#### Persistencia

- MySQL / MariaDB.
- Tablas relacionales para usuarios, roles, áreas, centros de costos, presupuestos, gastos, CFDI, catálogos y bitácora.
- Almacenamiento local de XML.
- Referencia del archivo XML desde la base de datos.
- Conservación mínima de 5 años.

#### Infraestructura

- Servidor on-premise.
- Servidor web Apache o equivalente compatible con PHP.
- Base de datos MySQL / MariaDB instalada en infraestructura interna.
- Directorio local protegido para almacenamiento de XML.
- Políticas de respaldo y mantenimiento.
- Control de permisos sobre carpetas de aplicación y archivos cargados.

---

## 6. Módulos funcionales

### Autenticación

- Inicio de sesión por usuario y contraseña.
- Validación de credenciales contra base de datos.
- Manejo de sesiones.
- Cierre de sesión.
- Control de acceso según rol.
- Protección de rutas privadas.

### Administración de la aplicación

- Alta, edición y desactivación de usuarios.
- Asignación de roles o perfiles.
- Configuración de áreas.
- Configuración de centros de costos.
- Asignación de usuarios a áreas.
- Definición del jefe de autorización por área.
- Configuración de presupuestos mensuales por área.
- Administración de catálogos básicos.

### Captura de gastos

- Registro manual de gasto.
- Asociación del gasto con área y centro de costos.
- Captura de monto, fecha, proveedor, concepto y observaciones.
- Carga de archivo XML.
- Extracción de datos clave del XML.
- Guardado en estado borrador.
- Envío a aprobación.

### Aprobación

- Bandeja de gastos pendientes para jefe de área.
- Consulta de detalle del gasto.
- Visualización de datos extraídos del XML.
- Consulta de presupuesto del área.
- Aprobación del gasto.
- Rechazo del gasto.
- Registro obligatorio de observaciones en rechazo.
- Cambio controlado de estado.

### Reporteo

- Reporte de gastos por área.
- Reporte de gastos por centro de costos.
- Reporte por rango de fechas.
- Reporte por estado.
- Comparativo presupuestado vs real.
- Consulta de gasto acumulado mensual.
- Exportación operativa según necesidades internas permitidas por la aplicación.

### Auditoría

- Registro de creación de gasto.
- Registro de carga de XML.
- Registro de envío a aprobación.
- Registro de aprobación.
- Registro de rechazo.
- Registro de corrección.
- Registro de usuario, fecha, hora y acción.
- Bitácora no editable desde la interfaz funcional.

### Historial

- Consulta del ciclo de vida del gasto.
- Visualización de cambios de estado.
- Consulta de observaciones.
- Consulta de responsables por etapa.
- Recuperación de gastos históricos.
- Conservación mínima de 5 años.

### Notificaciones

- Aviso interno de gasto enviado a aprobación.
- Aviso interno de gasto aprobado.
- Aviso interno de gasto rechazado.
- Aviso interno de gasto corregido.
- Notificaciones visibles dentro del sistema.
- Sin dependencia obligatoria de servicios externos.

---

## 7. Flujo completo del gasto

### Captura de un gasto

1. El usuario capturista inicia un nuevo registro.
2. Captura datos generales del gasto.
3. Selecciona centro de costos permitido para su área.
4. El gasto se guarda inicialmente como borrador.

### Cargar XML de CFDI

1. El usuario adjunta la factura en formato XML.
2. El sistema almacena el archivo XML en repositorio local.
3. Se registra la relación entre el gasto y el archivo cargado.

### Procesar XML

1. El sistema lee el XML.
2. Extrae datos clave requeridos para el registro operativo.
3. Los datos extraídos se asocian al gasto.
4. El procesamiento de XML se limita a lectura y extracción de información, sin validar RFC, estatus SAT, conceptos o comprobantes de pago.

### Mandar aprobación

1. El usuario revisa la información capturada.
2. Envía el gasto al flujo de aprobación.
3. El sistema cambia el estado a pendiente de aprobación.
4. El gasto queda disponible para el jefe de autorización del área.

### Consultar aprobaciones de gasto

1. El jefe de área consulta su bandeja de pendientes.
2. Revisa detalle del gasto.
3. Consulta XML, datos extraídos y presupuesto disponible del área.
4. Verifica comparativo presupuestado vs real antes de decidir.

### Aprobación o rechazo

1. El jefe de área aprueba el gasto cuando procede.
2. El sistema cambia el estado a aprobación.
3. Si el gasto no procede, el jefe de área lo rechaza.
4. El sistema cambia el estado a rechazo y registra observaciones.

### Corregir o validar

1. Si el gasto fue rechazado, el capturista puede corregirlo.
2. El gasto corregido puede enviarse nuevamente a aprobación.
3. Cuentas por pagar puede consultar gastos aprobados para revisión operativa.
4. La bitácora conserva todos los movimientos.

### Generar un reporte

1. Cuentas por pagar, jefe de área o administrador genera reportes según permisos.
2. Los reportes pueden filtrarse por área, centro de costos, periodo, estado o presupuesto.
3. El reporte permite comparar gasto real contra presupuesto mensual.

### Estados del gasto

- **Borrador:** registro creado pero no enviado a aprobación.
- **Pendiente de aprobación:** gasto enviado al jefe de área.
- **Aprobación:** gasto autorizado por el jefe de área.
- **Rechazo:** gasto no autorizado, con observaciones para corrección o cierre.

---

## 8. Modelo de datos conceptual

### Entidades principales

- Usuario.
- Rol o perfil.
- Áreas.
- Centro de costos.
- Presupuesto.
- Gasto.
- Factura CFDI.
- Catálogos.

### Relaciones clave

- Un usuario pertenece a un área.
- Un usuario tiene un rol o perfil.
- Un área puede tener muchos usuarios.
- Un área tiene un solo jefe de autorización.
- Un área puede tener varios centros de costos.
- Un centro de costos pertenece a una sola área.
- Un presupuesto pertenece a un área y a un periodo mensual.
- Un gasto pertenece a un usuario capturista.
- Un gasto pertenece a un área.
- Un gasto pertenece a un centro de costos.
- Un gasto puede tener una factura CFDI asociada.
- Una factura CFDI pertenece a un gasto.
- Un gasto tiene un estado dentro del flujo.
- Un gasto genera registros de auditoría.
- Los catálogos alimentan listas controladas utilizadas en captura, clasificación y reporteo.

---

## 9. Consideraciones técnicas y de seguridad

- Implementar RBAC para controlar acceso por rol.
- Validación backend obligatoria en todos los formularios y acciones críticas.
- Uso de hash seguro de contraseñas.
- Protección CSRF en formularios y acciones POST.
- Protección XSS mediante escape de salida y sanitización de entradas.
- Prevención SQL Injection mediante consultas preparadas.
- Manejo seguro de XML:
  - Validar extensión y tipo de archivo.
  - Limitar tamaño de archivo.
  - Deshabilitar carga de entidades externas.
  - Evitar procesamiento de XML no confiable con configuraciones inseguras.
- Almacenar XML fuera de rutas públicas directas.
- Bitácora inmutable para eventos críticos.
- Retención mínima de 5 años para gastos, XML y registros de auditoría.
- Respaldos periódicos de base de datos.
- Respaldos periódicos del repositorio local de XML.
- Control de permisos de escritura en carpetas del servidor.
- Restricción de acceso directo a archivos sensibles de configuración.
- Registro de usuario, fecha, hora y acción en operaciones relevantes.

---

## 10. Lineamientos de calidad y mantenibilidad

- Separación por capas: vistas, controladores, servicios, modelos y repositorios.
- Servicios de negocio centralizados para aprobación, presupuesto, XML, auditoría e historial.
- Uso mínimo de librerías externas.
- Convenciones claras de nombres para archivos, clases, tablas, columnas y rutas.
- Validaciones concentradas en backend.
- Reglas de negocio documentadas dentro de servicios específicos.
- Consultas a base de datos encapsuladas en repositorios.
- Interfaces simples y consistentes para usuarios operativos.
- Estructura preparada para crecimiento controlado.
- Evitar acoplamiento entre lógica de presentación y lógica de negocio.
- Mantener configuración separada del código funcional.
- Diseñar módulos independientes para facilitar mantenimiento correctivo y evolutivo.

---

## 11. Conclusión

El **Sistema de Gestión de Gastos Empresariales** queda diseñado como una solución web on-premise, sólida y mantenible, orientada al control financiero interno de una sola empresa. Su arquitectura MVC clásica en PHP, junto con una base de datos MySQL / MariaDB y almacenamiento local de XML, permite mantener independencia tecnológica y control directo sobre la operación.

El flujo definido cubre el ciclo completo del gasto, desde la captura hasta el reporte, incorporando aprobación por área, control presupuestal, trazabilidad, historial y auditoría básica. La estructura de roles, áreas y centros de costos permite delimitar responsabilidades y reducir riesgos operativos.

La solución prioriza mantenibilidad, seguridad y claridad técnica, evitando dependencias innecesarias e integraciones fuera de alcance. Con este diseño, la empresa contará con una plataforma capaz de centralizar la gestión de gastos, conservar evidencia por al menos 5 años y fortalecer el control interno sin comprometer simplicidad arquitectónica.
