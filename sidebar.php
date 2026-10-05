<?php
/**
 * Painel lateral (sidebar) - exibido APENAS para usuário logado.
 * Inclua com:  if (usuarioLogado()) include 'sidebar.php';
 * A página que incluir precisa ter <body class="tem-sidebar"> e
 * carregar style.css + javascript.js.
 */
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$fotoUsuario = fotoUsuario($_SESSION['usuario_foto'] ?? null, $nomeUsuario);
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<button id="toggleSidebar">&#10094;</button>

<div class="sidebar" id="sidebar">
    <div class="perfil">
        <img src="<?= $fotoUsuario ?>" alt="Foto de <?= h($nomeUsuario) ?>">
        <h3><?= h($nomeUsuario) ?></h3>
        <span>Autor</span>
    </div>

    <nav class="menu-lateral">
        <a href="index.php" class="<?= $paginaAtual === 'index.php' ? 'ativo' : '' ?>">&#127968; Início</a>
        <a href="artigos.php?autor=<?= (int) $_SESSION['usuario_id'] ?>">&#128218; Meus Artigos</a>
        <a href="artigos.php?publicar=1">&#128221; Publicar Artigo</a>
        <a href="autores.php" class="<?= $paginaAtual === 'autores.php' ? 'ativo' : '' ?>">&#128101; Autores</a>
    </nav>

    <div class="rodape">
        <button onclick="window.location.href='logout.php'">Sair</button>
    </div>
</div>
