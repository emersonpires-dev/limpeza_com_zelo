<?php
// ======================================================================
// ARQUIVO ATUALIZADO: dashboard.php
// Objetivo: Adicionar o alerta visual para produtos com stock baixo.
// ======================================================================

require_once 'core/verificar_sessao.php'; 
require_once 'core/conexao.php';

// A consulta de produtos agora busca também o nível mínimo para a comparação
$query_produtos = "SELECT id, nome_produto, quantidade_estoque, unidade_medida, nivel_minimo FROM produtos ORDER BY nome_produto ASC";
$resultado_produtos = $conexao->query($query_produtos);

$produtos_para_tabela = [];
$produtos_com_stock_baixo = 0; // <-- NOVO: Inicializa o contador

if ($resultado_produtos->num_rows > 0) {
    while($row = $resultado_produtos->fetch_assoc()) {
        $produtos_para_tabela[] = $row;
        
        // <-- NOVO: Verifica se o stock está baixo e incrementa o contador
        if ($row['quantidade_estoque'] <= $row['nivel_minimo']) {
            $produtos_com_stock_baixo++;
        }
    }
}

$page_title = 'Dashboard - Controle de Estoque';
include 'includes/header.php';

$status = isset($_GET['status']) ? $_GET['status'] : '';
?>

<!-- Mensagem de boas-vindas -->
<div class="bg-white p-8 rounded-lg shadow-lg border mb-8">
    <h2 class="text-3xl font-bold">Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h2>
    <p class="text-slate-600">Este é o seu painel de controlo de stock.</p>
</div>

<?php 
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $erro = isset($_GET['erro']) ? $_GET['erro'] : '';

    // Alertas de Sucesso (baseados no parâmetro 'status')
    if ($status == 'produto_adicionado'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Produto adicionado com sucesso!</div>
    <?php elseif ($status == 'produto_atualizado'): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4">Produto atualizado com sucesso!</div>
    <?php elseif ($status == 'movimentacao_sucesso'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Movimentação de stock registrada com sucesso!</div>
    <?php elseif ($status == 'produto_excluido'): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Produto excluído com sucesso!</div>
    <?php endif;

    // Alertas de Erro (baseados no parâmetro 'erro')
    if ($erro == 'stock_insuficiente'): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><b>Erro:</b> A quantidade de saída é maior que o stock disponível.</div>
    <?php elseif ($erro == 'falha_movimentacao' || $erro == 'dados_invalidos'): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><b>Erro:</b> Ocorreu uma falha ao processar a operação. Tente novamente.</div>
        <?php elseif ($erro == 'acesso_negado'): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><b>Erro:</b> Você não tem permissão para executar esta ação.</div>
    <?php endif; 
?>
<?php if ($produtos_com_stock_baixo > 0): ?>
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg shadow-md mb-8" role="alert">
        <p class="font-bold">Atenção!</p>
        <?php if ($produtos_com_stock_baixo == 1): ?>
            <p>Existe <strong>1 produto</strong> com stock baixo. Considere fazer uma nova encomenda.</p>
        <?php else: ?>
            <p>Existem <strong><?php echo $produtos_com_stock_baixo; ?> produtos</strong> com stock baixo. Considere fazer uma nova encomenda.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Tabela de Estoque de Produtos -->
<div class="bg-white p-8 rounded-lg shadow-lg border">
    <header class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <h2 class="text-3xl font-bold">Stock de Produtos</h2>
    <div class="grid grid-cols-2 md:flex md:justify-end gap-2">
        <button onclick="openMovementModal('saida')" class="bg-red-500 text-white font-bold py-2 px-4 rounded-md hover:bg-red-600">Nova Saída</button>
        <button onclick="openMovementModal('entrada')" class="bg-green-500 text-white font-bold py-2 px-4 rounded-md hover:bg-green-600">Nova Entrada</button>
        <a href="produto_form.php" class="bg-blue-500 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-600">Adicionar Produto</a>
        
        <div class="relative" id="download-container-inventario">
            <button type="button" onclick="toggleDropdownInventario()" class="bg-slate-700 text-white font-bold py-2 px-4 rounded-md hover:bg-slate-600 transition-colors w-full sm:w-auto flex items-center gap-2">
                Download
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
            <div id="download-dropdown-inventario" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                <a href="core/gerar_relatorio_inventario.php?formato=pdf" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Baixar como PDF</a>
                <a href="core/gerar_relatorio_inventario.php?formato=csv" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Baixar como CSV</a>
            </div>
        </div>
    </div>
</header>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-3 font-semibold">Produto</th>
                    <th class="p-3 font-semibold">Qtd. <span class="hidden sm:inline">em Stock</span></th>
                    <th class="p-3 font-semibold">Unidade</th>
                    <th class="p-3 font-semibold text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($produtos_para_tabela)): ?>
                    <?php foreach ($produtos_para_tabela as $produto): ?>
                        <?php
                            // LÓGICA DO ALERTA VISUAL
                            // Define a classe da linha com base no nível de stock
                            $row_class = 'border-b hover:bg-slate-50';
                            if ($produto['quantidade_estoque'] <= $produto['nivel_minimo']) {
                                $row_class = 'border-b bg-red-100 hover:bg-red-200';
                            }
                        ?>
                        <tr class="<?php echo $row_class; ?>">
                            <td class="p-3 font-medium"><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                            <td class="p-3 font-bold"><?php echo htmlspecialchars($produto['quantidade_estoque']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($produto['unidade_medida']); ?></td>
                          <td class="p-3 text-center">
    <a href="produto_form.php?id=<?php echo $produto['id']; ?>" class="text-blue-600 hover:underline">Editar</a>

    <?php // Apenas administradores podem ver o botão de excluir ?>
    <?php if ($_SESSION['usuario_permissao'] === 'admin'): ?>
        |
        <a 
            href="core/produto_acoes.php?acao=excluir&id=<?php echo $produto['id']; ?>" 
            class="text-red-600 hover:underline" 
            onclick="return confirm('Tem certeza que deseja excluir este produto? A ação não pode ser desfeita.');"
        >
            Excluir
        </a>
    <?php endif; ?>
</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="border-b">
                        <td colspan="4" class="p-3 text-center text-slate-500">Nenhum produto cadastrado ainda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Movimentação de Stock -->
<div id="movementModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md">
        <div class="flex justify-between items-center mb-6">
            <h2 id="movementModalTitle" class="text-3xl font-bold">Nova Entrada</h2>
            <button onclick="closeMovementModal()" class="text-3xl text-slate-500 hover:text-slate-800">&times;</button>
        </div>
        
        <form action="core/movimentacao_acoes.php" method="POST">
            <input type="hidden" id="movementType" name="tipo_movimentacao" value="">
            
            <div class="space-y-4">
                <div>
                    <label for="produto_id" class="block text-sm font-medium text-slate-600 mb-1">Produto</label>
                    <select id="produto_id" name="produto_id" class="w-full p-3 border border-slate-300 rounded-md" required>
                        <option value="">Selecione um produto</option>
                        <?php foreach ($produtos_para_tabela as $produto): ?>
                            <option value="<?php echo $produto['id']; ?>"><?php echo htmlspecialchars($produto['nome_produto']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="quantidade" class="block text-sm font-medium text-slate-600 mb-1">Quantidade</label>
                    <input type="number" id="quantidade" name="quantidade" class="w-full p-3 border border-slate-300 rounded-md" min="1" required>
                </div>
            </div>
            
            <div class="mt-8 flex justify-end gap-4">
                <button type="button" onclick="closeMovementModal()" class="bg-slate-200 text-slate-800 font-bold py-2 px-6 rounded-md hover:bg-slate-300">Cancelar</button>
                <button type="submit" id="movementModalButton" class="text-white font-bold py-2 px-6 rounded-md">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<script>
    const movementModal = document.getElementById('movementModal');
    const movementModalTitle = document.getElementById('movementModalTitle');
    const movementModalButton = document.getElementById('movementModalButton');
    const movementTypeInput = document.getElementById('movementType');

    function openMovementModal(type) {
        movementTypeInput.value = type;
        if (type === 'entrada') {
            movementModalTitle.textContent = 'Nova Entrada';
            movementModalButton.className = 'bg-green-500 text-white font-bold py-2 px-6 rounded-md hover:bg-green-600';
        } else {
            movementModalTitle.textContent = 'Nova Saída';
            movementModalButton.className = 'bg-red-500 text-white font-bold py-2 px-6 rounded-md hover:bg-red-600';
        }
        movementModal.classList.remove('hidden');
    }

    function closeMovementModal() {
        movementModal.classList.add('hidden');
    }
    // NOVO SCRIPT PARA O DROPDOWN DE DOWNLOAD
    function toggleDropdownInventario() {
        document.getElementById('download-dropdown-inventario').classList.toggle('hidden');
    }

    // Fecha o dropdown se clicar fora dele
    window.onclick = function(event) {
        if (!event.target.closest('#download-container-inventario')) {
            const dropdown = document.getElementById('download-dropdown-inventario');
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        }
    }
</script>

<?php
include 'includes/footer.php';
?>
