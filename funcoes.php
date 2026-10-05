<?php
/**
 * Funções utilitárias usadas em várias páginas.
 */

function h($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function usuarioLogado() {
    return isset($_SESSION['usuario_id']);
}

function usuarioAdministrador() {
    return isset($_SESSION['usuario_tipo']) &&
           $_SESSION['usuario_tipo'] === 'admin';
}

function exigirLogin($destinoAposLogin = null) {
    if (!usuarioLogado()) {
        $destino = $destinoAposLogin ?? $_SERVER['REQUEST_URI'];
        header("Location: login.php?redirect=" . urlencode($destino));
        exit;
    }
}

function avatarIniciais($nome) {
    $nome = trim((string)$nome);
    $partes = preg_split('/\s+/', $nome);
    $iniciais = '';

    if (count($partes) >= 1 && $partes[0] !== '') {
        $iniciais .= mb_substr($partes[0], 0, 1);
    }

    if (count($partes) > 1) {
        $iniciais .= mb_substr(end($partes), 0, 1);
    }

    $iniciais = mb_strtoupper($iniciais ?: '?');

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
         . '<rect width="100" height="100" rx="50" fill="#b99d3f"/>'
         . '<text x="50" y="50" font-family="Georgia, serif" font-size="38" fill="#3e2f1c" '
         . 'text-anchor="middle" dominant-baseline="central">' . h($iniciais) . '</text>'
         . '</svg>';

    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function fotoUsuario($foto_perfil, $nome) {
    if (!empty($foto_perfil)) {
        return h($foto_perfil);
    }

    return avatarIniciais($nome);
}

function formatarData($dataMysql) {
    if (empty($dataMysql)) {
        return '';
    }

    $ts = strtotime($dataMysql);

    if (!$ts) {
        return '';
    }

    $meses = [
        '',
        'jan',
        'fev',
        'mar',
        'abr',
        'mai',
        'jun',
        'jul',
        'ago',
        'set',
        'out',
        'nov',
        'dez'
    ];

    return date('d', $ts) . ' ' .
           $meses[(int)date('n', $ts)] . ' ' .
           date('Y', $ts);
}
