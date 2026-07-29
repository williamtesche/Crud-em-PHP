<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuarioAtual(PDO $pdo): ?array
{
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, nome, email, pode_cadastrar_usuarios, is_admin FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();

    return $usuario ?: null;
}

function exigirLogin(PDO $pdo, string $base = ''): array
{
    $usuario = usuarioAtual($pdo);

    if (!$usuario) {
        header('Location: ' . $base . 'login.php');
        exit;
    }

    return $usuario;
}

function exigirPermissaoUsuarios(PDO $pdo, string $base = ''): array
{
    $usuario = exigirLogin($pdo, $base);

    if (!$usuario['pode_cadastrar_usuarios']) {
        header('Location: ' . $base . 'home.php?erro=sem_permissao');
        exit;
    }

    return $usuario;
}
