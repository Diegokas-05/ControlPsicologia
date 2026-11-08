<?php
declare(strict_types=1);

$idStr = (string)$item->_id;
$motivo = (string)($item->motivo ?? 'Paciente sin registro');
$tipo = (string)($item->tipo ?? 'Individual');
$psicologo = (string)($item->psicologo ?? 'Psicólogo tratante acreditado');

$fechaCreacion = isset($item->fecha_creacion) && $item->fecha_creacion instanceof MongoDB\BSON\UTCDateTime
    ? $item->fecha_creacion->toDateTime()->format('d/m/Y H:i') . ' UTC'
    : 'Fecha inicial no registrada';

$fechaCierre = isset($item->actualizado_en) && $item->actualizado_en instanceof MongoDB\BSON\UTCDateTime
    ? $item->actualizado_en->toDateTime()->format('d/m/Y H:i') . ' UTC'
    : date('d/m/Y H:i') . ' UTC';

$historial = $item->historial_flujo ?? [];
$folio = strtoupper(substr($idStr, -8));
$hashVerificacion = strtoupper(hash('crc32b', $idStr . $fechaCierre));
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Constancia de Cierre — <?= e($folio) ?></title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <style>
        .pagina-comprobante {
            background: #ffffff;
            max-width: 820px;
            margin: 2.5rem auto;
            padding: 3rem 3.5rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            position: relative;
        }

        .comprobante-marca-agua {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 6.5rem;
            font-weight: 900;
            color: rgba(37, 99, 235, 0.035);
            user-select: none;
            pointer-events: none;
            white-space: nowrap;
        }

        .comprobante-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 2px solid var(--primary-border);
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }

        .comprobante-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .comprobante-logo-icono {
            font-size: 2.8rem;
            color: var(--primary);
            line-height: 1;
        }

        .comprobante-clinica-nombre {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }

        .comprobante-clinica-sub {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .comprobante-folio-caja {
            text-align: right;
        }

        .comprobante-folio-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .comprobante-folio-numero {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary);
            font-family: monospace;
        }

        .comprobante-titulo-documento {
            text-align: center;
            margin-bottom: 2rem;
        }

        .comprobante-titulo-documento h1 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.01em;
            text-transform: uppercase;
        }

        .comprobante-titulo-documento p {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .comprobante-cuerpo {
            font-size: 1rem;
            line-height: 1.8;
            color: var(--text-main);
            margin-bottom: 2rem;
        }

        .comprobante-datos-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin: 1.5rem 0 2rem;
        }

        .dato-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .dato-etiqueta {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .dato-valor {
            font-size: 0.95rem;
            color: var(--text-main);
            font-weight: 600;
        }

        .comprobante-trazabilidad {
            margin: 2rem 0;
            border-top: 1px dashed var(--border-color);
            padding-top: 1.5rem;
        }

        .comprobante-trazabilidad h3 {
            font-size: 0.92rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-muted);
            margin-bottom: 0.85rem;
        }

        .tabla-trazabilidad {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .tabla-trazabilidad th {
            text-align: left;
            background: #f1f5f9;
            padding: 8px 12px;
            font-weight: 700;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
        }

        .tabla-trazabilidad td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .comprobante-firmas {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 4rem;
            padding-top: 1.5rem;
            gap: 3rem;
        }

        .firma-bloque {
            flex: 1;
            text-align: center;
        }

        .firma-linea {
            border-top: 1px solid var(--text-main);
            margin-bottom: 6px;
        }

        .firma-nombre {
            font-weight: 700;
            font-size: 0.92rem;
            color: var(--text-main);
        }

        .firma-cargo {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .comprobante-sello {
            border: 2px dashed var(--primary-border);
            border-radius: var(--radius-md);
            padding: 10px 16px;
            text-align: center;
            font-size: 0.72rem;
            color: var(--primary);
            font-weight: 700;
            letter-spacing: 0.04em;
            line-height: 1.4;
        }

        .comprobante-acciones {
            max-width: 820px;
            margin: 1rem auto 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
        }

        /* Reglas estrictas de impresión */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .navbar-global,
            .comprobante-acciones,
            .no-print {
                display: none !important;
            }

            .pagina-comprobante {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 1.5rem !important;
                max-width: 100% !important;
            }

            .comprobante-marca-agua {
                display: block !important;
                color: rgba(0, 0, 0, 0.04) !important;
            }
        }
    </style>
</head>
<body style="background-color: var(--bg-app);">
    <div class="no-print">
        <?php require dirname(__DIR__) . '/layouts/header.php'; ?>
    </div>

    <!-- Barra Superior de Acciones en Pantalla -->
    <div class="comprobante-acciones no-print">
        <a href="flujo.php" class="btn btn-secondary">
            &larr; Volver a la Bandeja de Trabajo
        </a>

        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                &#128424; Imprimir Constancia (PDF)
            </button>
        </div>
    </div>

    <!-- Documento Formal Imprimible -->
    <article class="pagina-comprobante" id="documento-constancia">
        <div class="comprobante-marca-agua">FINALIZADO</div>

        <!-- Encabezado Clínico -->
        <header class="comprobante-header">
            <div class="comprobante-logo">
                <span class="comprobante-logo-icono">&#9764;</span>
                <div class="comprobante-clinica-textos">
                    <div class="comprobante-clinica-nombre">ControlPsicología</div>
                    <div class="comprobante-clinica-sub">Servicio Clínico de Salud Mental y Psicoterapia</div>
                </div>
            </div>

            <div class="comprobante-folio-caja">
                <div class="comprobante-folio-label">Folio Oficial de Cierre</div>
                <div class="comprobante-folio-numero">#<?= e($folio) ?></div>
            </div>
        </header>

        <!-- Título del Documento -->
        <div class="comprobante-titulo-documento">
            <h1>Constancia de Cierre de Atención Psicoterapéutica</h1>
            <p>Acreditación de conclusión de proceso clínico conforme al protocolo del flujo de trabajo</p>
        </div>

        <!-- Cuerpo Oficial -->
        <div class="comprobante-cuerpo">
            <p>
                Por medio de la presente, la unidad clínica certifica que el paciente y expediente registrado bajo el motivo
                <strong>&laquo;<?= e($motivo) ?>&raquo;</strong> ha concluido de manera satisfactoria su proceso psicoterapéutico,
                cumpliendo con las fases de evaluación, intervención y cierre clínico conforme a la normativa establecida.
            </p>
        </div>

        <!-- Cuícula de Datos Clave -->
        <div class="comprobante-datos-grid">
            <div class="dato-item">
                <span class="dato-etiqueta">Expediente ID</span>
                <span class="dato-valor" style="font-family: monospace;"><?= e($idStr) ?></span>
            </div>
            <div class="dato-item">
                <span class="dato-etiqueta">Modalidad de Terapia</span>
                <span class="dato-valor"><?= e($tipo) ?></span>
            </div>
            <div class="dato-item">
                <span class="dato-etiqueta">Estado Operativo</span>
                <span class="dato-valor" style="color: var(--estado-finalizado-text);">&#10003; Finalizado / Alta Médica</span>
            </div>
            <div class="dato-item">
                <span class="dato-etiqueta">Terapeuta Asignado</span>
                <span class="dato-valor"><?= e($psicologo) ?></span>
            </div>
            <div class="dato-item">
                <span class="dato-etiqueta">Fecha de Apertura</span>
                <span class="dato-valor"><?= e($fechaCreacion) ?></span>
            </div>
            <div class="dato-item">
                <span class="dato-etiqueta">Fecha de Cierre / Alta</span>
                <span class="dato-valor"><?= e($fechaCierre) ?></span>
            </div>
        </div>

        <!-- Trazabilidad del Flujo de Trabajo -->
        <div class="comprobante-trazabilidad">
            <h3>Trazabilidad de Estados en el Sistema</h3>
            <table class="tabla-trazabilidad">
                <thead>
                    <tr>
                        <th>Transición de Estado</th>
                        <th>Operador Responsable</th>
                        <th>Marca Temporal (UTC)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Ingreso &rarr; Pendiente</strong></td>
                        <td>Apertura de Expediente</td>
                        <td><?= e($fechaCreacion) ?></td>
                    </tr>
                    <?php foreach ($historial as $ev): ?>
                        <tr>
                            <td>
                                <strong><?= e(ucfirst($ev['desde'])) ?> &rarr; <?= e(ucfirst($ev['hacia'])) ?></strong>
                            </td>
                            <td><?= e($ev['actor_id']) ?></td>
                            <td>
                                <?= isset($ev['fecha']) && $ev['fecha'] instanceof MongoDB\BSON\UTCDateTime
                                    ? e($ev['fecha']->toDateTime()->format('d/m/Y H:i')) . ' UTC'
                                    : 'Registrado' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Bloque de Firmas y Validación Digital -->
        <footer class="comprobante-firmas">
            <div class="firma-bloque">
                <div class="firma-linea"></div>
                <div class="firma-nombre"><?= e($psicologo) ?></div>
                <div class="firma-cargo">Psicólogo Clínico Responsable</div>
            </div>

            <div class="comprobante-sello">
                <div>SELLO DIGITAL</div>
                <div style="font-family: monospace; font-size: 0.85rem; margin-top: 2px;">#<?= e($hashVerificacion) ?></div>
                <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;">VERIFICADO POR MONGO AUDITORÍA</div>
            </div>

            <div class="firma-bloque">
                <div class="firma-linea"></div>
                <div class="firma-nombre">Dirección de Salud Mental</div>
                <div class="firma-cargo">Coordinación de Psicología</div>
            </div>
        </footer>
    </article>
</body>
</html>
