<?php
// ======================================================================
// ARQUIVO ATUALIZADO: core/autenticacao.php
// Objetivo: Processar login e implementar a funcionalidade "Lembrar-me".
// ======================================================================

require_once 'conexao.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha']; // CORRIGIDO: Estava 'password', agora é 'senha' para corresponder ao formulário.

    // --- LÓGICA DO "LEMBRAR-ME" ---
    // Isto deve ser executado ANTES de session_start()
    if (isset($_POST['remember_me'])) {
        // Se a opção foi marcada, define a duração do cookie da sessão para 30 dias.
        $lifetime = 60 * 60 * 24 * 30; // 30 dias em segundos
        session_set_cookie_params($lifetime);
    }
    // Se a opção não for marcada, a sessão durará apenas até o navegador ser fechado (comportamento padrão).
    
    session_start(); // Inicia a sessão PHP

    if (empty($email) || empty($senha)) {
        header('Location: ../login.php?erro=campos_vazios');
        exit();
    }

    // Prepara a consulta para evitar injeção de SQL
    $stmt = $conexao->prepare("SELECT id, nome, senha, permissao, foto_perfil FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Verifica se a senha enviada corresponde ao hash no banco de dados
        if (password_verify($senha, $usuario['senha'])) {
            // Senha correta, cria a sessão do usuário
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_permissao'] = $usuario['permissao'];
            $_SESSION['usuario_foto'] = $usuario['foto_perfil'];

            // Redireciona para o dashboard
            header('Location: ../dashboard.php');
            exit();
        }
    }

    // Se o email não existir ou a senha estiver incorreta, redireciona de volta para o login com erro
    header('Location: ../login.php?erro=login_invalido');
    exit();
}
?>