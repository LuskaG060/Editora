<?php

session_start();

require_once 'banco.php';
require_once 'funcoes.php';


// ============================================================
// VERIFICAR CONEXÃO
// ============================================================
if (isset($_POST['aprovar'])) {

    $id = (int) $_POST['aprovar'];

    $stmt = $conn->prepare("
        UPDATE Artigos
        SET status = 'publicado'
        WHERE idArtigos = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: artigos.php");
    exit;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Erro: conexão com o banco de dados não foi criada.");
}

if ($conn->connect_errno) {
    die(
        "Erro de conexão com MySQL: " .
        htmlspecialchars($conn->connect_error)
    );
}


// ============================================================
// USUÁRIO
// ============================================================

$logado = usuarioLogado();

$meuId = $logado
    ? (int) ($_SESSION['usuario_id'] ?? 0)
    : 0;


// ============================================================
// FILTRO POR AUTOR
// ============================================================

$autorFiltro = isset($_GET['autor'])
    ? (int) $_GET['autor']
    : 0;

$nomeAutorFiltro = null;


// ============================================================
// BUSCAR NOME DO AUTOR
// ============================================================

if ($autorFiltro > 0) {

    $autorId = (int) $autorFiltro;

    $sqlAutor = "
        SELECT Nome
        FROM Usuarios
        WHERE idUsuarios = {$autorId}
        LIMIT 1
    ";

    $resultadoAutor = $conn->query($sqlAutor);

    if ($resultadoAutor === false) {

        die(
            "<h2>Erro ao buscar autor</h2>" .
            "<p><strong>MySQL:</strong> " .
            htmlspecialchars($conn->error) .
            "</p>" .
            "<pre>" .
            htmlspecialchars($sqlAutor) .
            "</pre>"
        );
    }

    $row = $resultadoAutor->fetch_assoc();

    if ($row) {
        $nomeAutorFiltro = $row['Nome'];
    }
}


// ============================================================
// BUSCAR ARTIGOS
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
";


// ============================================================
// FILTRAR POR AUTOR
// ============================================================

if ($autorFiltro > 0) {

    $sql .= "
        WHERE a.usuario_id = ?
    ";
}


// ============================================================
// AGRUPAMENTO
// ============================================================

$sql .= "
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

    ORDER BY a.data_publicacao DESC
";


// ============================================================
// PREPARAR CONSULTA
// ============================================================

$stmt = $conn->prepare($sql);

if ($stmt === false) {

    die(
        "<h2>Erro ao preparar consulta dos artigos</h2>" .

        "<p><strong>MySQL:</strong><br>" .
        htmlspecialchars($conn->error) .
        "</p>" .

        "<p><strong>Consulta:</strong></p>" .

        "<pre>" .
        htmlspecialchars($sql) .
        "</pre>"
    );
}


// ============================================================
// BIND DO AUTOR
// ============================================================

if ($autorFiltro > 0) {

    if (!$stmt->bind_param("i", $autorFiltro)) {

        die(
            "Erro no bind_param: " .
            htmlspecialchars($stmt->error)
        );
    }
}


// ============================================================
// EXECUTAR
// ============================================================

if (!$stmt->execute()) {

    die(
        "<h2>Erro ao executar consulta</h2>" .

        "<p><strong>MySQL:</strong><br>" .
        htmlspecialchars($stmt->error) .
        "</p>"
    );
}


// ============================================================
// RESULTADO
// ============================================================

$resultado = $stmt->get_result();

if ($resultado === false) {

    die(
        "Erro ao obter resultado: " .
        htmlspecialchars($stmt->error)
    );
}


$artigos = $resultado->fetch_all(MYSQLI_ASSOC);

$stmt->close();


// ============================================================
// MENSAGENS
// ============================================================

$mensagensInfo = [

    'bem_vindo'
        => 'Conta criada com sucesso! Bem-vindo(a).',

    'publicado'
        => 'Artigo publicado com sucesso!',

    'editado'
        => 'Artigo atualizado com sucesso!',

    'excluido'
        => 'Artigo excluído com sucesso.'
];


$mensagensErro = [

    'campos'
        => 'Preencha todos os campos obrigatórios.',

    'permissao'
        => 'Você só pode editar ou excluir os próprios artigos.',

    'banco'
        => 'Ocorreu um erro ao acessar o banco de dados.'
];

?>
<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Artigos - Editora</title>

    <link
        rel="stylesheet"
        href="base.css"
    >

    <link
        rel="stylesheet"
        href="artigo.css"
    >

    <?php if ($logado): ?>

        <link
            rel="stylesheet"
            href="style.css"
        >

    <?php endif; ?>


    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lora:ital,wght@0,400;0,500;1,400&display=swap"
        rel="stylesheet"
    >

</head>


<body<?= $logado ? ' class="tem-sidebar"' : '' ?>>


<?php if ($logado): ?>

    <?php include 'sidebar.php'; ?>

<?php endif; ?>


<!-- ============================================================
     MENU
============================================================ -->

<header
    style="
        background:var(--primary);
        padding:14px 24px;
    "
>

    <nav
        style="
            max-width:1100px;
            margin:0 auto;
            display:flex;
            gap:20px;
            align-items:center;
        "
    >

        <a
            href="index.php"
            style="
                color:var(--gold);
                font-family:'Playfair Display',serif;
                font-weight:700;
                text-decoration:none;
            "
        >
            Editora
        </a>


        <a
            href="artigos.php"
            style="
                color:var(--cream);
                text-decoration:none;
                font-size:14px;
            "
        >
            Artigos
        </a>


        <a
            href="autores.php"
            style="
                color:var(--cream);
                text-decoration:none;
                font-size:14px;
            "
        >
            Autores
        </a>


        <span style="flex:1;"></span>


        <?php if ($logado): ?>

            <a
                href="logout.php"
                style="
                    color:var(--gold-light);
                    text-decoration:none;
                    font-size:14px;
                "
            >
                Sair
            </a>

        <?php else: ?>

            <a
                href="login.php"
                style="
                    color:var(--gold-light);
                    text-decoration:none;
                    font-size:14px;
                "
            >
                Login
            </a>

        <?php endif; ?>

    </nav>

</header>


<!-- ============================================================
     ARTIGOS
============================================================ -->

<main class="artigos">

    <div class="container">

        <div class="secao-header">

            <h2>

                <?php if ($nomeAutorFiltro): ?>

                    <?= h('Artigos de ' . $nomeAutorFiltro) ?>

                <?php else: ?>

                    Artigos Recentes

                <?php endif; ?>

            </h2>


            <?php if ($autorFiltro > 0): ?>

                <a
                    href="artigos.php"
                    class="botao"
                >
                    &larr; Ver todos os artigos
                </a>

            <?php endif; ?>


            <?php if ($logado): ?>

                <button
                    type="button"
                    class="btn-publicar"
                    onclick="abrirModalPublicar()"
                >

                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>

                    Publicar artigo

                </button>

            <?php elseif (!$autorFiltro): ?>

                <a
                    href="login.php?redirect=artigos.php"
                    class="btn-publicar"
                >
                    Faça login para publicar
                </a>

            <?php endif; ?>

        </div>


        <!-- ========================================================
             LISTA DE ARTIGOS
        ========================================================= -->

        <div
            class="grid-artigos"
            id="grid-artigos"
        >

            <?php if (empty($artigos)): ?>

                <p style="color:var(--text-muted);">

                    Nenhum artigo publicado ainda

                    <?php if ($autorFiltro > 0): ?>

                        por este autor

                    <?php endif; ?>.

                </p>

            <?php endif; ?>


            <?php foreach ($artigos as $artigo): ?>

                <?php

                $dono =
                    $logado &&
                    $meuId === (int) $artigo['usuario_id'];

                $categoriaArtigo =
                    $artigo['categoria'] ?? '';

                ?>


                <div
                    class="card-artigo"

                    data-id="<?= (int) $artigo['idArtigos'] ?>"

                    data-titulo="<?= h($artigo['titulo']) ?>"

                    data-categoria="<?= h($categoriaArtigo) ?>"

                    data-resumo="<?= h($artigo['Resumo'] ?? '') ?>"

                    data-corpo="<?= h($artigo['corpo_texto'] ?? '') ?>"
                >

                    <?php if (!empty($artigo['Imagem_capa'])): ?>
                        <div class="capa-artigo" style="margin-bottom: 12px; overflow: hidden; border-radius: 8px;">
                            <img 
                                src="<?= h($artigo['Imagem_capa']) ?>" 
                                alt="<?= h($artigo['titulo']) ?>" 
                                style="width: 100%; max-height: 200px; object-fit: cover; display: block;"
                            >
                        </div>
                    <?php endif; ?>

                    <?php if ($categoriaArtigo !== ''): ?>

                        <span class="categoria-badge">
                            <?= h($categoriaArtigo) ?>
                        </span>

                    <?php endif; ?>


                    <h3>
                        <?= h($artigo['titulo']) ?>
                    </h3>


                    <p class="autor">

                        Por

                        <a
                            href="artigos.php?autor=<?= (int) $artigo['usuario_id'] ?>"
                            style="color:inherit;"
                        >
                            <?= h($artigo['autor_nome'] ?? 'Autor removido') ?>
                        </a>

                    </p>


                    <p class="resumo">

                        <?= h($artigo['Resumo'] ?? '') ?>

                    </p>


                    <a
                        href="artigo.php?id=<?= (int) $artigo['idArtigos'] ?>"
                        class="botao"
                    >
                        Ler mais
                    </a>


                    <?php if ($dono): ?>

                        <div class="acoes">

                            <button
                                type="button"
                                class="btn-editar"
                                onclick="abrirEdicao(this)"
                            >
                                Editar
                            </button>


                            <form
                                method="POST"
                                action="processar_artigo.php"
                                onsubmit="return confirm('Excluir este artigo? Essa ação não pode ser desfeita.');"
                                style="display:contents;"
                            >

                                <input
                                    type="hidden"
                                    name="acao"
                                    value="excluir"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $artigo['idArtigos'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn-excluir"
                                >
                                    Excluir
                                </button>

                            </form>

                        </div>

                    <?php endif; ?>


                </div>

            <?php endforeach; ?>

        </div>

    </div>

</main>


<!-- ============================================================
     MODAL
============================================================ -->

<?php if ($logado): ?>

<div
    class="modal-overlay"
    id="modal-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-titulo"
>

    <div class="modal">


        <div class="modal-topo">

            <h2 id="modal-titulo">
                Novo Artigo
            </h2>


            <button
                type="button"
                class="modal-fechar"
                onclick="fecharModal()"
                aria-label="Fechar"
            >
                &times;
            </button>

        </div>


        <form
            class="modal-corpo"
            id="form-artigo"
            action="processar_artigo.php"
            method="POST"
            enctype="multipart/form-data"
        >

            <input
                type="hidden"
                name="acao"
                id="campo-acao"
                value="publicar"
            >


            <input
                type="hidden"
                name="id"
                id="campo-id"
                value=""
            >


            <p
                style="
                    font-size:13px;
                    color:var(--text-muted);
                    margin-bottom:16px;
                "
            >

                Publicando como

                <strong style="color:var(--primary);">

                    <?= h($_SESSION['usuario_nome'] ?? 'Usuário') ?>

                </strong>

            </p>


            <!-- TÍTULO -->

            <div
                class="campo"
                id="campo-titulo"
            >

                <label for="inp-titulo">

                    Título

                    <span class="obrigatorio">
                        *
                    </span>

                </label>


                <input
                    type="text"
                    id="inp-titulo"
                    name="titulo"
                    placeholder="Escreva um título chamativo..."
                    maxlength="150"
                    required
                    oninput="contarCaracteres('inp-titulo','cnt-titulo',150)"
                >


                <div class="cont">

                    <span id="cnt-titulo">
                        0
                    </span>

                    /150

                </div>

            </div>


            <!-- CATEGORIA -->

            <div class="campo">

                <label for="inp-categoria">
                    Categoria
                </label>


                <select
                    id="inp-categoria"
                    name="categoria"
                >

                    <option value="">
                        Selecione...
                    </option>

                    <option value="Tecnologia">
                        Tecnologia
                    </option>

                    <option value="Ciência">
                        Ciência
                    </option>

                    <option value="Educação">
                        Educação
                    </option>

                    <option value="Esportes">
                        Esportes
                    </option>

                    <option value="Cultura">
                        Cultura
                    </option>

                </select>

            </div>


            <!-- IMAGEM DO ARTIGO -->

            <div class="campo">

                <label for="inp-imagem">
                    Imagem de Capa (opcional)
                </label>

                <input
                    type="file"
                    id="inp-imagem"
                    name="imagem"
                    accept="image/png, image/jpeg, image/webp"
                >

            </div>


            <!-- RESUMO -->

            <div
                class="campo"
                id="campo-resumo"
            >

                <label for="inp-resumo">

                    Resumo

                    <span class="obrigatorio">
                        *
                    </span>

                </label>


                <textarea
                    id="inp-resumo"
                    name="resumo"
                    placeholder="Um breve resumo para atrair o leitor..."
                    maxlength="300"
                    required
                    oninput="contarCaracteres('inp-resumo','cnt-resumo',300)"
                ></textarea>


                <div class="cont">

                    <span id="cnt-resumo">
                        0
                    </span>

                    /300

                </div>

            </div>


            <!-- CORPO -->

            <div
                class="campo"
                id="campo-corpo"
            >

                <label for="inp-corpo">

                    Texto completo do artigo

                    <span class="obrigatorio">
                        *
                    </span>

                </label>


                <textarea
                    id="inp-corpo"
                    name="corpo_texto"
                    placeholder="Escreva o artigo completo aqui..."
                    required
                    style="min-height:200px;"
                ></textarea>

            </div>


            <!-- RODAPÉ -->

            <div class="modal-rodape">

                <button
                    type="button"
                    class="btn-cancelar"
                    onclick="fecharModal()"
                >
                    Cancelar
                </button>


                <button
                    type="submit"
                    class="btn-enviar"
                    id="btn-enviar-form"
                >
                    Publicar &rarr;
                </button>

            </div>

        </form>

    </div>

</div>

<?php endif; ?>


<!-- ============================================================
     TOAST
============================================================ -->

<div
    class="toast"
    id="toast"
></div>


<!-- ============================================================
     JAVASCRIPT
============================================================ -->

<script>

function abrirModalPublicar() {

    const acao = document.getElementById('campo-acao');
    const id = document.getElementById('campo-id');
    const titulo = document.getElementById('modal-titulo');
    const botao = document.getElementById('btn-enviar-form');
    const formulario = document.getElementById('form-artigo');

    if (!formulario) {
        return;
    }

    formulario.reset();

    acao.value = 'publicar';
    id.value = '';

    titulo.textContent = 'Novo Artigo';
    botao.textContent = 'Publicar →';

    document.getElementById('cnt-titulo').textContent = '0';
    document.getElementById('cnt-resumo').textContent = '0';

    abrirModal();
}


function abrirEdicao(botao) {

    const card = botao.closest('.card-artigo');

    if (!card) {
        return;
    }

    document.getElementById('campo-acao').value = 'editar';

    document.getElementById('campo-id').value =
        card.dataset.id || '';

    document.getElementById('inp-titulo').value =
        card.dataset.titulo || '';

    document.getElementById('inp-categoria').value =
        card.dataset.categoria || '';

    document.getElementById('inp-resumo').value =
        card.dataset.resumo || '';

    document.getElementById('inp-corpo').value =
        card.dataset.corpo || '';

    document.getElementById('cnt-titulo').textContent =
        (card.dataset.titulo || '').length;

    document.getElementById('cnt-resumo').textContent =
        (card.dataset.resumo || '').length;

    document.getElementById('modal-titulo').textContent =
        'Editar Artigo';

    document.getElementById('btn-enviar-form').textContent =
        'Salvar alterações';

    abrirModal();
}


function abrirModal() {

    const overlay =
        document.getElementById('modal-overlay');

    if (!overlay) {
        return;
    }

    overlay.classList.add('ativo');

    const titulo =
        document.getElementById('inp-titulo');

    if (titulo) {
        titulo.focus();
    }
}


function fecharModal() {

    const overlay =
        document.getElementById('modal-overlay');

    if (!overlay) {
        return;
    }

    overlay.classList.remove('ativo');
}


const overlay =
    document.getElementById('modal-overlay');


if (overlay) {

    overlay.addEventListener('click', function (e) {

        if (e.target === this) {
            fecharModal();
        }

    });

}


document.addEventListener('keydown', function (e) {

    if (e.key === 'Escape') {
        fecharModal();
    }

});


function contarCaracteres(idCampo, idContador, max) {

    const campo =
        document.getElementById(idCampo);

    const contador =
        document.getElementById(idContador);

    if (!campo || !contador) {
        return;
    }

    contador.textContent =
        campo.value.length;

}


function mostrarToast(texto) {

    const toast =
        document.getElementById('toast');

    if (!toast) {
        return;
    }

    toast.textContent = texto;

    toast.classList.add('visivel');

    setTimeout(function () {

        toast.classList.remove('visivel');

    }, 3000);

}


// ============================================================
// MENSAGENS DO BACKEND
// ============================================================

(function () {

    const params =
        new URLSearchParams(window.location.search);

    const mensagensInfo =
        <?= json_encode(
            $mensagensInfo,
            JSON_UNESCAPED_UNICODE
        ) ?>;

    const mensagensErro =
        <?= json_encode(
            $mensagensErro,
            JSON_UNESCAPED_UNICODE
        ) ?>;


    const mensagem =
        params.get('msg');

    const erro =
        params.get('erro');


    if (
        mensagem &&
        mensagensInfo[mensagem]
    ) {

        mostrarToast(
            '✓ ' + mensagensInfo[mensagem]
        );

    }
    else if (
        erro &&
        mensagensErro[erro]
    ) {

        mostrarToast(
            '⚠ ' + mensagensErro[erro]
        );

    }


    // Guarda a informação de publicar antes
    // de remover os parâmetros da URL.
    const abrirPublicar =
        params.get('publicar') === '1';


    if (
        mensagem ||
        erro
    ) {

        params.delete('msg');
        params.delete('erro');

        const query =
            params.toString();

        const novaUrl =
            window.location.pathname +
            (query ? '?' + query : '');

        window.history.replaceState(
            {},
            '',
            novaUrl
        );

    }


    if (
        abrirPublicar &&
        document.getElementById('modal-overlay')
    ) {

        abrirModalPublicar();

    }

})();

</script>


<?php if ($logado): ?>

<script src="javascript.js"></script>

<?php endif; ?>


</body>

</html>