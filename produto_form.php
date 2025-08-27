<?php
// ======================================================================
// ARQUIVO: produto_form.php
// Objetivo: Formulário para adicionar ou editar produtos.
// ======================================================================

require_once 'core/verificar_sessao.php'; // Protege a página
require_once 'core/conexao.php';

// Lógica para o título e para o modo de edição
$is_editing = isset($_GET['id']);
$page_title = $is_editing ? 'Editar Produto' : 'Adicionar Novo Produto';

$produto = [
    'nome_produto' => '',
    'unidade_medida' => '',
    'quantidade_estoque' => 0,
    'nivel_minimo' => 5 // Valor padrão para o novo campo
];

// Se estiver no modo de edição, busca os dados do produto no banco
if ($is_editing) {
    $produto_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($produto_id) {
        // A consulta agora também busca o novo campo 'nivel_minimo'
        $stmt = $conexao->prepare("SELECT nome_produto, unidade_medida, quantidade_estoque, nivel_minimo FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            $produto = $resultado->fetch_assoc();
        } else {
            // Se o produto não for encontrado, redireciona para o dashboard
            header('Location: dashboard.php?erro=produto_nao_encontrado');
            exit();
        }
        $stmt->close();
    }
}

include 'includes/header.php';

$erro = isset($_GET['erro']) ? $_GET['erro'] : '';
?>

<div class="w-full max-w-2xl mx-auto">
    <div class="bg-white p-8 rounded-lg shadow-lg border">
        <h2 class="text-3xl font-bold mb-6"><?php echo $page_title; ?></h2>
        
        <?php if ($erro == 'campos_invalidos'): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                Por favor, preencha todos os campos corretamente.
            </div>
        <?php elseif ($erro == 'falha_salvar'): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                Ocorreu um erro ao salvar o produto. Tente novamente.
            </div>
        <?php endif; ?>

        <!-- Formulário agora aponta para o script de ações no backend -->
        <form action="core/produto_acoes.php" method="POST">
            
            <?php // Se estiver editando, incluímos o ID do produto de forma oculta ?>
            <?php if ($is_editing): ?>
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($_GET['id']); ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="product_name" class="block text-sm font-medium text-slate-600 mb-1">Nome do Produto</label>
                    <input 
                        type="text" 
                        id="product_name" 
                        name="product_name" 
                        class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-slate-500 focus:outline-none" 
                        value="<?php echo htmlspecialchars($produto['nome_produto']); ?>"
                        required
                    >
                </div>
                <div>
                    <label for="unit_measure" class="block text-sm font-medium text-slate-600 mb-1">Unidade de Medida</label>
                    <input 
                        type="text" 
                        id="unit_measure" 
                        name="unit_measure" 
                        class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-slate-500 focus:outline-none" 
                        placeholder="Ex: Unidades, Litros, Kg" 
                        value="<?php echo htmlspecialchars($produto['unidade_medida']); ?>"
                        required
                    >
                </div>
                <div>
                    <label for="quantity_stock" class="block text-sm font-medium text-slate-600 mb-1">Quantidade em Estoque</label>
                    <input 
                        type="number" 
                        id="quantity_stock" 
                        name="quantity_stock" 
                        class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-slate-500 focus:outline-none" 
                        value="<?php echo htmlspecialchars($produto['quantidade_estoque']); ?>" 
                        required
                    >
                </div>
                 <!-- NOVO CAMPO ADICIONADO -->
                <div>
                    <label for="nivel_minimo" class="block text-sm font-medium text-slate-600 mb-1">Nível Mínimo de Stock</label>
                    <input 
                        type="number" 
                        id="nivel_minimo" 
                        name="nivel_minimo" 
                        class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-slate-500 focus:outline-none" 
                        value="<?php echo htmlspecialchars($produto['nivel_minimo']); ?>" 
                        required
                    >
                </div>
            </div>
            
            <div class="mt-8 flex justify-end gap-4">
                <a href="dashboard.php" class="bg-slate-200 text-slate-800 font-bold py-2 px-6 rounded-md hover:bg-slate-300 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="bg-slate-800 text-white font-bold py-2 px-6 rounded-md hover:bg-slate-700 transition-colors">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$conexao->close();
include 'includes/footer.php';
?>
