<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$base = '../';
$usuarioAtual = exigirPermissaoUsuarios($pdo, $base);

$erros = [];
$dados = ['nome' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados['nome'] = trim($_POST['nome'] ?? '');

    if ($dados['nome'] === '') {
        $erros['nome'] = 'O nome da categoria é obrigatório.';
    }

    if (empty($erros)) {
        $check = $pdo->prepare('SELECT id FROM categorias WHERE nome = :nome');
        $check->execute(['nome' => $dados['nome']]);
        if ($check->fetch()) {
            $erros['nome'] = 'Já existe uma categoria com este nome.';
        }
    }

    if (empty($erros)) {
        $stmt = $pdo->prepare('INSERT INTO categorias (nome) VALUES (:nome)');
        $stmt->execute(['nome' => $dados['nome']]);

        header('Location: index.php?msg=criado');
        exit;
    }
}

$usuario = $usuarioAtual;
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Nova Categoria</h2>

<div class="card">
    <form method="post" action="create.php" novalidate>
        <div class="form-group">
            <label for="nome">Nome *</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dados['nome']) ?>" maxlength="100">
            <?php if (isset($erros['nome'])): ?><div class="field-error"><?= htmlspecialchars($erros['nome']) ?></div><?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
