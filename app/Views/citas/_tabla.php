<div class="tabla">
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="background-color: #28a745; color: white;">
                <th style="padding: 10px; border: 1px solid #ddd;">Motivo</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Tipo</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Estado</th>
                <?php if (tiene_rol('admin')): ?>
                    <th style="padding: 10px; border: 1px solid #ddd;">Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!$items): ?>
                <tr>
                    <td colspan="<?= tiene_rol('admin') ? 4 : 3 ?>" style="padding: 15px; text-align: center;">
                        No hay resultados para estos filtros.
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($items as $item): ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($item['motivo'] ?? '') ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($item['tipo'] ?? '') ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($item['estado'] ?? '') ?></td>

                    <?php if (tiene_rol('admin')): ?>
                        <td style="padding: 10px; border: 1px solid #ddd; white-space: nowrap;">
                            <a href="editar.php?id=<?= htmlspecialchars((string)$item['_id'], ENT_QUOTES, 'UTF-8') ?>"
                               class="btn-link">Editar</a>

                            <form action="eliminar.php" method="POST"
                                  onsubmit="return confirm('¿Eliminar esta cita?');"
                                  style="display:inline;">
                                <?= campo_csrf() ?>
                                <input type="hidden" name="id"
                                       value="<?= htmlspecialchars((string)$item['_id'], ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn-eliminar"
                                        style="background: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer;">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>