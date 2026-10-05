<?php

mysqli_report(MYSQLI_REPORT_OFF);

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "sistema_artigos";


// ==========================================================
// CONEXÃO COM MYSQL
// ==========================================================

$conn = new mysqli($host, $usuario, $senha);

if ($conn->connect_error) {
    die("Erro de conexão com MySQL: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


// ==========================================================
// CRIA BANCO
// ==========================================================

$sql = "CREATE DATABASE IF NOT EXISTS sistema_artigos
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci";

if (!$conn->query($sql)) {
    die("Erro ao criar banco: " . $conn->error);
}


// ==========================================================
// SELECIONA BANCO
// ==========================================================

if (!$conn->select_db($banco)) {
    die("Erro ao selecionar banco: " . $conn->error);
}


// ==========================================================
// TABELA USUARIOS
// ==========================================================

$sql = "
CREATE TABLE IF NOT EXISTS Usuarios (
    idUsuarios INT UNSIGNED NOT NULL AUTO_INCREMENT,
    Nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    Senha VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    Tipo_usuario ENUM('admin','autor','leitor') NOT NULL DEFAULT 'autor',
    Data_Cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (idUsuarios),
    UNIQUE KEY uk_usuario_email (email)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
";

if (!$conn->query($sql)) {
    die("Erro ao criar tabela Usuarios: " . $conn->error);
}


// ==========================================================
// TABELA ARTIGOS
// ==========================================================

$sql = "
CREATE TABLE IF NOT EXISTS Artigos (
    idArtigos INT UNSIGNED NOT NULL AUTO_INCREMENT,
    Resumo VARCHAR(300) DEFAULT NULL,
    titulo VARCHAR(150) NOT NULL,
    subtitulo VARCHAR(150) DEFAULT NULL,
    corpo_texto MEDIUMTEXT NOT NULL,
    Imagem_capa VARCHAR(255) DEFAULT NULL,

    status ENUM(
        'pendente',
        'publicado',
        'correcao',
        'rejeitado'
    ) NOT NULL DEFAULT 'pendente',

    data_publicacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT UNSIGNED DEFAULT NULL,

    PRIMARY KEY (idArtigos),

    KEY idx_artigos_usuario (usuario_id),
    KEY idx_artigos_status (status),

    CONSTRAINT fk_artigos_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES Usuarios(idUsuarios)
        ON DELETE SET NULL
        ON UPDATE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
";

if (!$conn->query($sql)) {
    die("Erro ao criar tabela Artigos: " . $conn->error);
}
