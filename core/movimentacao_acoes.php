<?php
// ======================================================================
// ARQUIVO ATUALIZADO: core/movimentacao_acoes.php
// Objetivo: Adicionar validação para impedir saídas com stock insuficiente.
// ======================================================================

session_start();
require_once 'conexao.php';
require_once 'verificar_sessao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $produto_id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
    $tipo_movimentacao = $_POST['tipo_movimentacao'];
    $usuario_id = $_SESSION['usuario_id'];

    if (!$produto_id || !$quantidade || !in_array($tipo_movimentacao, ['entrada', 'saida']) || $quantidade <= 0) {
        header('Location: ../dashboard.php?erro=dados_invalidos');
        exit();
    }

    // Inicia uma transação para garantir a consistência dos dados
    $conexao->begin_transaction();

    try {
        // Se a movimentação for de SAÍDA, precisamos validar o stock
        if ($tipo_movimentacao == 'saida') {
            // 1. Busca o stock atual do produto
            $query_check = "SELECT quantidade_estoque FROM produtos WHERE id = ? FOR UPDATE"; // "FOR UPDATE" bloqueia a linha para evitar condições de corrida
            $stmt_check = $conexao->prepare($query_check);
            $stmt_check->bind_param("i", $produto_id);
            $stmt_check->execute();
            $resultado = $stmt_check->get_result();
            $produto_atual = $resultado->fetch_assoc();
            $stmt_check->close();

            // 2. Verifica se a quantidade a ser retirada é maior que o stock existente
            if (!$produto_atual || $quantidade > $produto_atual['quantidade_estoque']) {
                // 3. Se não houver stock suficiente, desfaz a transação e redireciona com erro
                $conexao->rollback();
                header('Location: ../dashboard.php?erro=stock_insuficiente');
                exit();
            }
        }

        // Se a validação passar (ou for uma entrada), o processo continua
        if ($tipo_movimentacao == 'entrada') {
            $query_update = "UPDATE produtos SET quantidade_estoque = quantidade_estoque + ? WHERE id = ?";
        } else {
            $query_update = "UPDATE produtos SET quantidade_estoque = quantidade_estoque - ? WHERE id = ?";
        }
        
        $stmt_update = $conexao->prepare($query_update);
        $stmt_update->bind_param("ii", $quantidade, $produto_id);
        $stmt_update->execute();

        // Insere o registo na tabela de movimentações
        $query_insert = "INSERT INTO movimentacoes (produto_id, usuario_id, tipo_movimentacao, quantidade) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conexao->prepare($query_insert);
        $stmt_insert->bind_param("iisi", $produto_id, $usuario_id, $tipo_movimentacao, $quantidade);
        $stmt_insert->execute();

        // Se tudo correu bem, confirma a transação
        $conexao->commit();
        header('Location: ../dashboard.php?status=movimentacao_sucesso');

    } catch (mysqli_sql_exception $exception) {
        $conexao->rollback();
        header('Location: ../dashboard.php?erro=falha_movimentacao');
    } finally {
        if (isset($stmt_update)) $stmt_update->close();
        if (isset($stmt_insert)) $stmt_insert->close();
        $conexao->close();
        exit();
    }
}
?>