<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

if (usuarioAtual($pdo)) {
    header('Location: home.php');
} else {
    header('Location: login.php');
}
exit;
