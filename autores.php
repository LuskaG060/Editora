<?php
session_start();
require_once 'banco.php';
require_once 'funcoes.php';

$logado = usuarioLogado();

$sql = "SELECT u.idUsuarios, u.Nome, u.foto_perfil, COUNT(a.idArtigos) AS total_artigos
        FROM Usuarios u
        JOIN Artigos a ON a.usuario_id = u.idUsuarios
        GROUP BY u.idUsuarios, u.Nome, u.foto_perfil
        ORDER BY u.Nome";
$autores = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autores - Editora</title>
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="autores.css">
    <?php if ($logado): ?><link rel="stylesheet" href="style.css"><?php endif; ?>
</head>
<body<?= $logado ? ' class="tem-sidebar"' : '' ?>>

<?php if ($logado) include 'sidebar.php'; ?>

<header style="background:var(--primary); padding:14px 24px;">
    <nav style="max-width:1100px;margin:0 auto;display:flex;gap:20px;align-items:center;">
        <a href="index.php" style="color:var(--gold);font-family:Georgia,serif;font-weight:700;text-decoration:none;">Editora</a>
        <a href="artigos.php" style="color:var(--cream);text-decoration:none;font-size:14px;">Artigos</a>
        <a href="autores.php" style="color:var(--cream);text-decoration:none;font-size:14px;">Autores</a>
        <span style="flex:1;"></span>
        <?php if ($logado): ?>
            <a href="logout.php" style="color:var(--gold-light);text-decoration:none;font-size:14px;">Sair</a>
        <?php else: ?>
            <a href="login.php" style="color:var(--gold-light);text-decoration:none;font-size:14px;">Login</a>
        <?php endif; ?>
    </nav>
</header>

<main class="autores">
    <div class="container">
        <h2>Nossos Autores</h2>

        <?php if (empty($autores)): ?>
            <p style="text-align:center;color:#666;">Ainda não há autores com artigos publicados.</p>
        <?php else: ?>
        <div class="grid-autores">
            <?php foreach ($autores as $autor): ?>
                <div class="card-autor">
                    <img src="<?= fotoUsuario($autor['foto_perfil'], $autor['Nome']) ?>" alt="<?= h($autor['Nome']) ?>">
                    <h3><?= h($autor['Nome']) ?></h3>
                    <p><?= (int) $autor['total_artigos'] ?> artigo<?= $autor['total_artigos'] == 1 ? '' : 's' ?> publicado<?= $autor['total_artigos'] == 1 ? '' : 's' ?></p>
                    <a href="artigos.php?autor=<?= (int) $autor['idUsuarios'] ?>" class="botao">Ver artigos</a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php if ($logado): ?><script src="javascript.js"></script><?php endif; ?>
</body>
</html>
