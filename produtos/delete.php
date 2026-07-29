<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$base = '../';
exigirLogin($pdo, $base);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $pdo->prepare('SELECT imagem FROM produtos WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $produto = $stmt->fetch();

    if ($produto) {
        $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = :id');
        $stmt->execute(['id' => $id]);

        if ($produto['imagem']) {
            $caminho = __DIR__ . '/../uploads/produtos/' . $produto['imagem'];
            if (is_file($caminho)) {
                unlink($caminho);
            }
        }
    }
}

header('Location: index.php?msg=excluido');
exit;
