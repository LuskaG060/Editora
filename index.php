<?php session_start(); require_once 'funcoes.php'; $logado = usuarioLogado(); ?> <!DOCTYPE html> <html lang="pt-BR"> <head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editora de Artigos</title>

<link rel="stylesheet" href="base.css">
<link rel="stylesheet" href="index.css">
<link rel="stylesheet" href="ad.css">

<?php if ($logado): ?>
    <link rel="stylesheet" href="style.css">
<?php endif; ?>

</head>

<body<?= $logado ? ' class="tem-sidebar"' : '' ?>>

<?php if ($logado): ?>
<?php include 'sidebar.php'; ?>

<?php endif; ?> <header> <div class="container">
    <div class="logo-area">
        <a href="index.php">
<img src="EDITORA.png" alt="">
    </a>

        <h1>Editora Decolar</h1>
    </div>

    <nav>
        <a href="index.php">Início</a>
        <a href="artigos.php">Artigos</a>
        <a href="autores.php">Autores</a>

        <?php if ($logado): ?>

            <?php if (usuarioAdministrador()): ?>
                <a href="admin/index.php">Administrador</a>
            <?php endif; ?>

            <a href="logout.php">
                Sair (<?= h($_SESSION['usuario_nome']) ?>)
            </a>

        <?php else: ?>

            <a href="login.php">Login</a>

        <?php endif; ?>
    </nav>

</div>

</header> <main> <section class="hero"> <div class="hero-box">
        <h2>Publique e Descubra Ideias</h2>

        <p>
            Uma plataforma para escritores compartilharem
            conhecimento e criatividade
        </p>

        <div class="hero-acoes">
            <a href="artigos.php" class="botao">
                Explorar Artigos
            </a>

            <?php if (!$logado): ?>
                <a href="cadastro.php" class="botao botao-secundario">
                    Criar Conta
                </a>
            <?php endif; ?>
        </div>

    </div>
</section>

</main> <footer> <p>© 2026 Editora</p> </footer> <?php if ($logado): ?>
<script src="javascript.js"></script>

<?php endif; ?> </body> </html>