<?php

session_start();

require_once 'banco.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$redirect = $_POST['redirect'] ?? '';

if ($email === '' || $senha === '') {
    header("Location: login.php?erro=1");
    exit;
}

$sql = "SELECT idUsuarios, Nome, Senha, foto_perfil, Tipo_usuario
        FROM Usuarios
        WHERE email = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Erro no SQL: " . $conn->error);
}

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    die("Erro ao executar SQL: " . $stmt->error);
}

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$stmt->close();

if (!$usuario || !password_verify($senha, $usuario['Senha'])) {
    header("Location: login.php?erro=1");
    exit;
}

// Criar sessão
$_SESSION['usuario_id']   = $usuario['idUsuarios'];
$_SESSION['usuario_nome'] = $usuario['Nome'];
$_SESSION['usuario_foto'] = $usuario['foto_perfil'];
$_SESSION['usuario_tipo'] = $usuario['Tipo_usuario'];


// ==========================================
// REDIRECIONAMENTO
// ==========================================

// Administrador vai para o painel
if ($usuario['Tipo_usuario'] === 'admin') {
    header("Location: admin.php");
    exit;
}


// Outros usuários vão para artigos
if (
    $redirect !== '' &&
    preg_match('/^[a-zA-Z0-9_\-]+\.php(\?[^"\'<>]*)?$/', $redirect)
) {
    header("Location: " . $redirect);
    exit;
}

header("Location: artigos.php");
exit;

