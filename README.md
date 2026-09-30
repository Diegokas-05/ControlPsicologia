# Sistema ControlPsicologia - Avance 01

## 1. Identificación
* **Estudiante:** Diego Aaron
* **Asignatura:** Tecnologías Emergentes
* **Proyecto:** Sistema ControlPsicologia

## 2. Módulo Implementado
* **Módulo:** Gestión de Citas (CRUD).
* **Propósito:** Administrar las solicitudes de atención psicológica de los pacientes.
* **Requerimiento planificado:** Permite al personal administrativo registrar, consultar, modificar y eliminar las citas, validando la integridad de la información y facilitando la búsqueda por categorías.

## 3. Base de Datos y Colección
* **Base de datos:** `control_psicologia`
* **Colección:** `citas`
* **Campos del documento y valores permitidos:**
  * `_id`: ObjectId generado por MongoDB.
  * `motivo`: Texto (Obligatorio, máximo 100 caracteres).
  * `tipo` (Filtro 1): 'Individual', 'Pareja', 'Infantil'.
  * `estado` (Filtro 2): 'Pendiente', 'Completada', 'Cancelada'.
  * `fecha_creacion`: UTCDateTime.

## 4. Archivos Principales
* `config/Database.php`: Establece la conexión con MongoDB utilizando el driver de Composer.
* `app/models/Cita.php`: Modelo POO que ejecuta las consultas directas (find, insertOne, updateOne, deleteOne) en la base de datos.
* `public/index.php`: Interfaz principal que renderiza el listado, gestiona los parámetros GET y aplica los filtros de búsqueda.
* `public/crear.php` / `editar.php`: Vistas con formularios HTML para la entrada de datos.
* `public/guardar.php` / `actualizar.php` / `eliminar.php`: Controladores que reciben peticiones POST, validan en PHP y comunican a las vistas con el modelo.
* `public/probar_validacion.php`: Script automatizado para evaluar la lógica de validación de campos.

## 5. Instrucciones de Ejecución
Desde la terminal en la raíz del proyecto, ejecutar:
1. Reconstruir dependencias: `composer install`
2. Levantar el servidor local (asegurar que el servicio MongoDB está activo en puerto 27017): 
   `php -S localhost:8000 -t public`
3. Abrir en el navegador web: `http://localhost:8000/index.php`

## 6. Datos de los 3 Registros de Demostración
Para recrear el entorno, registrar los siguientes datos en el formulario:
1. **Registro 1:** Motivo: "Ansiedad generalizada", Tipo: "Individual", Estado: "Pendiente"
2. **Registro 2:** Motivo: "Terapia de pareja semanal", Tipo: "Pareja", Estado: "Completada"
3. **Registro 3:** Motivo: "Evaluación de conducta", Tipo: "Infantil", Estado: "Cancelada"

## 7. Tabla de Comprobaciones (P01 - P10)

| Prueba | Acción Realizada | Resultado Esperado | Resultado Observado | Estado |
|---|---|---|---|---|
| **P01** | Crear los tres registros válidos. | Cada registro se presenta en el listado y existe como documento en Compass. | Los registros aparecen en la tabla HTML y en MongoDB Compass. | Aprobada |
| **P02** | Consultar y recargar el listado. | Los campos mostrados coinciden con los guardados. | Los campos se renderizan correctamente mediante htmlspecialchars. | Aprobada |
| **P03** | Editar un registro y volver a abrirlo. | El valor nuevo permanece y su _id se conserva. | La operación updateOne actualiza los campos manteniendo el _id original. | Aprobada |
| **P04** | Guardar una edición sin cambiar datos. | La operación termina normalmente; no se informa un error falso. | Retorna éxito sin fallos del motor de base de datos. | Aprobada |
| **P05** | Aplicar cada filtro, combinarlos y limpiarlos. | El resultado cumple los valores elegidos y Limpiar devuelve el listado completo. | find() aplica los arrays de criterios correctamente. | Aprobada |
| **P06** | Probar una combinación sin coincidencias. | La aplicación muestra un mensaje de ausencia de resultados. | Aparece el recuadro rojo informando la falta de coincidencias. | Aprobada |
| **P07** | Ejecutar probar_validacion.php | Se aceptan datos válidos y se rechazan vacío, longitud y valores fuera de lista. | La tabla de prueba arroja "OK" para éxito y "FALLO" preciso para errores. | Aprobada |
| **P08** | Crear registro temporal y eliminarlo. | Solo ese registro desaparece de la aplicación y de Compass. | deleteOne() borra el registro tras la confirmación de JS. | Aprobada |
| **P09** | Abrir guardar.php directamente. | La aplicación rechaza GET y no inserta un documento. | Aparece mensaje rojo bloqueando el acceso GET y mostrando enlace de retorno. | Aprobada |
| **P10** | Cerrar navegador y reiniciar PHP. | Los tres registros siguen disponibles. | Los datos persisten correctamente en la colección de MongoDB local. | Aprobada |