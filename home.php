<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

$base = '';
$usuario = exigirLogin($pdo, $base);

require_once __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['erro']) && $_GET['erro'] === 'sem_permissao'): ?>
    <div class="alert alert-error">Você não tem permissão para gerenciar usuários.</div>
<?php endif; ?>

<div class="card welcome-card">
    <h2>Seja bem-vindo(a), <?= htmlspecialchars($usuario['nome']) ?>!</h2>
    <p>O que você deseja fazer?</p>
    <div class="dashboard-links">
        <a href="produtos/index.php" class="btn btn-primary">Loja de Produtos</a>
        <?php if ($usuario['pode_cadastrar_usuarios']): ?>
            <a href="categorias/index.php" class="btn btn-secondary">Gerenciar Categorias</a>
            <a href="usuarios/index.php" class="btn btn-secondary">Gerenciar Usuários</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
