<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once 'banco.php';
require_once 'funcoes.php';


// ============================================================
// VERIFICAR LOGIN
// ============================================================

if (!usuarioLogado()) {
    header("Location: login.php");
    exit;
}


// ============================================================
// VERIFICAR CONEXÃO
// ============================================================

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Erro: conexão com o banco de dados não foi criada.");
}

if ($conn->connect_errno) {
    die("Erro de conexão: " . htmlspecialchars($conn->connect_error));
}

$conn->set_charset("utf8mb4");


// ============================================================
// SOMENTE POST
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: artigos.php");
    exit;
}


// ============================================================
// DADOS DO USUÁRIO
// ============================================================

$usuario_id = (int) ($_SESSION['usuario_id'] ?? 0);
$tipo_usuario = strtolower(trim($_SESSION['usuario_tipo'] ?? ''));

$acao = $_POST['acao'] ?? '';


// ============================================================
// FUNÇÃO DE UPLOAD DE IMAGEM
// ============================================================

function processarUploadImagem($file)
{
    if (
        !isset($file) ||
        !is_array($file) ||
        !isset($file['error']) ||
        $file['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $extensoes = ['jpg', 'jpeg', 'png', 'webp'];

    $extensao = strtolower(
        pathinfo($file['name'], PATHINFO_EXTENSION)
    );

    if (!in_array($extensao, $extensoes, true)) {
        return false;
    }

    $pasta = 'uploads/';

    if (!is_dir($pasta)) {
        if (!mkdir($pasta, 0755, true)) {
            return false;
        }
    }

    $novoNome = uniqid('artigo_', true) . '.' . $extensao;

    $caminhoCompleto = $pasta . $novoNome;

    if (move_uploaded_file(
        $file['tmp_name'],
        $caminhoCompleto
    )) {
        return $caminhoCompleto;
    }

    return false;
}


// ============================================================
// FUNÇÃO DE CATEGORIA
// ============================================================

function salvarCategoria($conn, $artigoId, $categoria)
{
    $artigoId = (int) $artigoId;
    $categoria = trim($categoria);

    // Remove categoria atual
    if ($categoria === '') {

        $stmt = $conn->prepare(
            "DELETE FROM Artigos_Categorias
             WHERE artigo_id = ?"
        );

        if ($stmt) {
            $stmt->bind_param("i", $artigoId);
            $stmt->execute();
            $stmt->close();
        }

        return;
    }


    // Procura a categoria
    $stmt = $conn->prepare(
        "SELECT idCategoria
         FROM Categoria
         WHERE Nome_categorias = ?
         LIMIT 1"
    );

    if (!$stmt) {
        die(
            "Erro ao buscar categoria: " .
            htmlspecialchars($conn->error)
        );
    }

    $stmt->bind_param("s", $categoria);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $categoriaEncontrada = $resultado->fetch_assoc();

    $stmt->close();


    // Categoria não encontrada
    if (!$categoriaEncontrada) {
        return;
    }


    $categoriaId = (int) $categoriaEncontrada['idCategoria'];


    // Verifica se já existe relação
    $stmt = $conn->prepare(
        "SELECT artigo_id
         FROM Artigos_Categorias
         WHERE artigo_id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        die(
            "Erro ao verificar categoria: " .
            htmlspecialchars($conn->error)
        );
    }

    $stmt->bind_param("i", $artigoId);
    $stmt->execute();

    $existe = $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();


    // Atualiza categoria existente
    if ($existe) {

        $stmt = $conn->prepare(
            "UPDATE Artigos_Categorias
             SET categoria_id = ?
             WHERE artigo_id = ?"
        );

        if (!$stmt) {
            die(
                "Erro ao atualizar categoria: " .
                htmlspecialchars($conn->error)
            );
        }

        $stmt->bind_param(
            "ii",
            $categoriaId,
            $artigoId
        );

    } else {

        // Insere nova relação
        $stmt = $conn->prepare(
            "INSERT INTO Artigos_Categorias
             (artigo_id, categoria_id)
             VALUES (?, ?)"
        );

        if (!$stmt) {
            die(
                "Erro ao inserir categoria: " .
                htmlspecialchars($conn->error)
            );
        }

        $stmt->bind_param(
            "ii",
            $artigoId,
            $categoriaId
        );
    }


    if (!$stmt->execute()) {
        die(
            "Erro ao salvar categoria: " .
            htmlspecialchars($stmt->error)
        );
    }

    $stmt->close();
}


// PUBLICAR ARTIGO

if ($acao === 'publicar') {

    $titulo = trim($_POST['titulo'] ?? '');
    $resumo = trim($_POST['resumo'] ?? '');
    $corpo = trim($_POST['corpo_texto'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');


    if (
        $titulo === '' ||
        $resumo === '' ||
        $corpo === ''
    ) {
        header("Location: artigos.php?erro=campos");
        exit;
    }


    // Processa imagem
    $caminhoImagem = processarUploadImagem(
        $_FILES['imagem'] ?? null
    );


    if ($caminhoImagem === false) {
        header("Location: artigos.php?erro=imagem");
        exit;
    }


    // IMPORTANTE:
    // A coluna correta é Imagem_capa
    $stmt = $conn->prepare(
        "INSERT INTO Artigos
        (
            titulo,
            Resumo,
            corpo_texto,
            Imagem_capa,
            usuario_id,
            status
        )
        VALUES (?, ?, ?, ?, ?, 'pendente')"
    );


    if (!$stmt) {
        die(
            "Erro ao preparar artigo: " .
            htmlspecialchars($conn->error)
        );
    }


    $stmt->bind_param(
        "ssssi",
        $titulo,
        $resumo,
        $corpo,
        $caminhoImagem,
        $usuario_id
    );


    if (!$stmt->execute()) {
        die(
            "Erro ao salvar artigo: " .
            htmlspecialchars($stmt->error)
        );
    }


    $novoId = $stmt->insert_id;

    $stmt->close();


    // Salva categoria
    salvarCategoria(
        $conn,
        $novoId,
        $categoria
    );


    header("Location: artigos.php?msg=publicado");
    exit;
}



// EDITAR ARTIGO

if ($acao === 'editar') {

    $id = (int) ($_POST['id'] ?? 0);

    $titulo = trim($_POST['titulo'] ?? '');
    $resumo = trim($_POST['resumo'] ?? '');
    $corpo = trim($_POST['corpo_texto'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');


    if (
        $id <= 0 ||
        $titulo === '' ||
        $resumo === '' ||
        $corpo === ''
    ) {
        header("Location: artigos.php?erro=campos");
        exit;
    }


    // Busca proprietário do artigo
    $stmt = $conn->prepare(
        "SELECT usuario_id
         FROM Artigos
         WHERE idArtigos = ?
         LIMIT 1"
    );


    if (!$stmt) {
        die(
            "Erro ao buscar artigo: " .
            htmlspecialchars($conn->error)
        );
    }


    $stmt->bind_param("i", $id);
    $stmt->execute();

    $artigo = $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();


    if (!$artigo) {
        header("Location: artigos.php?erro=permissao");
        exit;
    }


    $dono = (int) $artigo['usuario_id'];


    // Apenas dono ou administrador pode editar
    if (
        $tipo_usuario !== 'admin' &&
        $dono !== $usuario_id
    ) {
        header("Location: artigos.php?erro=permissao");
        exit;
    }


    // Processa nova imagem
    $novaImagem = processarUploadImagem(
        $_FILES['imagem'] ?? null
    );


    if ($novaImagem === false) {
        header("Location: artigos.php?erro=imagem");
        exit;
    }


    // Se enviou nova imagem
    if ($novaImagem !== null) {

        $stmt = $conn->prepare(
            "UPDATE Artigos
             SET
                titulo = ?,
                Resumo = ?,
                corpo_texto = ?,
                Imagem_capa = ?
             WHERE idArtigos = ?"
        );


        if (!$stmt) {
            die(
                "Erro ao preparar edição: " .
                htmlspecialchars($conn->error)
            );
        }


        $stmt->bind_param(
            "ssssi",
            $titulo,
            $resumo,
            $corpo,
            $novaImagem,
            $id
        );

    } else {

        // Mantém imagem atual
        $stmt = $conn->prepare(
            "UPDATE Artigos
             SET
                titulo = ?,
                Resumo = ?,
                corpo_texto = ?
             WHERE idArtigos = ?"
        );


        if (!$stmt) {
            die(
                "Erro ao preparar edição: " .
                htmlspecialchars($conn->error)
            );
        }


        $stmt->bind_param(
            "sssi",
            $titulo,
            $resumo,
            $corpo,
            $id
        );
    }


    if (!$stmt->execute()) {
        die(
            "Erro ao editar artigo: " .
            htmlspecialchars($stmt->error)
        );
    }


    $stmt->close();


    // Atualiza categoria
    salvarCategoria(
        $conn,
        $id,
        $categoria
    );


    header("Location: artigos.php?msg=editado");
    exit;
}


// EXCLUIR ARTIGO

if ($acao === 'excluir') {

    $id = (int) ($_POST['id'] ?? 0);


    if ($id <= 0) {
        header("Location: artigos.php?erro=permissao");
        exit;
    }


    // Busca proprietário
    $stmt = $conn->prepare(
        "SELECT usuario_id
         FROM Artigos
         WHERE idArtigos = ?
         LIMIT 1"
    );


    if (!$stmt) {
        die(
            "Erro ao buscar artigo: " .
            htmlspecialchars($conn->error)
        );
    }


    $stmt->bind_param("i", $id);
    $stmt->execute();

    $artigo = $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();


    if (!$artigo) {
        header("Location: artigos.php?erro=permissao");
        exit;
    }


    $dono = (int) $artigo['usuario_id'];


    // Apenas dono ou administrador pode excluir
    if (
        $tipo_usuario !== 'admin' &&
        $dono !== $usuario_id
    ) {
        header("Location: artigos.php?erro=permissao");
        exit;
    }


    // Remove relação com categoria
    $stmt = $conn->prepare(
        "DELETE FROM Artigos_Categorias
         WHERE artigo_id = ?"
    );


    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }


    // Remove artigo
    $stmt = $conn->prepare(
        "DELETE FROM Artigos
         WHERE idArtigos = ?"
    );


    if (!$stmt) {
        die(
            "Erro ao preparar exclusão: " .
            htmlspecialchars($conn->error)
        );
    }


    $stmt->bind_param("i", $id);


    if (!$stmt->execute()) {
        die(
            "Erro ao excluir artigo: " .
            htmlspecialchars($stmt->error)
        );
    }


    $stmt->close();


    header("Location: artigos.php?msg=excluido");
    exit;
}


// APROVAR ARTIGO

if ($acao === 'aprovar') {

    if ($tipo_usuario !== 'admin') {
        header("Location: artigos.php?erro=permissao");
        exit;
    }


    $id = (int) ($_POST['id'] ?? 0);


    if ($id <= 0) {
        header("Location: artigos.php?erro=permissao");
        exit;
    }


    $stmt = $conn->prepare(
        "UPDATE Artigos
         SET
            status = 'publicado',
            data_publicacao = NOW()
         WHERE idArtigos = ?"
    );


    if (!$stmt) {
        die(
            "Erro ao preparar aprovação: " .
            htmlspecialchars($conn->error)
        );
    }


    $stmt->bind_param("i", $id);


    if (!$stmt->execute()) {
        die(
            "Erro ao aprovar artigo: " .
            htmlspecialchars($stmt->error)
        );
    }


    $stmt->close();


    header("Location: artigos.php?msg=aprovado");
    exit;
}

// REJEITAR ARTIGO

if ($acao === 'rejeitar') {

    if ($tipo_usuario !== 'admin') {
        header("Location: artigos.php?erro=permissao");
        exit;
    }


    $id = (int) ($_POST['id'] ?? 0);


    if ($id <= 0) {
        header("Location: artigos.php?erro=permissao");
        exit;
    }


    $stmt = $conn->prepare(
        "UPDATE Artigos
         SET status = 'rejeitado'
         WHERE idArtigos = ?"
    );


    if (!$stmt) {
        die(
            "Erro ao preparar rejeição: " .
            htmlspecialchars($conn->error)
        );
    }


    $stmt->bind_param("i", $id);


    if (!$stmt->execute()) {
        die(
            "Erro ao rejeitar artigo: " .
            htmlspecialchars($stmt->error)
        );
    }


    $stmt->close();


    header("Location: artigos.php?msg=rejeitado");
    exit;
}



// ENVIAR PARA CORREÇÃO

if ($acao === 'correcao') {

    if ($tipo_usuario !== 'admin') {
        header("Location: artigos.php?erro=permissao");
        exit;
    }


    $id = (int) ($_POST['id'] ?? 0);


    if ($id <= 0) {
        header("Location: artigos.php?erro=permissao");
        exit;
    }


    $stmt = $conn->prepare(
        "UPDATE Artigos
         SET status = 'correcao'
         WHERE idArtigos = ?"
    );


    if (!$stmt) {
        die(
            "Erro ao preparar correção: " .
            htmlspecialchars($conn->error)
        );
    }


    $stmt->bind_param("i", $id);


    if (!$stmt->execute()) {
        die(
            "Erro ao enviar para correção: " .
            htmlspecialchars($stmt->error)
        );
    }


    $stmt->close();


    header("Location: artigos.php?msg=correcao");
    exit;
}


// AÇÃO DESCONHECIDA

header("Location: artigos.php");
exit;
