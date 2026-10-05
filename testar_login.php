<?php
require_once 'banco.php';

$emailTeste = 'admin@seusite.com';
$senhaTeste = 'admin123';

echo "<h2>Diagnóstico de Login</h2>";

// 1. Busca no banco
$sql = "SELECT idUsuarios, Nome, Senha, Tipo_usuario FROM Usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $emailTeste);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    echo "<p style='color:red;'>❌ Usuário com o e-mail '{$emailTeste}' NÃO foi encontrado no banco de dados.</p>";
    exit;
}

echo "<p style='color:green;'>✔️ Usuário encontrado: " . htmlspecialchars($user['Nome']) . "</p>";
echo "Tamanho do hash gravado no banco: " . strlen($user['Senha']) . " caracteres (deve ser 60)<br>";
echo "Valor de Tipo_usuario no banco: '" . htmlspecialchars($user['Tipo_usuario']) . "'<br><br>";

// 2. Teste da senha
if (password_verify($senhaTeste, $user['Senha'])) {
    echo "<p style='color:green;'>✔️ A senha '{$senhaTeste}' é VALIDA!</p>";
} else {
    echo "<p style='color:red;'>❌ A senha '{$senhaTeste}' é INVÁLIDA. (Possível problema: coluna VARCHAR muito pequena no MySQL que cortou o hash).</p>";
}
?>