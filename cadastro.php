<?php
session_start();
require_once 'funcoes.php';
if (usuarioLogado()) {
    header("Location: artigos.php");
    exit;
}

$nomePreenchido  = $_GET['nome'] ?? '';
$emailPreenchido = $_GET['email'] ?? '';

$mensagensErro = [
    'campos'        => 'Preencha todos os campos.',
    'email_invalido'=> 'Informe um e-mail válido.',
    'senha_curta'   => 'A senha precisa ter pelo menos 6 caracteres.',
    'email_existe'  => 'Já existe uma conta com esse e-mail. Tente entrar.',
    'falha'         => 'Não foi possível concluir o cadastro. Tente novamente.',
];
$erro = $_GET['erro'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Editora de Artigos</title>
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="cadastro.css">
</head>
<body>

<div class="container">
    <form action="processar_cadastro.php" method="POST" class="form-box">
        <h2>Criar Conta</h2>
        <p class="subtitle">Publique seus próprios artigos</p>

        <?php if ($erro && isset($mensagensErro[$erro])): ?>
            <p class="erro-cadastro"><?= h($mensagensErro[$erro]) ?></p>
        <?php endif; ?>

        <input type="text" name="nome" placeholder="Nome completo" value="<?= h($nomePreenchido) ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?= h($emailPreenchido) ?>" required>
        <input type="password" name="senha" placeholder="Senha (mín. 6 caracteres)" minlength="6" required>
        <button type="submit">Cadastrar</button>

        <p class="footer-text">Já tem conta? <a href="login.html">Entrar</a></p>
    </form>
</div>

</body>
</html>
