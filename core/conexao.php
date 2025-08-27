<?php
// =.====================================================================
// ARQUIVO: core/conexao.php
// Objetivo: Estabelecer a conexão com o banco de dados MySQL.
// ======================================================================

// Definição das constantes com as informações do banco de dados
// Substitua os valores abaixo pelos dados do seu ambiente local (XAMPP)
define('DB_HOST', 'localhost');       // O servidor do banco de dados, geralmente 'localhost'
define('DB_USER', 'root');            // O usuário do banco de dados, 'root' é o padrão no XAMPP
define('DB_PASS', '');                // A senha do banco de dados, em branco por padrão no XAMPP
define('DB_NAME', 'estoque_zelo');    // O nome do banco de dados que criaremos no phpMyAdmin

// Tentativa de conexão com o banco de dados usando a biblioteca MySQLi
$conexao = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificação da conexão
// Se houver um erro na conexão, o script será interrompido e exibirá uma mensagem de erro.
if ($conexao->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conexao->connect_error);
}

// Define o conjunto de caracteres para UTF-8 para evitar problemas com acentuação
$conexao->set_charset("utf8");

// Se tudo correu bem, a variável $conexao estará disponível para ser usada em outros arquivos.
?>
