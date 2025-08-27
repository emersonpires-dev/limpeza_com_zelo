<?php
// ======================================================================
// ARQUIVO ATUALIZADO: core/produto_acoes.php
// Objetivo: Processar o formulário para ADICIONAR ou EDITAR produtos, incluindo o nível mínimo.
// ======================================================================

session_start();
require_once 'conexao.php';
require_once 'verificar_sessao.php'; // Garante que apenas usuários logados possam executar esta ação

// Verifica se os dados foram enviados via POST (para Adicionar/Editar)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Coleta e limpa os dados do formulário
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $nome_produto = trim($_POST['product_name']);
    $unidade_medida = trim($_POST['unit_measure']);
    $quantidade_estoque = filter_input(INPUT_POST, 'quantity_stock', FILTER_VALIDATE_INT);
    $nivel_minimo = filter_input(INPUT_POST, 'nivel_minimo', FILTER_VALIDATE_INT); // NOVO CAMPO COLETADO

    // Validação simples, agora incluindo o nível mínimo
    if (empty($nome_produto) || empty($unidade_medida) || $quantidade_estoque === false || $nivel_minimo === false) {
        // Se algum campo estiver inválido, redireciona com erro
        header('Location: ../produto_form.php?erro=campos_invalidos' . ($product_id ? '&id=' . $product_id : ''));
        exit();
    }

    if ($product_id) {
        // MODO DE ATUALIZAÇÃO (se existe um ID de produto)
        $query = "UPDATE produtos SET nome_produto = ?, unidade_medida = ?, quantidade_estoque = ?, nivel_minimo = ? WHERE id = ?";
        $stmt = $conexao->prepare($query);
        $stmt->bind_param("ssiii", $nome_produto, $unidade_medida, $quantidade_estoque, $nivel_minimo, $product_id);
        $status_redirect = 'produto_atualizado';

    } else {
        // MODO DE INSERÇÃO (se não existe um ID de produto)
        $query = "INSERT INTO produtos (nome_produto, unidade_medida, quantidade_estoque, nivel_minimo) VALUES (?, ?, ?, ?)";
        $stmt = $conexao->prepare($query);
        $stmt->bind_param("ssii", $nome_produto, $unidade_medida, $quantidade_estoque, $nivel_minimo);
        $status_redirect = 'produto_adicionado';
    }
    
    // Executa a consulta e redireciona com base no sucesso ou falha
    if ($stmt->execute()) {
        header('Location: ../dashboard.php?status=' . $status_redirect);
    } else {
        header('Location: ../produto_form.php?erro=falha_salvar' . ($product_id ? '&id=' . $product_id : ''));
    }
    
    // Fecha o statement e a conexão
    $stmt->close();
    $conexao->close();
    exit();

} // <-- CHAVE DE FECHAMENTO ADICIONADA AQUI. Este é o ponto principal da correção.

// LÓGICA PARA EXCLUSÃO DE PRODUTO VIA GET
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['acao']) && $_GET['acao'] == 'excluir') {
    
    // ===== VERIFICAÇÃO DE PERMISSÃO ADICIONADA =====
    if ($_SESSION['usuario_permissao'] !== 'admin') {
        // Se não for admin, redireciona com erro e interrompe o script
        header('Location: ../dashboard.php?erro=acesso_negado');
        exit();
    }
    // ===============================================

    // Coleta e valida o ID do produto da URL
    $product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($product_id) {
        // Prepara a query de exclusão para evitar SQL Injection
        $query = "DELETE FROM produtos WHERE id = ?";
        $stmt = $conexao->prepare($query);
        $stmt->bind_param("i", $product_id);

        // Executa e redireciona com base no resultado
        if ($stmt->execute()) {
            header('Location: ../dashboard.php?status=produto_excluido');
        } else {
            header('Location: ../dashboard.php?erro=falha_excluir');
        }
        $stmt->close();
    } else {
        // Se o ID for inválido, apenas redireciona
        header('Location: ../dashboard.php?erro=id_invalido');
    }
    
    $conexao->close();
    exit();
}
?>