<?php
session_start();
require_once 'banco.php';
require_once 'funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cadastro.php");
    exit;
}

$nome  = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($nome === '' || $email === '' || $senha === '') {
    header("Location: cadastro.php?erro=campos");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: cadastro.php?erro=email_invalido");
    exit;
}

if (mb_strlen($senha) < 6) {
    header("Location: cadastro.php?erro=senha_curta");
    exit;
}

// Verifica se o e-mail já existe
$stmt = $conn->prepare("SELECT idUsuarios FROM Usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: cadastro.php?erro=email_existe");
    exit;
}

$stmt->close();

// Cria o hash da senha
$hash = password_hash($senha, PASSWORD_DEFAULT);

// Insere o usuário
$stmt = $conn->prepare(
    "INSERT INTO Usuarios (Nome, email, Senha) VALUES (?, ?, ?)"
);
$stmt->bind_param("sss", $nome, $email, $hash);

if (!$stmt->execute()) {
    $stmt->close();
    header("Location: cadastro.php?erro=falha");
    exit;
}

$novoId = $stmt->insert_id;
$stmt->close();

// Cria a sessão com os dados que acabaram de ser cadastrados
$_SESSION['usuario_id']   = $novoId;
$_SESSION['usuario_nome'] = $nome;
$_SESSION['usuario_foto'] = '';
$_SESSION['usuario_tipo'] = 'usuario';

// Redireciona para a página principal
header("Location: artigos.php");
exit;
