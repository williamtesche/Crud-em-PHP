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

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
$stmt->execute(['id' => $id]);
$registro = $stmt->fetch();

if (!$registro) {
    header('Location: index.php');
    exit;
}

if ($registro['is_admin'] && !$usuarioAtual['is_admin']) {
    header('Location: index.php?erro=acesso_negado');
    exit;
}

$erros = [];
$dados = [
    'nome' => $registro['nome'],
    'email' => $registro['email'],
    'telefone' => $registro['telefone'],
    'data_nascimento' => $registro['data_nascimento'],
    'endereco' => $registro['endereco'],
    'pode_cadastrar_usuarios' => (bool)$registro['pode_cadastrar_usuarios'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados['nome'] = trim($_POST['nome'] ?? '');
    $dados['email'] = trim($_POST['email'] ?? '');
    $dados['telefone'] = trim($_POST['telefone'] ?? '');
    $dados['data_nascimento'] = trim($_POST['data_nascimento'] ?? '');
    $dados['endereco'] = trim($_POST['endereco'] ?? '');
    $dados['pode_cadastrar_usuarios'] = isset($_POST['pode_cadastrar_usuarios']);
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if ($dados['nome'] === '') {
        $erros['nome'] = 'O nome é obrigatório.';
    }

    if ($dados['email'] === '') {
        $erros['email'] = 'O e-mail é obrigatório.';
    } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'Informe um e-mail válido.';
    }

    if ($senha !== '') {
        if (strlen($senha) < 4) {
            $erros['senha'] = 'A senha deve ter pelo menos 4 caracteres.';
        } elseif ($senha !== $confirmarSenha) {
            $erros['senha'] = 'As senhas não coincidem.';
        }
    }

    if (empty($erros)) {
        $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email AND id != :id');
        $check->execute(['email' => $dados['email'], 'id' => $id]);
        if ($check->fetch()) {
            $erros['email'] = 'Já existe outro usuário cadastrado com este e-mail.';
        }
    }

    if (empty($erros) && !$dados['pode_cadastrar_usuarios'] && (int)$registro['pode_cadastrar_usuarios'] === 1) {
        $totalStmt = $pdo->query('SELECT COUNT(*) AS total FROM usuarios WHERE pode_cadastrar_usuarios = 1');
        $total = (int)$totalStmt->fetch()['total'];
        if ($total <= 1) {
            $erros['pode_cadastrar_usuarios'] = 'Não é possível remover a permissão do último usuário que pode gerenciar usuários.';
        }
    }

    if (empty($erros)) {
        $params = [
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] !== '' ? $dados['telefone'] : null,
            'data_nascimento' => $dados['data_nascimento'] !== '' ? $dados['data_nascimento'] : null,
            'endereco' => $dados['endereco'] !== '' ? $dados['endereco'] : null,
            'pode' => $dados['pode_cadastrar_usuarios'] ? 1 : 0,
            'id' => $id,
        ];

        if ($senha !== '') {
            $stmt = $pdo->prepare(
                'UPDATE usuarios
                 SET nome = :nome, email = :email, senha = :senha, telefone = :telefone,
                     data_nascimento = :data_nascimento, endereco = :endereco, pode_cadastrar_usuarios = :pode
                 WHERE id = :id'
            );
            $params['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE usuarios
                 SET nome = :nome, email = :email, telefone = :telefone,
                     data_nascimento = :data_nascimento, endereco = :endereco, pode_cadastrar_usuarios = :pode
                 WHERE id = :id'
            );
        }

        $stmt->execute($params);

        header('Location: index.php?msg=editado');
        exit;
    }
}

$usuario = $usuarioAtual;
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Editar Usuário</h2>

<div class="card">
    <form method="post" action="edit.php?id=<?= (int)$id ?>" novalidate>
        <div class="form-group">
            <label for="nome">Nome *</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dados['nome']) ?>" maxlength="150">
            <?php if (isset($erros['nome'])): ?><div class="field-error"><?= htmlspecialchars($erros['nome']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($dados['email']) ?>" maxlength="150">
            <?php if (isset($erros['email'])): ?><div class="field-error"><?= htmlspecialchars($erros['email']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="senha">Nova Senha</label>
            <input type="password" id="senha" name="senha" placeholder="Deixe em branco para manter a senha atual">
            <?php if (isset($erros['senha'])): ?><div class="field-error"><?= htmlspecialchars($erros['senha']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="confirmar_senha">Confirmar Nova Senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha">
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($dados['telefone'] ?? '') ?>" maxlength="20">
        </div>

        <div class="form-group">
            <label for="data_nascimento">Data de Nascimento</label>
            <input type="date" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($dados['data_nascimento'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($dados['endereco'] ?? '') ?>" maxlength="255">
        </div>

        <div class="form-group form-check">
            <label>
                <input type="checkbox" name="pode_cadastrar_usuarios" value="1" <?= $dados['pode_cadastrar_usuarios'] ? 'checked' : '' ?>>
                Permitir que este usuário também cadastre outros usuários
            </label>
            <?php if (isset($erros['pode_cadastrar_usuarios'])): ?><div class="field-error"><?= htmlspecialchars($erros['pode_cadastrar_usuarios']) ?></div><?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">Atualizar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
