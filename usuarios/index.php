<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$base = '../';
$usuario = exigirPermissaoUsuarios($pdo, $base);

if ($usuario['is_admin']) {
    $stmt = $pdo->query('SELECT id, nome, email, telefone, data_nascimento, endereco, pode_cadastrar_usuarios, is_admin FROM usuarios ORDER BY nome ASC');
} else {
    $stmt = $pdo->query('SELECT id, nome, email, telefone, data_nascimento, endereco, pode_cadastrar_usuarios, is_admin FROM usuarios WHERE is_admin = 0 ORDER BY nome ASC');
}
$usuarios = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?php
            $mensagens = [
                'criado' => 'Usuário cadastrado com sucesso!',
                'editado' => 'Usuário atualizado com sucesso!',
                'excluido' => 'Usuário excluído com sucesso!',
            ];
            echo htmlspecialchars($mensagens[$_GET['msg']] ?? '');
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-error">
        <?php
            $errosMsg = [
                'auto_exclusao' => 'Você não pode excluir o seu próprio usuário.',
                'ultima_permissao' => 'Não é possível remover a permissão do último usuário que pode gerenciar usuários.',
                'acesso_negado' => 'Você não tem permissão para acessar este cadastro.',
            ];
            echo htmlspecialchars($errosMsg[$_GET['erro']] ?? 'Ocorreu um erro.');
        ?>
    </div>
<?php endif; ?>

<div class="actions-bar">
    <a href="create.php" class="btn btn-success">+ Novo Usuário</a>
</div>

<?php if (count($usuarios) === 0): ?>
    <div class="card empty-state">Nenhum usuário cadastrado ainda.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>Permissão</th>
                <th class="actions-col">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['nome']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['telefone'] ?? '-') ?></td>
                    <td>
                        <?php if ($u['pode_cadastrar_usuarios']): ?>
                            <span class="badge badge-success">Pode cadastrar usuários</span>
                        <?php else: ?>
                            <span class="badge badge-muted">Usuário comum</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-col">
                        <a href="edit.php?id=<?= (int)$u['id'] ?>" class="btn btn-warning">Editar</a>
                        <?php if ((int)$u['id'] !== (int)$usuario['id']): ?>
                            <a href="delete.php?id=<?= (int)$u['id'] ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">Excluir</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
