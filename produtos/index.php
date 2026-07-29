<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/maps.php';

$base = '../';
$usuario = exigirLogin($pdo, $base);

$categorias = $pdo->query('SELECT id, nome FROM categorias ORDER BY nome ASC')->fetchAll();

$categoriaFiltro = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);

$sql = 'SELECT p.id, p.nome, p.imagem, p.preco, p.localizacao, p.latitude, p.longitude, u.nome AS cadastrado_por, c.nome AS categoria_nome
        FROM produtos p
        LEFT JOIN usuarios u ON u.id = p.usuario_id
        LEFT JOIN categorias c ON c.id = p.categoria_id';

if ($categoriaFiltro) {
    $sql .= ' WHERE p.categoria_id = :categoria_id';
}

$sql .= ' ORDER BY p.criado_em DESC';

$stmt = $pdo->prepare($sql);
if ($categoriaFiltro) {
    $stmt->execute(['categoria_id' => $categoriaFiltro]);
} else {
    $stmt->execute();
}
$produtos = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?php
            $mensagens = [
                'criado' => 'Produto cadastrado com sucesso!',
                'editado' => 'Produto atualizado com sucesso!',
                'excluido' => 'Produto excluído com sucesso!',
            ];
            echo htmlspecialchars($mensagens[$_GET['msg']] ?? '');
        ?>
    </div>
<?php endif; ?>

<div class="actions-bar filter-bar">
    <form method="get" action="index.php" class="filter-form">
        <label for="categoria_id">Filtrar por categoria:</label>
        <select id="categoria_id" name="categoria_id" onchange="this.form.submit()">
            <option value="">Todas as categorias</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?= (int)$categoria['id'] ?>" <?= $categoriaFiltro === (int)$categoria['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($categoria['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <a href="create.php" class="btn btn-success">+ Novo Produto</a>
</div>

<?php if (count($produtos) === 0): ?>
    <div class="card empty-state">
        <?= $categoriaFiltro ? 'Nenhum produto encontrado nesta categoria.' : 'Nenhum produto cadastrado ainda.' ?>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($produtos as $produto): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($produto['imagem']): ?>
                        <img src="<?= $base ?>uploads/produtos/<?= htmlspecialchars($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                    <?php else: ?>
                        <div class="product-image-placeholder">Sem imagem</div>
                    <?php endif; ?>
                </div>
                <div class="product-body">
                    <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                    <?php if ($produto['categoria_nome']): ?>
                        <span class="badge badge-muted"><?= htmlspecialchars($produto['categoria_nome']) ?></span>
                    <?php endif; ?>
                    <p class="product-price">R$ <?= number_format((float)$produto['preco'], 2, ',', '.') ?></p>
                    <p class="product-location"><?= htmlspecialchars($produto['localizacao'] ?? '-') ?></p>
                    <p class="product-owner">Cadastrado por: <?= htmlspecialchars($produto['cadastrado_por'] ?? '-') ?></p>
                    <?php if ($produto['latitude'] !== null && $produto['longitude'] !== null): ?>
                        <?php $coordenadas = $produto['latitude'] . ',' . $produto['longitude']; ?>
                        <a class="product-map-link" href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($coordenadas) ?>" target="_blank" rel="noopener">
                            <img
                                class="product-map-thumb"
                                src="https://maps.googleapis.com/maps/api/staticmap?center=<?= urlencode($coordenadas) ?>&zoom=15&size=300x120&markers=color:red%7C<?= urlencode($coordenadas) ?>&key=<?= urlencode(GOOGLE_MAPS_API_KEY) ?>"
                                alt="Mapa da localização de <?= htmlspecialchars($produto['nome']) ?>"
                                loading="lazy"
                            >
                            <span>Ver no Google Maps ↗</span>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="product-actions">
                    <a href="edit.php?id=<?= (int)$produto['id'] ?>" class="btn btn-warning">Editar</a>
                    <a href="delete.php?id=<?= (int)$produto['id'] ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este produto?');">Excluir</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
