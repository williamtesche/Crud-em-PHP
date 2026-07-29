<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$base = '../';
$usuarioAtual = exigirPermissaoUsuarios($pdo, $base);

$erros = [];
$dados = [
    'nome' => '',
    'email' => '',
    'telefone' => '',
    'data_nascimento' => '',
    'endereco' => '',
    'pode_cadastrar_usuarios' => false,
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

    if ($senha === '') {
        $erros['senha'] = 'A senha é obrigatória.';
    } elseif (strlen($senha) < 4) {
        $erros['senha'] = 'A senha deve ter pelo menos 4 caracteres.';
    } elseif ($senha !== $confirmarSenha) {
        $erros['senha'] = 'As senhas não coincidem.';
    }

    if (empty($erros)) {
        $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
        $check->execute(['email' => $dados['email']]);
        if ($check->fetch()) {
            $erros['email'] = 'Já existe um usuário cadastrado com este e-mail.';
        }
    }

    if (empty($erros)) {
        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha, telefone, data_nascimento, endereco, pode_cadastrar_usuarios)
             VALUES (:nome, :email, :senha, :telefone, :data_nascimento, :endereco, :pode_cadastrar_usuarios)'
        );
        $stmt->execute([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'telefone' => $dados['telefone'] !== '' ? $dados['telefone'] : null,
            'data_nascimento' => $dados['data_nascimento'] !== '' ? $dados['data_nascimento'] : null,
            'endereco' => $dados['endereco'] !== '' ? $dados['endereco'] : null,
            'pode_cadastrar_usuarios' => $dados['pode_cadastrar_usuarios'] ? 1 : 0,
        ]);

        header('Location: index.php?msg=criado');
        exit;
    }
}

$usuario = $usuarioAtual;
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Novo Usuário</h2>

<div class="card">
    <form method="post" action="create.php" novalidate>
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
            <label for="senha">Senha *</label>
            <input type="password" id="senha" name="senha">
            <?php if (isset($erros['senha'])): ?><div class="field-error"><?= htmlspecialchars($erros['senha']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="confirmar_senha">Confirmar Senha *</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha">
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($dados['telefone']) ?>" maxlength="20" placeholder="(00) 00000-0000">
        </div>

        <div class="form-group">
            <label for="data_nascimento">Data de Nascimento</label>
            <input type="date" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($dados['data_nascimento']) ?>">
        </div>

        <div class="form-group">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($dados['endereco']) ?>" maxlength="255">
        </div>

        <div class="form-group form-check">
            <label>
                <input type="checkbox" name="pode_cadastrar_usuarios" value="1" <?= $dados['pode_cadastrar_usuarios'] ? 'checked' : '' ?>>
                Permitir que este usuário também cadastre outros usuários
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
