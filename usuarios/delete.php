<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$base = '../';
$usuarioAtual = exigirPermissaoUsuarios($pdo, $base);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === (int)$usuarioAtual['id']) {
    header('Location: index.php?erro=auto_exclusao');
    exit;
}

if ($id) {
    $stmt = $pdo->prepare('SELECT pode_cadastrar_usuarios, is_admin FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $alvo = $stmt->fetch();

    if ($alvo) {
        if ($alvo['is_admin'] && !$usuarioAtual['is_admin']) {
            header('Location: index.php?erro=acesso_negado');
            exit;
        }

        if ((int)$alvo['pode_cadastrar_usuarios'] === 1) {
            $totalStmt = $pdo->query('SELECT COUNT(*) AS total FROM usuarios WHERE pode_cadastrar_usuarios = 1');
            $total = (int)$totalStmt->fetch()['total'];
            if ($total <= 1) {
                header('Location: index.php?erro=ultima_permissao');
                exit;
            }
        }

        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

header('Location: index.php?msg=excluido');
exit;
