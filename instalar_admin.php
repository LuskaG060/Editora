<?php
require_once 'banco.php';

// Dados fixos do seu Admin de Testes
$nome  = 'Administrador Principal';
$email = 'admin@seusite.com';
$senha = 'admin123'; // <--- Sua senha padronizada aqui

// O próprio PHP gera a senha criptografada sem erros
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$tipo      = 'admin';

// Esta instrução SQL cria o usuário ou atualiza se ele já existir
$sql = "INSERT INTO Usuarios (Nome, email, Senha, Tipo_usuario) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            Senha = VALUES(Senha), 
            Tipo_usuario = 'admin'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $nome, $email, $senhaHash, $tipo);

if ($stmt->execute()) {
    echo "<h2>✅ Conta de Admin configurada e salva com sucesso!</h2>";
    echo "<p>Agora você sempre pode logar com:</p>";
    echo "<ul>";
    echo "<li><strong>E-mail:</strong> {$email}</li>";
    echo "<li><strong>Senha:</strong> {$senha}</li>";
    echo "</ul>";
    echo "<a href='login.php'>Ir para a tela de Login</a>";
} else {
    echo "❌ Erro ao criar admin: " . $conn->error;
}
?>