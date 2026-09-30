<?php
declare(strict_types=1);

final class CitaController {
    private CitaModel $modelo;

    public function __construct(CitaModel $modelo) {
        $this->modelo = $modelo;
    }

    public function index(): void {
        $campos = [
            'tipo' => ['Tipo de Terapia', ['Individual', 'Pareja', 'Infantil']],
            'estado' => ['Estado de Cita', ['Pendiente', 'Completada', 'Cancelada']]
        ];

        $filtro = [];
        $seleccion = [];

        foreach ($campos as $campo => [$etiqueta, $opciones]) {
            $valor = $_GET[$campo] ?? '';
            if (!is_string($valor) || ($valor !== '' && !in_array($valor, $opciones, true))) {
                http_response_code(400);
                exit('Filtro inválido.');
            }
            $seleccion[$campo] = $valor;
            if ($valor !== '') {
                $filtro[$campo] = $valor;
            }
        }

        // Nueva validación estricta para la búsqueda por texto
        $busqueda = $_GET['q'] ?? '';
        if (!is_string($busqueda)) {
            http_response_code(400);
            exit('La búsqueda debe ser texto.');
        }
        $busqueda = trim($busqueda);
        if (mb_strlen($busqueda) > 60) {
            http_response_code(400);
            exit('La búsqueda supera 60 caracteres.');
        }

        $items = $this->modelo->listar($filtro, $busqueda);
        $total = count($items);
        $titulo = 'Citas: Listado HTMX + MVC';

        // Detectar si es una petición de HTMX para enviar solo el pedazo de código
        $esHtmx = strtolower((string)($_SERVER['HTTP_HX_REQUEST'] ?? '')) === 'true';

        if ($esHtmx) {
            require dirname(__DIR__) . '/Views/citas/_respuesta_htmx.php';
            return;
        }

        require dirname(__DIR__) . '/Views/citas/index.php';
    }
}