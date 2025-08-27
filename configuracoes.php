<?php
// ======================================================================
// ARQUIVO COMPLETO E CORRIGIDO: configuracoes.php
// ======================================================================

require_once 'core/verificar_sessao.php';
require_once 'core/conexao.php';

$page_title = 'Configurações - Controle de Estoque';

// Apenas administradores podem ver a lista completa de usuários
if ($_SESSION['usuario_permissao'] !== 'admin') {
    // Redireciona operadores para o dashboard ou mostra uma mensagem de erro
    header('Location: dashboard.php?erro=acesso_negado_config');
    exit();
}

// Busca todos os usuários do banco
$query_usuarios = "SELECT id, nome, email, permissao, foto_perfil FROM usuarios ORDER BY nome ASC";
$resultado_usuarios = $conexao->query($query_usuarios);

include 'includes/header.php';
?>

<?php
    // Garante que as variáveis $status e $erro sempre existam para evitar avisos
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $erro = isset($_GET['erro']) ? $_GET['erro'] : '';

    // --- Bloco para mensagens de SUCESSO ---
    if ($status == 'usuario_adicionado'):
?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Usuário adicionado com sucesso!</div>
<?php
    elseif ($status == 'usuario_atualizado'):
?>
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4">Usuário atualizado com sucesso!</div>
<?php
    elseif ($status == 'usuario_excluido'):
?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Usuário excluído com sucesso!</div>
<?php
    endif;

    // --- Bloco para mensagens de ERRO ---
    if ($erro == 'email_duplicado'):
?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><b>Erro:</b> O e-mail fornecido já está a ser utilizado por outro usuário.</div>
<?php
    elseif ($erro == 'campos_obrigatorios'):
?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><b>Erro:</b> Por favor, preencha todos os campos obrigatórios (Nome e E-mail).</div>
<?php
    elseif ($erro == 'senha_obrigatoria'):
?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><b>Erro:</b> A senha é obrigatória ao criar um novo usuário.</div>
<?php
    endif;
?>
<div class="space-y-8">
    <div class="bg-white p-8 rounded-lg shadow-lg border">
        <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
            <h2 class="text-3xl font-bold">Gerenciamento de Usuários</h2>
            <button onclick="openUserModal()" class="bg-blue-500 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-600">Adicionar Novo Usuário</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="p-3 font-semibold">Foto</th>
                        <th class="p-3 font-semibold">Nome</th>
                        <th class="p-3 font-semibold">Email</th>
                        <th class="p-3 font-semibold">Permissão</th>
                        <th class="p-3 font-semibold text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_usuarios->num_rows > 0): ?>
                        <?php while($usuario = $resultado_usuarios->fetch_assoc()): 
                            $usuario_json = htmlspecialchars(json_encode($usuario), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr class="border-b hover:bg-slate-50">
                                <td class="p-3">
                                    <img src="<?php echo !empty($usuario['foto_perfil']) ? htmlspecialchars($usuario['foto_perfil']) : 'https://placehold.co/60x60/E2E8F0/475569?text=Foto'; ?>" alt="Foto de <?php echo htmlspecialchars($usuario['nome']); ?>" class="w-12 h-12 rounded-full object-cover">
                                </td>
                                <td class="p-3"><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td class="p-3"><?php echo ucfirst(htmlspecialchars($usuario['permissao'])); ?></td>
                                <td class="p-3 text-center">
                                    <button onclick='openUserModal(<?php echo $usuario_json; ?>)' class="text-blue-600 hover:underline">Editar</button>
                                    <?php if ($_SESSION['usuario_id'] != $usuario['id']): ?>
                                        | <a href="core/usuario_acoes.php?acao=excluir&id=<?php echo $usuario['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.');">Excluir</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr class="border-b"><td colspan="5" class="p-3 text-center text-slate-500">Nenhum usuário encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white p-8 rounded-lg shadow-lg border">
        <h2 class="text-3xl font-bold mb-6">Configurações Gerais</h2>
        <form action="#" method="POST">
            <div class="max-w-md">
                <label for="low_stock_threshold" class="block text-lg font-medium text-slate-700 mb-1">Alerta de Estoque Baixo</label>
                <p class="text-sm text-slate-500 mb-2">Defina a quantidade mínima para um produto ser considerado com "estoque baixo".</p>
                <input type="number" id="low_stock_threshold" name="low_stock_threshold" class="w-full p-3 border border-slate-300 rounded-md" value="5">
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-slate-800 text-white font-bold py-2 px-6 rounded-md hover:bg-slate-700">Salvar Configurações</button>
            </div>
        </form>
    </div>
</div>

<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-2xl">
        <div class="flex justify-between items-center mb-6">
            <h2 id="modalTitle" class="text-3xl font-bold">Adicionar Novo Usuário</h2>
            <button onclick="closeUserModal()" class="text-3xl text-slate-500 hover:text-slate-800">&times;</button>
        </div>
        
        <form action="core/usuario_acoes.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="userId" name="user_id" value="">

            <div class="flex flex-col md:flex-row gap-8">
                <div class="flex-shrink-0 text-center">
                    <img id="userImagePreview" src="https://placehold.co/128x128/E2E8F0/475569?text=Foto" alt="Foto do Usuário" class="w-32 h-32 rounded-full object-cover mx-auto mb-4">
                    <label for="user_image" class="cursor-pointer bg-slate-200 text-slate-800 font-bold py-2 px-4 rounded-md hover:bg-slate-300">Trocar Foto</label>
                    <input type="file" name="user_image" id="user_image" class="hidden" onchange="previewImage(event)">
                </div>
                <div class="flex-grow space-y-4">
                     <div>
                        <label for="userName" class="block text-sm font-medium text-slate-600 mb-1">Nome Completo</label>
                        <input type="text" id="userName" name="user_name" class="w-full p-3 border border-slate-300 rounded-md" required>
                    </div>
                     <div>
                        <label for="userEmail" class="block text-sm font-medium text-slate-600 mb-1">Email</label>
                        <input type="email" id="userEmail" name="user_email" class="w-full p-3 border border-slate-300 rounded-md" required>
                    </div>
                     <div>
                        <label for="userPermission" class="block text-sm font-medium text-slate-600 mb-1">Permissão</label>
                        <select id="userPermission" name="user_permission" class="w-full p-3 border border-slate-300 rounded-md">
                            <option value="operador">Operador</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                     <div>
                        <label for="userPassword" class="block text-sm font-medium text-slate-600 mb-1">Nova Senha (deixe em branco para não alterar)</label>
                        <input type="password" id="userPassword" name="user_password" class="w-full p-3 border border-slate-300 rounded-md">
                    </div>
                </div>
            </div>
            
            <div class="mt-8 flex justify-end gap-4">
                <button type="button" onclick="closeUserModal()" class="bg-slate-200 text-slate-800 font-bold py-2 px-6 rounded-md hover:bg-slate-300">Cancelar</button>
                <button type="submit" class="bg-slate-800 text-white font-bold py-2 px-6 rounded-md hover:bg-slate-700">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
    const userModal = document.getElementById('userModal');
    const modalTitle = document.getElementById('modalTitle');
    const userIdInput = document.getElementById('userId');
    const userNameInput = document.getElementById('userName');
    const userEmailInput = document.getElementById('userEmail');
    const userPermissionSelect = document.getElementById('userPermission');
    const userPasswordInput = document.getElementById('userPassword');
    const userImagePreview = document.getElementById('userImagePreview');
    const defaultUserImage = 'https://placehold.co/128x128/E2E8F0/475569?text=Foto';

    function openUserModal(userData = null) {
        if (userData) {
            modalTitle.textContent = 'Editar Usuário';
            userIdInput.value = userData.id;
            userNameInput.value = userData.nome;
            userEmailInput.value = userData.email;
            userPermissionSelect.value = userData.permissao;
            userImagePreview.src = userData.foto_perfil ? userData.foto_perfil : defaultUserImage;
        } else {
            modalTitle.textContent = 'Adicionar Novo Usuário';
            userIdInput.value = '';
            userNameInput.value = '';
            userEmailInput.value = '';
            userPermissionSelect.value = 'operador';
            userImagePreview.src = defaultUserImage;
        }
        userPasswordInput.value = '';
        userModal.classList.remove('hidden');
    }

    function closeUserModal() {
        userModal.classList.add('hidden');
    }

    function previewImage(event) {
        if (event.target.files && event.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e){
                userImagePreview.src = e.target.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>

<?php
$conexao->close();
include 'includes/footer.php';
?>