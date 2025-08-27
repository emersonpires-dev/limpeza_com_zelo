<?php
// ======================================================================
// ARQUIVO NOVO: core/verificar_sessao.php
// Objetivo: Proteger páginas que exigem login.
// ======================================================================

session_start(); // Inicia ou continua a sessão existente

// Verifica se a sessão do usuário não existe
if (!isset($_SESSION['usuario_id'])) {
    // Se não estiver logado, redireciona para a página de login
    header('Location: login.php?erro=acesso_negado');
    exit();
}
?>