<p class="resumen-total-texto">Total de registros analizados: <strong><?= e((string)$resumen['total']) ?></strong></p>
<div class="tabla-responsive">
    <table class="tabla-clinica tabla-resumen">
        <thead>
            <tr>
                <th>Estado del Flujo</th>
                <th class="col-centrada">Cantidad</th>
                <th class="col-centrada">Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resumen['grupos'] as $fila): ?>
                <?php 
                    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string)$fila['etiqueta']));
                ?>
                <tr>
                    <td>
                        <span class="badge badge-estado-<?= e($slug) ?>">
                            <?= e($fila['etiqueta']) ?>
                        </span>
                    </td>
                    <td class="col-centrada"><strong><?= e((string)$fila['cantidad']) ?></strong></td>
                    <td class="col-centrada">
                        <span class="porcentaje-pill">
                            <?= e(number_format((float)$fila['porcentaje'], 2)) ?> %
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
