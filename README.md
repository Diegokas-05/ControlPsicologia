# Sistema ControlPsicologia — Avance 02 (Parcial II)

## 1. Identificación

- **Estudiante:** Diego Aaron
- **Asignatura:** Tecnologías Emergentes — Unidad II
- **Proyecto:** Sistema ControlPsicologia
- **Entrega:** Parcial II — Arquitectura de Confianza
- **Fecha de entrega:** 8 de octubre de 2026

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
- **Control nuevo:** Control de Intentos de Ingreso con bloqueo temporal.
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

- `config/Database.php`: conexión a MongoDB mediante el driver de Composer.

### Modelos (`app/Models/`)

- `UsuarioModel.php`: búsqueda de usuarios, registro de intentos fallidos, bloqueo y limpieza.
- `CitaModel.php`: operaciones CRUD sobre la colección `citas`.
- `BitacoraModel.php`: registro de eventos de auditoría.

### Controladores (`app/Controllers/`)

- `AuthController.php`: login, control de intentos, sesión y bitácora.
- `CitaController.php`: listado, filtros y operaciones del CRUD.

### Seguridad (`app/Security/`)

- `Sesion.php`: inicio de sesión segura, timeouts, roles y logout.
- `Csrf.php`: token sincronizado para proteger escrituras.

### Vistas (`app/Views/`)

- `auth/login.php`: formulario de acceso.
- `citas/*.php`: listado, creación, edición.

### Rutas públicas (`public/`)

- `login.php`: procesa GET (formulario) y POST (login).
- `logout.php`: destruye la sesión y la cookie.
- `mvc.php` / `index.php`: listado principal.
- `crear.php`, `editar.php`: formularios.
- `guardar.php`, `actualizar.php`, `eliminar.php`: rutas POST protegidas.

### Scripts (`scripts/`)

- `preparar_cuentas.php`: crea las cuentas ficticias y el índice único.
- `probar_validacion.php`: prueba automatizada de validaciones.

---

## 5. Cuentas ficticias

| Nombre | Correo | Clave | Rol |
|---|---|---|---|
| Administrador Psicología | `admin@gmail.com` | `Admin2026` | `admin` |
| Asistente Consulta | `consulta@gmail.com` | `hola` | `consulta` |

---

## 6. Matriz de permisos

| Acción | admin | consulta |
|---|---|---|
| Listar / buscar citas | Permitir | Permitir |
| Crear cita | Permitir | Denegar |
| Editar cita | Permitir | Denegar |
| Eliminar cita | Permitir | Denegar |
| Ver bitácora | Permitir | Denegar |

Deny by default: si una acción no está explícitamente permitida, la respuesta es `403`.

---

## 7. Instrucciones de ejecución

Desde la terminal en la raíz del proyecto:

1. Instalar dependencias:
   ```bash
   composer install