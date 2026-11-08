<p class="bandeja-subtitulo">Se muestran hasta 50 registros del estado seleccionado (más recientes primero):</p>

<?php if ($items === []): ?>
    <div class="alerta-vacia">
        <span>No se encontraron citas con este estado en la bandeja.</span>
    </div>
<?php endif; ?>

<div class="lista-tarjetas-flujo">
    <?php foreach ($items as $item): ?>
        <?php
        $estadoFlujo = (string)($item->estado_flujo ?? 'pendiente');
        $slugEstado = strtolower($estadoFlujo);
        $accion = match ($estadoFlujo) {
            'pendiente' => 'iniciar',
            'en_proceso' => 'finalizar',
            default => null,
        };
        ?>
        <article class="item-flujo">
            <div class="item-flujo-cabecera">
                <h4 class="item-flujo-titulo"><?= e($item->motivo ?? 'Cita sin motivo especificado') ?></h4>
                <span class="badge badge-estado-<?= e($slugEstado) ?>">
                    <?= e($estadoFlujo) ?>
                </span>
            </div>

            <div class="item-flujo-cuerpo">
                <?php if ($accion !== null): ?>
                    <form action="flujo_accion.php" method="post" data-flujo
                          hx-post="flujo_accion.php" hx-target="#mensaje-flujo"
                          hx-swap="innerHTML" hx-disabled-elt="find button"
                          class="form-accion-flujo">
                        <?= campo_csrf() ?>
                        <input type="hidden" name="id" value="<?= e($item->_id) ?>">
                        <input type="hidden" name="accion" value="<?= e($accion) ?>">
                        <button type="submit" class="boton boton-sm <?= $accion === 'iniciar' ? 'boton-primario' : 'boton-exito' ?>">
                            <?= $accion === 'iniciar' ? 'Iniciar atención' : 'Concluir atención' ?>
                        </button>
                    </form>
                <?php else: ?>
                    <span class="texto-finalizado">&#10003; Proceso concluido</span>
                <?php endif; ?>

                <?php if (($item->estado_flujo ?? '') === 'finalizado'): ?>
                    <p style="margin: 6px 0 0;">
                        <a href="comprobante.php?id=<?= e((string)$item->_id) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">
                            &#128196; Ver constancia de cierre
                        </a>
                    </p>
                <?php endif; ?>
            </div>

            <details class="item-flujo-historial">
                <summary>Ver historial de cambios</summary>
                <ol class="lista-historial">
                    <?php foreach (($item->historial_flujo ?? []) as $evento): ?>
                        <li>
                            <span class="historial-transicion"><?= e($evento['desde']) ?> &rarr; <?= e($evento['hacia']) ?></span>
                            <span class="historial-meta">por <?= e($evento['actor_id']) ?> &bull; <?= e($evento['fecha']->toDateTime()->format('d/m/Y H:i')) ?> UTC</span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </details>
        </article>
    <?php endforeach; ?>
</div>
