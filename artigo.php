<?php

// ============================================================
// SESSÃO
// ============================================================

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


// ============================================================
// ARQUIVOS
// ============================================================

require_once 'banco.php';
require_once 'funcoes.php';


// ============================================================
// CONEXÃO
// ============================================================

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Erro: conexão com o banco de dados não foi criada.");
}

if ($conn->connect_errno) {
    die(
        "Erro de conexão com MySQL: " .
        htmlspecialchars($conn->connect_error)
    );
}

$conn->set_charset("utf8mb4");


// ============================================================
// USUÁRIO
// ============================================================

$logado = usuarioLogado();

$meuId = $logado
    ? (int) ($_SESSION['usuario_id'] ?? 0)
    : 0;


// ============================================================
// PEGAR ID DO ARTIGO
// ============================================================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {
    header("Location: artigos.php");
    exit;
}


// ============================================================
// BUSCAR ARTIGO
// ============================================================

$sql = "
    SELECT
        a.idArtigos,
        a.titulo,
        a.subtitulo,
        a.Resumo,
        a.corpo_texto,
        a.Imagem_capa,
        a.data_publicacao,
        a.usuario_id,

        u.Nome AS autor_nome,

        GROUP_CONCAT(
            DISTINCT c.Nome_categorias
            ORDER BY c.Nome_categorias
            SEPARATOR ', '
        ) AS categoria

    FROM Artigos AS a

    LEFT JOIN Usuarios AS u
        ON u.idUsuarios = a.usuario_id

    LEFT JOIN Categoria AS c
        ON c.Artigos_idArtigos = a.idArtigos

    WHERE a.idArtigos = ?

    GROUP BY
        a.idArtigos,
        a.titulo,
        a.subtitulo,
        a.Resumo,
        a.corpo_texto,
        a.Imagem_capa,
        a.data_publicacao,
        a.usuario_id,
        u.Nome

    LIMIT 1
";


$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Erro ao preparar consulta SQL.");
}

$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    die("Erro ao buscar artigo.");
}

$resultado = $stmt->get_result();
$artigo    = $resultado->fetch_assoc();
$stmt->close();


// ============================================================
// ARTIGO NÃO ENCONTRADO
// ============================================================

if (!$artigo) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Artigo não encontrado</title>
        <link rel="stylesheet" href="base.css">
        <link rel="stylesheet" href="artigo.css">
        <?php if ($logado): ?><link rel="stylesheet" href="style.css"><?php endif; ?>
    </head>
    <body<?= $logado ? ' class="tem-sidebar"' : '' ?>>
        <?php if ($logado): ?><?php include 'sidebar.php'; ?><?php endif; ?>
        <main style="max-width:700px; margin:60px auto; padding:20px; text-align:center;">
            <h1>Artigo não encontrado</h1>
            <p>O artigo solicitado não existe ou foi removido.</p>
            <a href="artigos.php" class="botao">&larr; Voltar para os artigos</a>
        </main>
    </body>
    </html>
    <?php
    exit;
}


// ============================================================
// DADOS DO ARTIGO
// ============================================================

$titulo         = $artigo['titulo'] ?? 'Sem título';
$subtitulo      = $artigo['subtitulo'] ?? '';
$resumo         = $artigo['Resumo'] ?? '';
$corpo          = $artigo['corpo_texto'] ?? '';
$autor          = $artigo['autor_nome'] ?? 'Autor removido';
$categoria      = $artigo['categoria'] ?? '';
$dataPublicacao = $artigo['data_publicacao'] ?? '';
$imagemCapa     = $artigo['Imagem_capa'] ?? '';

$dono = $logado && $meuId === (int) $artigo['usuario_id'];


// ============================================================
// FORMATAÇÃO DA DATA
// ============================================================

$dataFormatada = '';

if ($dataPublicacao !== '') {
    $timestamp = strtotime($dataPublicacao);
    if ($timestamp !== false) {
        $dataFormatada = date('d/m/Y \à\s H:i', $timestamp);
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= h($titulo) ?> - Editora</title>

    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="artigo.css">

    <?php if ($logado): ?>
        <link rel="stylesheet" href="style.css">
    <?php endif; ?>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lora:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">

    <!-- CSS CUSTOMIZADO PARA FORMATAR E CENTRALIZAR O LAYOUT -->
    <style>
        .pagina-artigo-container {
            max-width: 800px !important;
            margin: 40px auto !important;
            padding: 0 20px 80px !important;
        }

        /* Card principal que dá destaque ao conteúdo no fundo bege */
        .artigo-card {
            background-color: #ffffff !important;
            border-radius: 12px !important;
            padding: 40px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        .artigo-titulo {
            font-family: 'Playfair Display', serif !important;
            font-size: 38px !important;
            line-height: 1.25 !important;
            color: #2b1d0c !important;
            margin-bottom: 15px !important;
        }

        .artigo-meta {
            color: #776b5d !important;
            font-size: 14px !important;
            margin-bottom: 30px !important;
            border-bottom: 1px solid #f0eada !important;
            padding-bottom: 15px !important;
        }

        .resumo-box {
            background: #faf6f0 !important;
            border-left: 4px solid #c59b27 !important;
            padding: 18px 22px !important;
            margin-bottom: 35px !important;
            border-radius: 4px !important;
        }

        .resumo-box strong {
            display: block !important;
            margin-bottom: 6px !important;
            color: #5c4314 !important;
            font-size: 13px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.8px !important;
        }

        .artigo-corpo {
            font-family: 'Lora', Georgia, serif !important;
            font-size: 1.15rem !important; /* ~18px */
            line-height: 1.85 !important;
            color: #2c2c2c !important;
        }

        /* Estilização dos botões inferiores */
        .acoes-container {
            margin-top: 40px !important;
            padding-top: 25px !important;
            border-top: 1px solid #eee !important;
            display: flex !important;
            gap: 12px !important;
            align-items: center !important;
        }

        .btn-voltar-estilizado {
            display: inline-block !important;
            background-color: #3d2b1f !important;
            color: #ffffff !important;
            padding: 10px 20px !important;
            border-radius: 6px !important;
            text-decoration: none !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            transition: background 0.2s !important;
        }

        .btn-voltar-estilizado:hover {
            background-color: #5c4314 !important;
        }

        .btn-excluir-estilizado {
            background-color: #dc3545 !important;
            color: #ffffff !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            transition: background 0.2s !important;
        }

        .btn-excluir-estilizado:hover {
            background-color: #bd2130 !important;
        }
    </style>
</head>


<body<?= $logado ? ' class="tem-sidebar"' : '' ?>>

<?php if ($logado): ?>
    <?php include 'sidebar.php'; ?>
<?php endif; ?>


<!-- MENU SUPERIOR -->
<header style="background:var(--primary); padding:14px 24px;">
    <nav style="max-width:1100px; margin:0 auto; display:flex; gap:20px; align-items:center;">
        <a href="index.php" style="color:var(--gold); font-family:'Playfair Display',serif; font-weight:700; text-decoration:none;">
            Editora
        </a>
        <a href="artigos.php" style="color:var(--cream); text-decoration:none; font-size:14px;">Artigos</a>
        <a href="autores.php" style="color:var(--cream); text-decoration:none; font-size:14px;">Autores</a>
        <span style="flex:1;"></span>

        <?php if ($logado): ?>
            <a href="logout.php" style="color:var(--gold-light); text-decoration:none; font-size:14px;">Sair</a>
        <?php else: ?>
            <a href="login.php" style="color:var(--gold-light); text-decoration:none; font-size:14px;">Login</a>
        <?php endif; ?>
    </nav>
</header>


<!-- CONTEÚDO CENTRAL DO ARTIGO -->
<main class="pagina-artigo-container">

    <!-- BOTÃO VOLTAR TOPO -->
    <div style="margin-bottom: 20px;">
        <a href="artigos.php" class="btn-voltar-estilizado">&larr; Voltar para artigos</a>
    </div>

    <!-- CARD DO ARTIGO -->
    <article class="artigo-card">

        <!-- CATEGORIA -->
        <?php if ($categoria !== ''): ?>
            <div style="margin-bottom:12px;">
                <span class="categoria-badge" style="background:#f0eada; color:#5c4314; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:600;">
                    <?= h($categoria) ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- TÍTULO -->
        <h1 class="artigo-titulo">
            <?= h($titulo) ?>
        </h1>

        <!-- SUBTÍTULO -->
        <?php if ($subtitulo !== ''): ?>
            <h2 style="font-family:'Lora',serif; font-size:20px; font-weight:400; color:#666; line-height:1.4; margin-bottom:20px;">
                <?= h($subtitulo) ?>
            </h2>
        <?php endif; ?>

        <!-- AUTOR / DATA -->
        <div class="artigo-meta">
            Por 
            <?php if ((int) $artigo['usuario_id'] > 0): ?>
                <a href="artigos.php?autor=<?= (int) $artigo['usuario_id'] ?>" style="color:#5c4314; font-weight:600; text-decoration:none;">
                    <?= h($autor) ?>
                </a>
            <?php else: ?>
                <strong><?= h($autor) ?></strong>
            <?php endif; ?>

            <?php if ($dataFormatada !== ''): ?>
                <span> &nbsp;•&nbsp; <?= h($dataFormatada) ?></span>
            <?php endif; ?>
        </div>

        <!-- IMAGEM DE CAPA -->
        <?php if ($imagemCapa !== ''): ?>
            <div style="margin-bottom:30px;">
                <img src="<?= h($imagemCapa) ?>" alt="<?= h($titulo) ?>" style="width:100%; max-height:420px; object-fit:cover; border-radius:8px; display:block;">
            </div>
        <?php endif; ?>

        <!-- RESUMO -->
        <?php if ($resumo !== ''): ?>
            <div class="resumo-box">
                <strong>Resumo</strong>
                <p style="margin:0; line-height:1.6; color:#444; font-size:1rem;"><?= h($resumo) ?></p>
            </div>
        <?php endif; ?>

        <!-- CORPO DO ARTIGO -->
        <div class="artigo-corpo">
            <?= nl2br(h($corpo)) ?>
        </div>

        <!-- AÇÕES DO DONO/ADMIN -->
        <?php if ($dono): ?>
            <div class="acoes-container">
                <a href="artigos.php" class="btn-voltar-estilizado">Voltar</a>

                <form method="POST" action="processar_artigo.php" onsubmit="return confirm('Excluir este artigo? Essa ação não pode ser desfeita.');" style="margin:0;">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" value="<?= (int) $artigo['idArtigos'] ?>">
                    <button type="submit" class="btn-excluir-estilizado">Excluir artigo</button>
                </form>
            </div>
        <?php endif; ?>

    </article>

</main>

<?php if ($logado): ?>
    <script src="javascript.js"></script>
<?php endif; ?>

</body>
</html>