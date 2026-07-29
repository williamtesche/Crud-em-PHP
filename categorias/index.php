<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$base = '../';
$usuario = exigirPermissaoUsuarios($pdo, $base);

$stmt = $pdo->query(
    'SELECT c.id, c.nome, COUNT(p.id) AS total_produtos
     FROM categorias c
     LEFT JOIN produtos p ON p.categoria_id = c.id
     GROUP BY c.id, c.nome
     ORDER BY c.nome ASC'
);
$categorias = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?php
            $mensagens = [
                'criado' => 'Categoria cadastrada com sucesso!',
                'editado' => 'Categoria atualizada com sucesso!',
                'excluido' => 'Categoria excluída com sucesso!',
            ];
            echo htmlspecialchars($mensagens[$_GET['msg']] ?? '');
        ?>
    </div>
<?php endif; ?>

<div class="actions-bar">
    <a href="create.php" class="btn btn-success">+ Nova Categoria</a>
</div>

<?php if (count($categorias) === 0): ?>
    <div class="card empty-state">Nenhuma categoria cadastrada ainda.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Produtos</th>
                <th class="actions-col">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $categoria): ?>
                <tr>
                    <td><?= htmlspecialchars($categoria['nome']) ?></td>
                    <td><?= (int)$categoria['total_produtos'] ?></td>
                    <td class="actions-col">
                        <a href="edit.php?id=<?= (int)$categoria['id'] ?>" class="btn btn-warning">Editar</a>
                        <a href="delete.php?id=<?= (int)$categoria['id'] ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta categoria? Os produtos vinculados ficarão sem categoria.');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
