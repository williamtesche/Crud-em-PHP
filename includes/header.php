<?php $base = $base ?? ''; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cadastro</title>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="app-header">
            <div class="app-header-top">
                <h1><a href="<?= $base ?>home.php" class="brand-link">Sistema de Cadastro</a></h1>
                <?php if (!empty($usuario)): ?>
                    <div class="user-info">
                        <span>Olá, <strong><?= htmlspecialchars($usuario['nome']) ?></strong></span>
                        <a href="<?= $base ?>logout.php" class="btn btn-secondary btn-sm">Sair</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($usuario)): ?>
                <nav class="main-nav">
                    <a href="<?= $base ?>produtos/index.php">Loja</a>
                    <?php if ($usuario['pode_cadastrar_usuarios']): ?>
                        <a href="<?= $base ?>categorias/index.php">Categorias</a>
                        <a href="<?= $base ?>usuarios/index.php">Usuários</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </header>
