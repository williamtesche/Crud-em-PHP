<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/upload_helper.php';

$base = '../';
$usuarioAtual = exigirLogin($pdo, $base);

$erros = [];
$dados = [
    'nome' => '',
    'preco' => '',
    'localizacao' => '',
    'categoria_id' => '',
    'latitude' => '',
    'longitude' => '',
];

$categorias = $pdo->query('SELECT id, nome FROM categorias ORDER BY nome ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados['nome'] = trim($_POST['nome'] ?? '');
    $dados['preco'] = trim($_POST['preco'] ?? '');
    $dados['localizacao'] = trim($_POST['localizacao'] ?? '');
    $dados['categoria_id'] = trim($_POST['categoria_id'] ?? '');
    $dados['latitude'] = trim($_POST['latitude'] ?? '');
    $dados['longitude'] = trim($_POST['longitude'] ?? '');

    $precoNormalizado = str_replace(',', '.', $dados['preco']);

    if ($dados['nome'] === '') {
        $erros['nome'] = 'O nome do produto é obrigatório.';
    }

    if ($dados['preco'] === '') {
        $erros['preco'] = 'O preço é obrigatório.';
    } elseif (!is_numeric($precoNormalizado) || (float)$precoNormalizado < 0) {
        $erros['preco'] = 'Informe um preço válido.';
    }

    $categoriaId = $dados['categoria_id'] !== '' ? (int)$dados['categoria_id'] : null;
    if ($categoriaId !== null && !in_array($categoriaId, array_column($categorias, 'id'), true)) {
        $erros['categoria_id'] = 'Categoria inválida.';
    }

    $latitude = null;
    $longitude = null;
    if ($dados['latitude'] !== '' && $dados['longitude'] !== '') {
        if (is_numeric($dados['latitude']) && is_numeric($dados['longitude'])
            && (float)$dados['latitude'] >= -90 && (float)$dados['latitude'] <= 90
            && (float)$dados['longitude'] >= -180 && (float)$dados['longitude'] <= 180) {
            $latitude = (float)$dados['latitude'];
            $longitude = (float)$dados['longitude'];
        }
    }

    $nomeArquivo = null;
    if (!empty($_FILES['imagem']['name'])) {
        $nomeArquivo = processarUploadImagem($_FILES['imagem'], $erros);
    }

    if (empty($erros)) {
        $stmt = $pdo->prepare(
            'INSERT INTO produtos (nome, imagem, preco, localizacao, latitude, longitude, categoria_id, usuario_id)
             VALUES (:nome, :imagem, :preco, :localizacao, :latitude, :longitude, :categoria_id, :usuario_id)'
        );
        $stmt->execute([
            'nome' => $dados['nome'],
            'imagem' => $nomeArquivo,
            'preco' => (float)$precoNormalizado,
            'localizacao' => $dados['localizacao'] !== '' ? $dados['localizacao'] : null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'categoria_id' => $categoriaId,
            'usuario_id' => $usuarioAtual['id'],
        ]);

        header('Location: index.php?msg=criado');
        exit;
    }
}

$usuario = $usuarioAtual;
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Novo Produto</h2>

<div class="card">
    <form method="post" action="create.php" enctype="multipart/form-data" novalidate>
        <div class="form-group">
            <label for="nome">Nome *</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dados['nome']) ?>" maxlength="150">
            <?php if (isset($erros['nome'])): ?><div class="field-error"><?= htmlspecialchars($erros['nome']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="preco">Preço (R$) *</label>
            <input type="text" id="preco" name="preco" value="<?= htmlspecialchars($dados['preco']) ?>" placeholder="0,00">
            <?php if (isset($erros['preco'])): ?><div class="field-error"><?= htmlspecialchars($erros['preco']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="localizacao">Localização</label>
            <input type="text" id="localizacao" name="localizacao" value="<?= htmlspecialchars($dados['localizacao']) ?>" maxlength="255" placeholder="Ex: Loja Centro">
        </div>

        <?php require __DIR__ . '/../includes/mapa_picker.php'; ?>

        <div class="form-group">
            <label for="categoria_id">Categoria</label>
            <select id="categoria_id" name="categoria_id">
                <option value="">Sem categoria</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= (int)$categoria['id'] ?>" <?= (string)$dados['categoria_id'] === (string)$categoria['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categoria['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($erros['categoria_id'])): ?><div class="field-error"><?= htmlspecialchars($erros['categoria_id']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="imagem">Imagem do Produto</label>
            <input type="file" id="imagem" name="imagem" accept=".jpg,.jpeg,.png,.gif,.webp">
            <?php if (isset($erros['imagem'])): ?><div class="field-error"><?= htmlspecialchars($erros['imagem']) ?></div><?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
