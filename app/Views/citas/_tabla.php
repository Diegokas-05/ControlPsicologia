<div class="tabla">
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="background-color: #28a745; color: white;">
                <th style="padding: 10px; border: 1px solid #ddd;">Motivo</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Tipo</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$items): ?>
                <tr><td colspan="3" style="padding: 15px; text-align: center;">No hay resultados para estos filtros.</td></tr>
            <?php endif; ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($item['motivo'] ?? '') ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($item['tipo'] ?? '') ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($item['estado'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>