# Sistema ControlPsicologia

Plataforma web administrativa para clínicas psicológicas orientada a la gestión de expedientes y citas. Desarrollada bajo el patrón de arquitectura Modelo-Vista-Controlador (MVC).

## Tecnologías y Entorno
* **Backend:** PHP 8.5 (Programación Orientada a Objetos)
* **Base de Datos:** MongoDB (Driver TS x64)
* **Frontend:** HTML, CSS, JavaScript (Próximamente)

## Arquitectura del Proyecto
* `/app/controllers/`: Validaciones y lógica de peticiones.
* `/app/models/`: Clases y operaciones directas con MongoDB.
* `/app/views/`: Interfaces gráficas y componentes HTML.
* `/config/`: Archivos críticos (Ej. `Database.php`).
* `/public/`: Punto de entrada único (`index.php`) y assets.
* `/docs/`: Requisitos y diccionario de la base de datos.

## Instalación y Ejecución
1. Asegurarse de tener el servidor de MongoDB corriendo localmente en el puerto `27017`.
2. Verificar que la extensión `mongodb` esté activa en el `php.ini`.
3. Abrir la terminal en la raíz del proyecto y levantar el servidor web embebido:
   `php -S localhost:8000 -t public`