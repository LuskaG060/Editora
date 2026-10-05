<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "funcoes.php";
require_once "banco.php";

if (!usuarioLogado()) {
    header("Location: login.php");
    exit;
}

if (!usuarioAdministrador()) {
    http_response_code(403);
    die("Acesso negado. Apenas administradores podem acessar esta área.");
}

// ==========================================================
// DADOS DO BANCO
// ==========================================================

$artigos_pendentes       = 0;
$artigos_publicados      = 0;
$comentarios_denunciados = 0;
$usuarios_cadastrados    = 0;

// Artigos Pendentes
$sql = "SELECT COUNT(*) AS total FROM Artigos WHERE status = 'pendente'";
$resultado = $conn->query($sql);
if ($resultado) {
    $linha = $resultado->fetch_assoc();
    $artigos_pendentes = (int) $linha['total'];
}

// Artigos Publicados
$sql = "SELECT COUNT(*) AS total FROM Artigos WHERE status = 'publicado'";
$resultado = $conn->query($sql);
if ($resultado) {
    $linha = $resultado->fetch_assoc();
    $artigos_publicados = (int) $linha['total'];
}

// Usuários Cadastrados
$sql = "SELECT COUNT(*) AS total FROM Usuarios";
$resultado = $conn->query($sql);
if ($resultado) {
    $linha = $resultado->fetch_assoc();
    $usuarios_cadastrados = (int) $linha['total'];
}

$atividades = [
    [
        "tipo"      => "aprovado",
        "icone"     => "✓",
        "titulo"    => "Artigo aprovado",
        "descricao" => 'O artigo "A importância da leitura" foi aprovado.',
        "tempo"     => "Há 15 minutos"
    ],
    [
        "tipo"      => "denuncia",
        "icone"     => "!",
        "titulo"    => "Comentário denunciado",
        "descricao" => "Um comentário foi denunciado por um usuário.",
        "tempo"     => "Há 42 minutos"
    ],
    [
        "tipo"      => "correcao",
        "icone"     => "✎",
        "titulo"    => "Artigo enviado para correção",
        "descricao" => "O autor recebeu uma solicitação de alterações.",
        "tempo"     => "Há 1 hora"
    ]
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador | Editora</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<aside class="sidebar">
    <div class="logo">
        <h1>Editora</h1>
        <span>ADMINISTRADOR</span>
    </div>

    <nav class="menu">
        <a href="index.php" class="menu-item ativo">
            <span class="menu-icon">⌂</span>
            <span>Dashboard</span>
        </a>
        <a href="artigos.php" class="menu-item">
            <span class="menu-icon">▤</span>
            <span>Artigos</span>
        </a>
        <a href="comentarios.php" class="menu-item">
            <span class="menu-icon">◌</span>
            <span>Comentários</span>
        </a>
        <a href="usuarios.php" class="menu-item">
            <span class="menu-icon">♙</span>
            <span>Usuários</span>
        </a>
        <a href="denuncias.php" class="menu-item">
            <span class="menu-icon">!</span>
            <span>Denúncias</span>
        </a>
        <a href="categorias.php" class="menu-item">
            <span class="menu-icon">◇</span>
            <span>Categorias</span>
        </a>
        <a href="historico.php" class="menu-item">
            <span class="menu-icon">◷</span>
            <span>Histórico</span>
        </a>
    </nav>

    <div class="sidebar-bottom">
        <a href="../index.php" class="voltar-site">← Voltar ao site</a>
    </div>
</aside>

<main class="main">
    <header class="topbar">
        <div class="titulo">
            <span class="pequeno-titulo">ADMINISTRAÇÃO</span>
            <h2>Dashboard</h2>
            <p>Visão geral da plataforma.</p>
        </div>

        <div class="perfil-admin">
            <div class="avatar">A</div>
            <div class="perfil-info">
                <strong>Administrador</strong>
                <span>Administrador</span>
            </div>
            <button class="perfil-botao">⋮</button>
        </div>
    </header>

    <section class="cards">
        <div class="card">
            <div class="card-topo">
                <div class="card-icone azul">▤</div>
                <span class="card-status">Atenção</span>
            </div>
            <div class="card-conteudo">
                <span>Artigos pendentes</span>
                <strong><?php echo $artigos_pendentes; ?></strong>
            </div>
            <a href="artigos.php" class="card-link">Avaliar artigos →</a>
        </div>

        <div class="card">
            <div class="card-topo">
                <div class="card-icone verde">✓</div>
                <span class="card-status">Publicados</span>
            </div>
            <div class="card-conteudo">
                <span>Artigos publicados</span>
                <strong><?php echo $artigos_publicados; ?></strong>
            </div>
            <a href="artigos.php" class="card-link">Gerenciar artigos →</a>
        </div>

        <div class="card">
            <div class="card-topo">
                <div class="card-icone amarelo">!</div>
                <span class="card-status">Moderar</span>
            </div>
            <div class="card-conteudo">
                <span>Comentários denunciados</span>
                <strong><?php echo $comentarios_denunciados; ?></strong>
            </div>
            <a href="comentarios.php" class="card-link">Ver denúncias →</a>
        </div>

        <div class="card">
            <div class="card-topo">
                <div class="card-icone roxo">♙</div>
                <span class="card-status">Comunidade</span>
            </div>
            <div class="card-conteudo">
                <span>Usuários cadastrados</span>
                <strong><?php echo $usuarios_cadastrados; ?></strong>
            </div>
            <a href="usuarios.php" class="card-link">Gerenciar usuários →</a>
        </div>
    </section>

    <section class="dashboard-grid">
        <div class="painel atividades">
            <div class="painel-header">
                <div>
                    <h3>Atividades recentes</h3>
                    <p>Últimas ações realizadas.</p>
                </div>
                <a href="historico.php">Ver histórico</a>
            </div>

            <div class="lista-atividades">
                <?php foreach ($atividades as $atividade): ?>
                    <div class="atividade">
                        <div class="atividade-icone <?php echo $atividade["tipo"]; ?>">
                            <?php echo $atividade["icone"]; ?>
                        </div>
                        <div class="atividade-texto">
                            <strong><?php echo $atividade["titulo"]; ?></strong>
                            <p><?php echo $atividade["descricao"]; ?></p>
                            <span><?php echo $atividade["tempo"]; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="painel acoes">
            <div class="painel-header">
                <div>
                    <h3>Precisa da sua atenção</h3>
                    <p>Tarefas pendentes.</p>
                </div>
            </div>

            <div class="acao">
                <div class="acao-icone">▤</div>
                <div class="acao-info">
                    <strong><?php echo $artigos_pendentes; ?> artigos aguardando avaliação</strong>
                    <span>Revise os artigos enviados pelos autores.</span>
                </div>
                <a href="artigos.php">→</a>
            </div>

            <div class="acao">
                <div class="acao-icone alerta">!</div>
                <div class="acao-info">
                    <strong><?php echo $comentarios_denunciados; ?> comentários denunciados</strong>
                    <span>Verifique as denúncias da comunidade.</span>
                </div>
                <a href="comentarios.php">→</a>
            </div>

            <div class="acao">
                <div class="acao-icone denuncia">⚠</div>
                <div class="acao-info">
                    <strong>Denúncias para analisar</strong>
                    <span>Acesse a central de denúncias.</span>
                </div>
                <a href="denuncias.php">→</a>
            </div>
        </div>
    </section>

    <footer class="footer">
        <span>Editora — Painel Administrativo</span>
        <span>Sistema de gerenciamento de conteúdo</span>
    </footer>
</main>

</body>
</html>