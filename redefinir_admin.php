<?php
require_once 'banco.php';

// E-mail do seu admin e a nova senha desejada
$email = 'admin@seusite.com';
$novaSenha = 'admin123';

// Gera o hash de forma limpa pelo seu próprio servidor
$hashSeguro = password_hash($novaSenha, PASSWORD_DEFAULT);

$sql = "UPDATE Usuarios SET Senha = ?, Tipo_usuario = 'admin' WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hashSeguro, $email);

if ($stmt->execute()) {
    echo "<h3>✔️ Senha e tipo atualizados com sucesso!</h3>";
    echo "<p>Tente logar agora com:</p>";
    echo "<strong>E-mail:</strong> " . htmlspecialchars($email) . "<br>";
    echo "<strong>Senha:</strong> " . htmlspecialchars($novaSenha) . "<br>";
} else {
    echo "Erro ao atualizar: " . $conn->error;
}
?>