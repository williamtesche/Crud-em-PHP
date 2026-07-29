<?php
// Configuração da conexão com o banco de dados MySQL (via PDO)

$host = '127.0.0.1';
$port = '3306';
$dbname = 'crud_pessoas';
$user = 'root';
$pass = ''; // Altere para a senha configurada no seu MySQL Workbench

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . htmlspecialchars($e->getMessage()));
}
