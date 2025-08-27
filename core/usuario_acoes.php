<?php
// ======================================================================
// ARQUIVO ATUALIZADO: core/usuario_acoes.php
// Objetivo: Adicionada verificação de e-mail duplicado e corrigido aviso de sessão.
// ======================================================================

// session_start(); // REMOVIDO: Esta linha causava o aviso. 'verificar_sessao.php' já trata disso.
require_once 'conexao.php';
require_once 'verificar_sessao.php';

// Apenas administradores podem executar estas ações
if ($_SESSION['usuario_permissao'] !== 'admin') {
    header('Location: ../dashboard.php?erro=acesso_negado');
    exit();
}

// --- LÓGICA PARA ADICIONAR/EDITAR (MÉTODO POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $nome = trim($_POST['user_name']);
    $email = filter_input(INPUT_POST, 'user_email', FILTER_VALIDATE_EMAIL);
    $permissao = in_array($_POST['user_permission'], ['admin', 'operador']) ? $_POST['user_permission'] : 'operador';
    $senha = $_POST['user_password'];
    
    // Validação
    if (empty($nome) || empty($email)) {
        header('Location: ../configuracoes.php?erro=campos_obrigatorios');
        exit();
    }

    // ===== NOVA VERIFICAÇÃO DE E-MAIL DUPLICADO =====
    $query_check_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    $stmt_check = $conexao->prepare($query_check_email);
    // Se for um novo usuário ($user_id é nulo), usamos um ID que nunca existirá (0) para a comparação
    $id_para_ignorar = $user_id ? $user_id : 0;
    $stmt_check->bind_param("si", $email, $id_para_ignorar);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Se encontrou um e-mail igual em OUTRO usuário, redireciona com erro
        $stmt_check->close();
        header('Location: ../configuracoes.php?erro=email_duplicado');
        exit();
    }
    $stmt_check->close();
    // ===============================================

    // Lógica de Upload da Imagem (código original mantido)
    $caminho_foto = null;
    if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] == 0) {
        $diretorio_uploads = '../assets/images/perfis/';
        if (!is_dir($diretorio_uploads)) {
            mkdir($diretorio_uploads, 0777, true);
        }
        $nome_arquivo = uniqid() . '-' . basename($_FILES['user_image']['name']);
        $caminho_foto = $diretorio_uploads . $nome_arquivo;
        move_uploaded_file($_FILES['user_image']['tmp_name'], $caminho_foto);
        $caminho_foto = 'assets/images/perfis/' . $nome_arquivo;
    }

    if ($user_id) {
        // --- MODO DE ATUALIZAÇÃO --- (código original mantido)
        $query_parts = [];
        $params = [];
        $types = "";

        array_push($query_parts, "nome = ?");
        array_push($params, $nome);
        $types .= "s";

        array_push($query_parts, "email = ?");
        array_push($params, $email);
        $types .= "s";
        
        array_push($query_parts, "permissao = ?");
        array_push($params, $permissao);
        $types .= "s";

        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            array_push($query_parts, "senha = ?");
            array_push($params, $senha_hash);
            $types .= "s";
        }
        if ($caminho_foto) {
            array_push($query_parts, "foto_perfil = ?");
            array_push($params, $caminho_foto);
            $types .= "s";
        }

        array_push($params, $user_id);
        $types .= "i";

        $query = "UPDATE usuarios SET " . implode(", ", $query_parts) . " WHERE id = ?";
        $stmt = $conexao->prepare($query);
        $stmt->bind_param($types, ...$params);
        $status_redirect = 'usuario_atualizado';

    } else {
        // --- MODO DE INSERÇÃO --- (código original mantido)
        if (empty($senha)) {
            header('Location: ../configuracoes.php?erro=senha_obrigatoria');
            exit();
        }
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $query = "INSERT INTO usuarios (nome, email, permissao, senha, foto_perfil) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($query);
        $stmt->bind_param("sssss", $nome, $email, $permissao, $senha_hash, $caminho_foto);
        $status_redirect = 'usuario_adicionado';
    }
    
    if ($stmt->execute()) {
        header('Location: ../configuracoes.php?status=' . $status_redirect);
    } else {
        // Em caso de erro, podemos verificar se é de e-mail duplicado (fallback)
        if ($conexao->errno == 1062) { // 1062 é o código de erro para entrada duplicada
            header('Location: ../configuracoes.php?erro=email_duplicado');
        } else {
            header('Location: ../configuracoes.php?erro=falha_salvar');
        }
    }
    
    $stmt->close();
    $conexao->close();
    exit();
}

// --- LÓGICA PARA EXCLUIR (MÉTODO GET) --- (código original mantido)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['acao']) && $_GET['acao'] == 'excluir') {
    $user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($user_id && $user_id != $_SESSION['usuario_id']) {
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conexao->prepare($query);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            header('Location: ../configuracoes.php?status=usuario_excluido');
        } else {
            header('Location: ../configuracoes.php?erro=falha_excluir');
        }
        $stmt->close();
    } else {
        header('Location: ../configuracoes.php?erro=operacao_invalida');
    }
    
    $conexao->close();
    exit();
}
?>