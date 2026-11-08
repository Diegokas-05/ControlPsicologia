<?php
declare(strict_types=1);

final class CitaController {
    private CitaModel $modelo;

    public function __construct(CitaModel $modelo) {
        $this->modelo = $modelo;
    }

    public function index(): void {
        $campos = [
            'tipo'   => ['Tipo de Terapia', TIPOS_CITA],
            'estado' => ['Estado de Cita', ESTADOS_CITA]
        ];

        // 1. Validar claves permitidas en GET
        entrada_campos($_GET, ['q', 'tipo', 'estado']);

        // 2. Validar opciones de los filtros con Entrada.php
        $filtro = [];
        $seleccion = [];

        foreach ($campos as $campo => [$etiqueta, $opciones]) {
            $valor = entrada_opcion($_GET, $campo, $opciones, true);
            $seleccion[$campo] = $valor;
            if ($valor !== '') {
                $filtro[$campo] = $valor;
            }
        }

        // 3. Control de acceso por rol: Si es psicólogo (rol consulta), restringir citas a su asignación
        $usuario = usuario_actual();
        if (($usuario['rol'] ?? '') === 'consulta') {
            $nombrePsicologo = (string)($usuario['nombre'] ?? '');
            $idPsicologo = (string)($usuario['id'] ?? '');

            $filtroRol = [
                '$or' => [
                    ['psicologo' => $nombrePsicologo],
                    ['psicologo' => $idPsicologo],
                ]
            ];

            if ($filtro === []) {
                $filtro = $filtroRol;
            } else {
                $filtro = ['$and' => [$filtro, $filtroRol]];
            }
        }

        // 4. Validar búsqueda segura por texto (0 a 60 caracteres)
        $busqueda = entrada_texto($_GET, 'q', 0, 60);

        // 5. Consulta al modelo (filtro ya sanitizado y controlado por el servidor)
        $items = $this->modelo->listar($filtro, $busqueda);
        $total = count($items);
        $titulo = 'Citas: Listado HTMX + MVC';

        // 6. Detectar HTMX o vista completa
        $esHtmx = strtolower((string)($_SERVER['HTTP_HX_REQUEST'] ?? '')) === 'true';

        if ($esHtmx) {
            require dirname(__DIR__) . '/Views/citas/_respuesta_htmx.php';
            return;
        }

        require dirname(__DIR__) . '/Views/citas/index.php';
    }
}
