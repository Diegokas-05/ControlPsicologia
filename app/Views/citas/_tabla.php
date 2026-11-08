<div class="tabla-responsive">
    <table class="tabla-clinica">
        <thead>
            <tr>
                <th>Motivo de Consulta</th>
                <th>Tipo de Terapia</th>
                <th>Estado</th>
                <th style="text-align: center;">Expediente Clínico</th>
                <?php if (tiene_rol('admin')): ?>
                    <th style="text-align: right;">Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="<?= tiene_rol('admin') ? 5 : 4 ?>" style="text-align: center; padding: 2.5rem; color: var(--text-muted); font-style: italic;">
                        No se encontraron citas clínicas con los filtros aplicados.
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($items as $item): ?>
                <?php 
                    $id = is_object($item) ? (string)($item->_id ?? '') : (string)($item['_id'] ?? '');
                    $motivo = is_object($item) ? ($item->motivo ?? '') : ($item['motivo'] ?? '');
                    $tipo = is_object($item) ? ($item->tipo ?? '') : ($item['tipo'] ?? '');
                    $estado = is_object($item) ? ($item->estado ?? '') : ($item['estado'] ?? '');

                    $slugTipo = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string)$tipo));
                    $slugEstado = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string)$estado));
                ?>
                <tr>
                    <td>
                        <strong style="color: var(--text-main);"><?= e($motivo) ?></strong>
                    </td>
                    <td>
                        <span class="badge badge-tipo-<?= e($slugTipo) ?>">
                            <?= e($tipo) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-estado-<?= e($slugEstado) ?>">
                            <?= e($estado) ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <a href="expediente.php?id=<?= e($id) ?>" class="btn btn-sm btn-expediente" title="Abrir expediente clínico completo">
                            &#128196; Ver Expediente
                        </a>
                    </td>

                    <?php if (tiene_rol('admin')): ?>
                        <td>
                            <div class="acciones-fila">
                                <a href="editar.php?id=<?= e($id) ?>" class="btn btn-sm btn-editar" title="Modificar datos de la cita">
                                    Editar
                                </a>

                                <button type="button" class="btn btn-sm btn-danger"
                                        onclick="confirmarEliminar('<?= e($id) ?>', '<?= e(addslashes($motivo)) ?>')"
                                        title="Eliminar cita clínica">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
