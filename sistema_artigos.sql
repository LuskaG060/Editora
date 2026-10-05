-- ============================================================
-- BANCO DE DADOS: SISTEMA_ARTIGOS
-- Compatível com MySQL / MariaDB / phpMyAdmin
-- ============================================================

DROP DATABASE IF EXISTS sistema_artigos;

CREATE DATABASE sistema_artigos
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE sistema_artigos;


-- ============================================================
-- TABELA: USUARIOS
-- ============================================================

CREATE TABLE Usuarios (
    idUsuarios INT UNSIGNED NOT NULL AUTO_INCREMENT,
    Nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    Senha VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    Tipo_usuario VARCHAR(45) NOT NULL DEFAULT 'autor',
    Data_Cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (idUsuarios),
    UNIQUE KEY uk_usuario_email (email)
    
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABELA: ARTIGOS
-- ============================================================

CREATE TABLE Artigos (
    idArtigos INT UNSIGNED NOT NULL AUTO_INCREMENT,

    Resumo VARCHAR(300) DEFAULT NULL,
    titulo VARCHAR(150) NOT NULL,
    subtitulo VARCHAR(150) DEFAULT NULL,
    corpo_texto MEDIUMTEXT DEFAULT NULL,
    Imagem_capa VARCHAR(255) DEFAULT NULL,

    data_publicacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    usuario_id INT UNSIGNED DEFAULT NULL,

    PRIMARY KEY (idArtigos),

    KEY idx_artigos_usuario (usuario_id),
    KEY idx_artigos_data (data_publicacao),

    CONSTRAINT fk_artigo_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES Usuarios(idUsuarios)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABELA: COMENTARIOS
-- ============================================================

CREATE TABLE Comentarios (
    idComentarios INT UNSIGNED NOT NULL AUTO_INCREMENT,

    Comentario TEXT NOT NULL,
    Data_comentario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    usuario_id INT UNSIGNED DEFAULT NULL,
    artigo_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (idComentarios),

    KEY idx_comentarios_usuario (usuario_id),
    KEY idx_comentarios_artigo (artigo_id),

    CONSTRAINT fk_comentario_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES Usuarios(idUsuarios)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_comentario_artigo
        FOREIGN KEY (artigo_id)
        REFERENCES Artigos(idArtigos)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABELA: AVALIADORES
-- ============================================================

CREATE TABLE avaliadores (
    idavaliadores INT UNSIGNED NOT NULL AUTO_INCREMENT,

    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    especializacao VARCHAR(100) DEFAULT NULL,

    avaliar_artigo VARCHAR(45) DEFAULT NULL,
    salvar_artigo VARCHAR(45) DEFAULT NULL,

    PRIMARY KEY (idavaliadores),
    UNIQUE KEY uk_avaliador_email (email)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABELA: AVALIACOES
-- ============================================================

CREATE TABLE Avaliacoes (
    idAvaliacoes INT UNSIGNED NOT NULL AUTO_INCREMENT,

    Status_avaliacoes VARCHAR(45) DEFAULT NULL,
    Justificativa VARCHAR(255) DEFAULT NULL,
    comentarios VARCHAR(255) DEFAULT NULL,
    data_analise DATE DEFAULT NULL,

    Artigos_idArtigos INT UNSIGNED NOT NULL,
    avaliadores_idavaliadores INT UNSIGNED NOT NULL,

    PRIMARY KEY (idAvaliacoes),

    KEY idx_avaliacoes_artigo (Artigos_idArtigos),
    KEY idx_avaliacoes_avaliador (avaliadores_idavaliadores),

    CONSTRAINT fk_avaliacao_artigo
        FOREIGN KEY (Artigos_idArtigos)
        REFERENCES Artigos(idArtigos)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_avaliacao_avaliador
        FOREIGN KEY (avaliadores_idavaliadores)
        REFERENCES avaliadores(idavaliadores)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABELA: CATEGORIA
-- ============================================================

CREATE TABLE Categoria (
    idCategoria INT UNSIGNED NOT NULL AUTO_INCREMENT,

    Nome_categorias VARCHAR(45) NOT NULL,
    Descricao TEXT DEFAULT NULL,

    Artigos_idArtigos INT UNSIGNED NOT NULL,

    PRIMARY KEY (idCategoria),

    KEY idx_categoria_artigo (Artigos_idArtigos),

    CONSTRAINT fk_categoria_artigo
        FOREIGN KEY (Artigos_idArtigos)
        REFERENCES Artigos(idArtigos)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- DADOS DE TESTE
-- ============================================================


-- ============================================================
-- USUARIOS
-- ============================================================

INSERT INTO Usuarios
(
    Nome,
    email,
    Senha,
    Tipo_usuario
)
VALUES
(
    'Administrador',
    'admin@exemplo.com',
    '123456',
    'admin'
),
(
    'João da Silva',
    'joao@exemplo.com',
    '123456',
    'autor'
);


-- ============================================================
-- AVALIADOR
-- ============================================================

INSERT INTO avaliadores
(
    nome,
    email,
    senha,
    especializacao,
    avaliar_artigo,
    salvar_artigo
)
VALUES
(
    'Maria Avaliadora',
    'maria@exemplo.com',
    '123456',
    'Tecnologia',
    'sim',
    'sim'
);


-- ============================================================
-- ARTIGO
-- ============================================================

INSERT INTO Artigos
(
    Resumo,
    titulo,
    subtitulo,
    corpo_texto,
    Imagem_capa,
    usuario_id
)
VALUES
(
    'Este é um artigo de teste.',
    'Meu primeiro artigo',
    'Exemplo de subtítulo',
    'Este é o conteúdo do meu primeiro artigo.',
    'capa-artigo.jpg',
    2
);


-- ============================================================
-- COMENTARIO
-- ============================================================

INSERT INTO Comentarios
(
    Comentario,
    usuario_id,
    artigo_id
)
VALUES
(
    'Excelente artigo!',
    1,
    1
);


-- ============================================================
-- AVALIACAO
-- ============================================================

INSERT INTO Avaliacoes
(
    Status_avaliacoes,
    Justificativa,
    comentarios,
    data_analise,
    Artigos_idArtigos,
    avaliadores_idavaliadores
)
VALUES
(
    'Aprovado',
    'Artigo adequado para publicação.',
    'Conteúdo relevante.',
    CURDATE(),
    1,
    1
);


-- ============================================================
-- CATEGORIA
-- ============================================================

INSERT INTO Categoria
(
    Nome_categorias,
    Descricao,
    Artigos_idArtigos
)
VALUES
(
    'Tecnologia',
    'Artigos relacionados à tecnologia.',
    1
);


-- ============================================================
-- TESTES
-- ============================================================

SELECT * FROM Usuarios;

SELECT * FROM Artigos;

SELECT * FROM Comentarios;

SELECT * FROM avaliadores;

SELECT * FROM Avaliacoes;

SELECT * FROM Categoria;


-- ============================================================
-- TESTE COM RELACIONAMENTOS
-- ============================================================

SELECT
    a.idArtigos,
    a.titulo,
    a.subtitulo,
    a.Resumo,
    u.Nome AS autor,
    u.email AS email_autor
FROM Artigos a
LEFT JOIN Usuarios u
    ON a.usuario_id = u.idUsuarios;


-- ============================================================
-- TESTAR COMENTARIOS
-- ============================================================

SELECT
    c.idComentarios,
    c.Comentario,
    c.Data_comentario,
    u.Nome AS usuario,
    a.titulo AS artigo
FROM Comentarios c
LEFT JOIN Usuarios u
    ON c.usuario_id = u.idUsuarios
INNER JOIN Artigos a
    ON c.artigo_id = a.idArtigos;


-- ============================================================
-- TESTAR AVALIACOES
-- ============================================================

SELECT
    av.idAvaliacoes,
    av.Status_avaliacoes,
    av.Justificativa,
    av.comentarios,
    av.data_analise,
    a.titulo AS artigo,
    v.nome AS avaliador,
    v.especializacao
FROM Avaliacoes av
INNER JOIN Artigos a
    ON av.Artigos_idArtigos = a.idArtigos
INNER JOIN avaliadores v
    ON av.avaliadores_idavaliadores = v.idavaliadores;


-- ============================================================
-- TESTAR CATEGORIAS
-- ============================================================

SELECT
    c.idCategoria,
    c.Nome_categorias,
    c.Descricao,
    a.titulo AS artigo
FROM Categoria c
INNER JOIN Artigos a
    ON c.Artigos_idArtigos = a.idArtigos;