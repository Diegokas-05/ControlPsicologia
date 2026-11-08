# Sistema ControlPsicologia — Avance 02 (Parcial II)

## 1. Identificación

- **Estudiante:** Diego Aaron
- **Asignatura:** Tecnologías Emergentes — Unidad II
- **Proyecto:** Sistema ControlPsicologia
- **Entrega:** Parcial II — Arquitectura de Confianza
- **Fecha de entrega:** 5 de octubre de 2026

---

## 2. Módulo implementado

- **Módulo:** Gestión de Citas (CRUD).
- **Propósito:** Administrar las solicitudes de atención psicológica de los pacientes.
- **Requerimiento planificado:** Permitir al personal administrativo registrar, consultar, modificar y eliminar citas, validando la integridad de la información y facilitando la búsqueda por categorías.

En este Avance 02 se conserva el CRUD original y se agrega la capa de seguridad exigida por el parcial:

- Autenticación de usuarios con `password_hash()`.
- Sesión segura con regeneración de ID, timeout de inactividad y timeout absoluto.
- Autorización por roles (`admin`, `consulta`) verificada en el servidor.
- Protección CSRF en todas las escrituras.
- **Control nuevo:** Control de Intentos de Ingreso con bloqueo temporal (Opción C).
- **Bitácora de auditoría** en MongoDB.

---

## 3. Base de datos y colecciones

- **Base de datos:** `control_psicologia`

### 3.1 Colección `usuarios`

| Campo | Tipo | Descripción |
|---|---|---|
| `_id` | ObjectId | Generado por MongoDB |
| `nombre` | string | Nombre completo del usuario |
| `correo` | string | Correo tal como lo escribió el usuario |
| `correo_normalizado` | string | Correo en minúsculas, índice único |
| `clave_hash` | string | Hash generado con `password_hash()` |
| `rol` | string | `admin` o `consulta` |
| `activo` | bool | Permite desactivar cuentas sin borrarlas |
| `intentos_fallidos` | int | Contador de intentos fallidos |
| `bloqueado_hasta` | UTCDateTime | Fecha límite del bloqueo temporal (opcional) |
| `creado_en` | UTCDateTime | Fecha de creación |
| `actualizado_en` | UTCDateTime | Fecha de última modificación |

### 3.2 Colección `citas`

| Campo | Tipo | Valores permitidos |
|---|---|---|
| `_id` | ObjectId | Generado por MongoDB |
| `motivo` | string | Obligatorio, máximo 100 caracteres |
| `tipo` | string | `Individual`, `Pareja`, `Infantil` |
| `estado` | string | `Pendiente`, `Completada`, `Cancelada` |
| `fecha_creacion` | UTCDateTime | Fecha de creación |

### 3.3 Colección `bitacora`

| Campo | Tipo | Descripción |
|---|---|---|
| `_id` | ObjectId | Generado por MongoDB |
| `evento` | string | Nombre del evento (ej. `login.fallido`) |
| `actor_id` | string | ObjectId del usuario (si aplica) |
| `actor_correo` | string | Correo del usuario |
| `actor_rol` | string | Rol del usuario |
| `resultado` | string | `permitido` o `denegado` |
| `ip` | string | Hash SHA-256 de la IP del cliente |
| `user_agent` | string | Navegador recortado a 120 caracteres |
| `creado_en` | UTCDateTime | Fecha del evento |
| `detalles` | object | Información adicional (intentos, minutos, etc.) |

---

## 4. Archivos principales

### Configuración
- `config/Database.php`: Conexión a MongoDB mediante el driver de Composer.

### Modelos (`app/Models/`)
- `UsuarioModel.php`: Búsqueda de usuarios, registro atómico de intentos fallidos con `$inc`, bloqueo temporal y reseteo.
- `CitaModel.php`: Operaciones CRUD sobre la colección `citas`.
- `BitacoraModel.php`: Registro de eventos de auditoría.

### Controladores (`app/Controllers/`)
- `AuthController.php`: Gestión de inicio de sesión, validación de credenciales, control de intentos y bitácora.
- `CitaController.php`: Listado, filtros y operaciones del CRUD.

### Seguridad (`app/Security/`)
- `Sesion.php`: Inicio de sesión segura (`cookie_httponly`, `samesite`), regeneración de ID, timeouts y control RBAC.
- `Csrf.php`: Generación y validación de tokens sincronizados para peticiones POST.

### Vistas (`app/Views/`)
- `auth/login.php`: Formulario de acceso con avisos de bloqueo.
- `citas/index.php`: Listado reactivo con HTMX.
- `citas/_tabla.php`: Fragmento parcial HTML de resultados.

### Rutas públicas (`public/`)
- `login.php`: Procesa GET (formulario) y POST (autenticación).
- `logout.php`: Destruye la sesión en servidor y cookie en navegador.
- `mvc.php`: Front Controller con soporte HTMX.
- `index.php`: Listado tradicional (CRUD original conservado).
- `crear.php`, `editar.php`: Formularios con tokens CSRF.
- `guardar.php`, `actualizar.php`, `eliminar.php`: Rutas POST protegidas por rol e integridad.

### Scripts (`scripts/`)
- `crear_usuarios_demo.php`: Inicializa las cuentas de prueba, índices únicos y contadores en MongoDB.
- `probar_validacion.php`: Prueba automatizada de validaciones de dominio.

---

## 5. Cuentas ficticias

| Nombre | Correo | Clave | Rol |
|---|---|---|---|
| Administrador Psicología | `admin@psicologia.test` | `Admin-2026!` | `admin` |
| Asistente Consulta | `consulta@psicologia.test` | `Consulta-2026!` | `consulta` |

---

## 6. Matriz de permisos (RBAC)

| Acción | admin | consulta |
|---|---|---|
| Listar / buscar citas (`mvc.php` / `index.php`) | Permitir | Permitir |
| Crear cita (`crear.php` / `guardar.php`) | Permitir | Denegar (403) |
| Editar cita (`editar.php` / `actualizar.php`) | Permitir | Denegar (403) |
| Eliminar cita (`eliminar.php`) | Permitir | Denegar (403) |
| Ver bitácora | Permitir | Denegar (403) |

**Política Deny by Default:** Toda acción no explícitamente permitida para el rol autenticado es rechazada con código HTTP 403 en el servidor.

---

## 7. Instrucciones de ejecución

Desde la terminal en la raíz del proyecto (`ControlPsicologia`):

1. **Instalar dependencias de Composer:**
   ```bash
   composer install
   ```

2. **Sembrar las cuentas de demostración e índices:**
   ```bash
   php scripts/crear_usuarios_demo.php
   ```

3. **Iniciar el servidor local de desarrollo:**
   ```bash
   php -S localhost:8000 -t public
   ```

4. **Acceso al sistema:**
   Abrir en el navegador web `http://localhost:8000/login.php` e ingresar con las credenciales correspondientes.

---

## 8. Matriz de Pruebas y Evidencias (T01 - T10)

| ID | Acción Realizada | Resultado Esperado | Resultado Observado | Estado |
|---|---|---|---|---|
| **T01** | Acceder a `/mvc.php` sin sesión activa. | Redirección inmediata a `login.php` (401/Unauthorized). | El guardián de sesión rechaza la petición y redirige al login. | Aprobada |
| **T02** | Enviar credenciales incorrectas en el login. | Mensaje de error genérico que evita la enumeración de cuentas. | Muestra "Credenciales inválidas" sin indicar si falló el correo o la clave. | Aprobada |
| **T03** | Iniciar sesión válida como Administrador. | Regeneración del ID de sesión e interfaz adaptada a su rol. | Se ejecuta `session_regenerate_id(true)` y se visualizan opciones de gestión. | Aprobada |
| **T04** | Enviar POST a `eliminar.php` con cuenta de consulta. | Denegación por política en servidor con estado 403. | El servidor comprueba `exigir_rol('admin')`, bloquea la acción y no modifica datos. | Aprobada |
| **T05** | Enviar formulario POST habiendo eliminado el token CSRF. | Petición rechazada con código 403. | `validar_csrf()` detecta la discrepancia y cancela el procesamiento. | Aprobada |
| **T06** | Acceder a `guardar.php` directamente mediante método GET. | Rechazo de método no admitido con código 405. | El servidor verifica el verbo HTTP y retorna cabecera `Allow: POST`. | Aprobada |
| **T07** | Superar el tiempo de inactividad de sesión establecido. | Expiración en servidor y destrucción de credencial temporal. | Al interactuar nuevamente, la sesión ha caducado y solicita login. | Aprobada |
| **T08** | Fallar 2 intentos y acertar la clave en el 3.º. | Acceso permitido y restablecimiento del contador a 0. | El sistema limpia los intentos previos en MongoDB tras la autenticación válida. | Aprobada |
| **T09** | Provocar 3 fallos consecutivos con la misma cuenta. | Bloqueo temporal por 15 minutos reflejado en interfaz y Compass. | Muestra advertencia de espera; Compass registra `intentos_fallidos: 3` y `bloqueado_hasta`. | Aprobada |
| **T10** | Filtrar citas en tiempo real mediante inputs HTMX. | Actualización parcial del DOM sin recargar la página. | HTMX reemplaza `#resultados` manteniendo la sesión e indicadores visuales. | Aprobada |

---

## 9. Declaración de Uso de Inteligencia Artificial

De conformidad con la política de transparencia académica de la cátedra (Semana 5 · Parcial II), se declara el uso puntual y regulado de herramientas de Inteligencia Artificial durante el desarrollo del proyecto:

### Clasificación según la política de cátedra:

- **Nivel Verde (Permitido):**
  - **Localización de errores de sintaxis y arquitectura:** Se empleó IA para diagnosticar fallas en el enrutamiento de vistas (reubicación de `Views/citas/auth/login.php` hacia `Views/auth/login.php`) y en la resolución de peticiones erróneas del navegador hacia hojas de estilo (`GET /css/login.php` vs. `assets/css/estilos.css`).
  - **Generación de datos ficticios:** Generación de listas de motivos y casos de consulta para poblar la base de datos de pruebas.

- **Nivel Amarillo (Declarado):**
  - **Revisión de fragmentos y depuración de persistencia:** Se consultó sobre la persistencia atómica en MongoDB para el contador de intentos fallidos. Se identificó que el cálculo manual en PHP provocaba desincronización, optando por implementar el operador atómico `$inc` en `UsuarioModel::registrarFallo()`.
  - **Lógica de la máquina de estados de bloqueo:** Se solicitó análisis de la condición de desbloqueo temporal mediante comparación de marcas de tiempo UTC (`UTCDateTime`) entre PHP y MongoDB.

- **Nivel Rojo (Responsabilidad exclusiva del estudiante):**
  - Elección y justificación del control nuevo (Control de Intentos frente a riesgos de fuerza bruta en expedientes clínicos).
  - Integración completa del código en la estructura MVC de la aplicación.
  - Ejecución y verificación manual de las pruebas T01 a T10 con capturas en MongoDB Compass.
  - Dominio y defensa técnica individual del código en la sesión evaluativa.


  # Contrato de Entradas — ControlPsicologia (Guía 07)

| Dato | Origen | Regla que aplica PHP |
|---|---|---|
| `q` | GET (Búsqueda) | Texto UTF-8 de 0 a 60 caracteres; vacío significa sin filtro. |
| `tipo` | GET / POST | Uno de: `'Individual'`, `'Pareja'`, `'Infantil'`. En filtros GET se permite vacío. |
| `estado` | GET / POST | Uno de: `'Pendiente'`, `'Completada'`, `'Cancelada'`. En filtros GET se permite vacío. |
| `id` | GET / POST | Texto de 24 caracteres hexadecimales; después se convierte a ObjectId. |
| `motivo` | POST | Texto UTF-8 de 1 a 100 caracteres obligatorios. |
| `csrf` | POST | Token de sesión obligatorio. |
| `rol` / `bloqueado_hasta` | Servidor | Asignados internamente; jamás se aceptan desde formularios. |