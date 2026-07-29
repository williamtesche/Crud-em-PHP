<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$base = '../';
$usuarioAtual = exigirPermissaoUsuarios($pdo, $base);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM categorias WHERE id = :id');
$stmt->execute(['id' => $id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    header('Location: index.php');
    exit;
}

$erros = [];
$dados = ['nome' => $categoria['nome']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados['nome'] = trim($_POST['nome'] ?? '');

    if ($dados['nome'] === '') {
        $erros['nome'] = 'O nome da categoria é obrigatório.';
    }

    if (empty($erros)) {
        $check = $pdo->prepare('SELECT id FROM categorias WHERE nome = :nome AND id != :id');
        $check->execute(['nome' => $dados['nome'], 'id' => $id]);
        if ($check->fetch()) {
            $erros['nome'] = 'Já existe outra categoria com este nome.';
        }
    }

    if (empty($erros)) {
        $stmt = $pdo->prepare('UPDATE categorias SET nome = :nome WHERE id = :id');
        $stmt->execute(['nome' => $dados['nome'], 'id' => $id]);

        header('Location: index.php?msg=editado');
        exit;
    }
}

$usuario = $usuarioAtual;
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Editar Categoria</h2>

<div class="card">
    <form method="post" action="edit.php?id=<?= (int)$id ?>" novalidate>
        <div class="form-group">
            <label for="nome">Nome *</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dados['nome']) ?>" maxlength="100">
            <?php if (isset($erros['nome'])): ?><div class="field-error"><?= htmlspecialchars($erros['nome']) ?></div><?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">Atualizar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
