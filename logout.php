<?php
// Inicia a sessão se ainda não estiver iniciada
session_start();

// Limpa todas as variáveis da sessão
$_SESSION = array();

// Se a sessão estiver definida para usar um cookie, ele deve ser excluído também.
// Isto é feito definindo a expiração para o passado para remover o cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão do lado do servidor
session_destroy();

// Redireciona o utilizador para a página de login
header("Location: login.php"); // Ou qualquer outra página desejada
exit();
?>