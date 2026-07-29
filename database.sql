-- Script de criação do banco de dados e tabelas
-- Execute este script no MySQL Workbench

CREATE DATABASE IF NOT EXISTS crud_pessoas CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE crud_pessoas;

DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS pessoas;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    data_nascimento DATE,
    endereco VARCHAR(255),
    pode_cadastrar_usuarios TINYINT(1) NOT NULL DEFAULT 0,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    imagem VARCHAR(255),
    preco DECIMAL(10,2) NOT NULL,
    localizacao VARCHAR(255),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    categoria_id INT,
    usuario_id INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Usuário administrador padrão
-- Login: administrador@gmail.com
-- Senha: admin
INSERT INTO usuarios (nome, email, senha, pode_cadastrar_usuarios, is_admin) VALUES
('Administrador', 'administrador@gmail.com', '$2y$10$edOb.pQI.kixT74sWsOhlO2M9PpqzBQa.FDfytZd1vhPj00uVP4QO', 1, 1);
