<?php

// Inicia a sessão somente se ainda não estiver iniciada
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


// Remove todos os dados da sessão
$_SESSION = [];


// Se existir cookie de sessão, remove também
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}


// Destrói a sessão
session_destroy();


// Volta para a página de login
header("Location: login.php");
exit;
