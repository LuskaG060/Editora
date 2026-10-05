<?php
session_start();
require_once 'funcoes.php';
if (usuarioLogado()) {

    if (usuarioAdministrador()) {
        header("Location: admin.php");
    } else {
        header("Location: artigos.php");
    }

    exit;
}
$redirect = $_GET['redirect'] ?? '';
$erro = isset($_GET['erro']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Editora De Artigos</title>
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="container">
    <div class="login-box">
        <h2>Entrar</h2>
        <p class="subtitle">Acesse sua conta para publicar artigos</p>

        <?php if ($erro): ?>
            <p class="erro-login">E-mail ou senha incorretos.</p>
        <?php endif; ?>

        <form action="processar_login.php" method="POST">
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <input type="hidden" name="redirect" value="<?= h($redirect) ?>">

            <button type="submit">Acessar</button>
        </form>

        <p class="footer-text">Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
    </div>
</div>

</body>
</html>
