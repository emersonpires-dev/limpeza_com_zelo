<?php
// ======================================================================
// ARQUIVO ATUALIZADO: relatorios.php
// Objetivo: Implementar sistema de paginação completo.
// ======================================================================

require_once 'core/verificar_sessao.php';
require_once 'core/conexao.php';

$page_title = 'Relatórios - Controle de Estoque';

// --- LÓGICA DA PAGINAÇÃO E FILTROS ---

// 1. Obter parâmetros da URL ou definir valores padrão
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itens_por_pagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10; // Padrão de 10 itens

// Adiciona a hora final no end_date para incluir o dia todo na busca
$end_date_query = $end_date . ' 23:59:59';

// 2. Contar o total de registos para a paginação (com base nos filtros)
$query_total = "SELECT COUNT(m.id) as total FROM movimentacoes m WHERE m.data_movimentacao BETWEEN ? AND ?";
$stmt_total = $conexao->prepare($query_total);
$stmt_total->bind_param("ss", $start_date, $end_date_query);
$stmt_total->execute();
$resultado_total = $stmt_total->get_result()->fetch_assoc();
$total_registos = $resultado_total['total'];
$total_paginas = ceil($total_registos / $itens_por_pagina);
$stmt_total->close();

// 3. Calcular o OFFSET para a query principal
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// 4. Modificar a query principal com LIMIT e OFFSET
$query = "SELECT 
            m.data_movimentacao,
            p.nome_produto,
            m.tipo_movimentacao,
            m.quantidade,
            u.nome as nome_usuario
          FROM 
            movimentacoes m
          JOIN 
            produtos p ON m.produto_id = p.id
          JOIN 
            usuarios u ON m.usuario_id = u.id
          WHERE 
            m.data_movimentacao BETWEEN ? AND ?
          ORDER BY 
            m.data_movimentacao DESC
          LIMIT ? OFFSET ?";

$stmt = $conexao->prepare($query);
// Adicionados dois "i" para os inteiros de LIMIT e OFFSET
$stmt->bind_param("ssii", $start_date, $end_date_query, $itens_por_pagina, $offset);
$stmt->execute();
$resultado = $stmt->get_result();

include 'includes/header.php';
?>

<div class="bg-white p-8 rounded-lg shadow-lg border">
    <header class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
        <h2 class="text-3xl font-bold">Relatório de Movimentações</h2>
        
        <form method="GET" action="relatorios.php" class="flex flex-col md:flex-row md:flex-wrap md:items-center md:justify-end gap-4">
            <div class="flex items-center gap-2">
                <label for="start_date" class="font-medium shrink-0">De:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="p-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-slate-500 focus:outline-none">
            </div>
            <div class="flex items-center gap-2">
                <label for="end_date" class="font-medium shrink-0">Até:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="p-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-slate-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2">
                 <label for="por_pagina" class="font-medium shrink-0">Itens:</label>
                 <select name="por_pagina" id="por_pagina" class="p-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-slate-500 focus:outline-none">
                    <option value="10" <?php if ($itens_por_pagina == 10) echo 'selected'; ?>>10</option>
                    <option value="15" <?php if ($itens_por_pagina == 15) echo 'selected'; ?>>15</option>
                    <option value="20" <?php if ($itens_por_pagina == 20) echo 'selected'; ?>>20</option>
                 </select>
            </div>
            
            <button type="submit" class="bg-slate-800 text-white font-bold py-2 px-4 rounded-md hover:bg-slate-700 transition-colors w-full md:w-auto justify-center">Filtrar</button>
            
            <div class="relative w-full md:w-auto" id="download-container">
                <button type="button" onclick="toggleDropdown()" class="bg-green-600 text-white font-bold py-2 px-4 rounded-md hover:bg-green-700 transition-colors w-full flex items-center justify-center gap-2">
                    Download
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div id="download-dropdown" class="hidden absolute right-0 mt-2 w-full bg-white rounded-md shadow-lg z-10 border">
                    <a href="core/gerar_relatorio.php?formato=pdf&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Baixar como PDF</a>
                    <a href="core/gerar_relatorio.php?formato=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Baixar como CSV</a>
                </div>
            </div>
        </form>
    </header>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-3 font-semibold">Data</th>
                    <th class="p-3 font-semibold">Produto</th>
                    <th class="p-3 font-semibold">Tipo</th>
                    <th class="p-3 font-semibold">Quantidade</th>
                    <th class="p-3 font-semibold">Usuário</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado->num_rows > 0): ?>
                    <?php while($movimentacao = $resultado->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-slate-50">
                            <td class="p-3"><?php echo date('d/m/Y H:i', strtotime($movimentacao['data_movimentacao'])); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($movimentacao['nome_produto']); ?></td>
                            <td class="p-3">
                                <?php if ($movimentacao['tipo_movimentacao'] == 'entrada'): ?>
                                    <span class="text-green-600 font-semibold">ENTRADA</span>
                                <?php else: ?>
                                    <span class="text-red-600 font-semibold">SAÍDA</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3">
                                <?php 
                                    $sinal = ($movimentacao['tipo_movimentacao'] == 'entrada') ? '+' : '-';
                                    echo $sinal . htmlspecialchars($movimentacao['quantidade']); 
                                ?>
                            </td>
                            <td class="p-3"><?php echo htmlspecialchars($movimentacao['nome_usuario']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="border-b">
                        <td colspan="5" class="p-3 text-center text-slate-500">Nenhuma movimentação encontrada para o período selecionado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex justify-between items-center">
        <span class="text-sm text-slate-500">
            Mostrando <?php echo $resultado->num_rows; ?> de <?php echo $total_registos; ?> registos
        </span>
        
        <?php if ($total_paginas > 1): ?>
            <div class="flex items-center gap-2">
                <?php
                    // Preserva os filtros na URL da paginação
                    $query_params = $_GET;
                    
                    // Link para a página Anterior
                    if ($pagina_atual > 1) {
                        $query_params['pagina'] = $pagina_atual - 1;
                        echo "<a href='?".http_build_query($query_params)."' class='px-3 py-1 border rounded-md hover:bg-slate-100'>&laquo; Anterior</a>";
                    }

                    // Links para as páginas
                    for ($i = 1; $i <= $total_paginas; $i++) {
                        $query_params['pagina'] = $i;
                        $is_active = ($i == $pagina_atual) ? 'bg-slate-800 text-white' : 'hover:bg-slate-100';
                        echo "<a href='?".http_build_query($query_params)."' class='px-3 py-1 border rounded-md {$is_active}'>{$i}</a>";
                    }

                    // Link para a página Seguinte
                    if ($pagina_atual < $total_paginas) {
                        $query_params['pagina'] = $pagina_atual + 1;
                        echo "<a href='?".http_build_query($query_params)."' class='px-3 py-1 border rounded-md hover:bg-slate-100'>Seguinte &raquo;</a>";
                    }
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="assets/js/relatorios.js"></script>

<?php
$stmt->close();
$conexao->close();
include 'includes/footer.php';
?>