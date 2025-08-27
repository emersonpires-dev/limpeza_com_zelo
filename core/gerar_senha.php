<?php
// Arquivo: gerar_senha.php
// Objetivo: Gerar um hash seguro para uma senha

$senhaParaCriptografar = 'admin123';
$hashDaSenha = password_hash($senhaParaCriptografar, PASSWORD_DEFAULT);

echo "Copie este hash e cole no seu banco de dados: <br><br>";
echo "<strong>" . $hashDaSenha . "</strong>";
?>