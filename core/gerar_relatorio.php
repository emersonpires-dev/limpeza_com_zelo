<?php
// ======================================================================
// ARQUIVO ATUALIZADO: core/gerar_relatorio.php
// Objetivo: Corrigir o erro "Alguns dados já foram enviados" na geração de PDF.
// ======================================================================

// session_start(); // REMOVIDO: Esta linha era a causa do erro. 'verificar_sessao.php' já inicia a sessão.

require_once 'verificar_sessao.php'; // Este ficheiro já inicia a sessão.
require_once 'conexao.php';
require_once 'fpdf186/fpdf.php'; // Caminho para a biblioteca FPDF

// Pega os parâmetros da URL
$formato = isset($_GET['formato']) ? $_GET['formato'] : 'csv';
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$end_date_query = $end_date . ' 23:59:59';

// Mesma consulta da página de relatórios
$query = "SELECT m.data_movimentacao, p.nome_produto, m.tipo_movimentacao, m.quantidade, u.nome as nome_usuario
          FROM movimentacoes m
          JOIN produtos p ON m.produto_id = p.id
          JOIN usuarios u ON m.usuario_id = u.id
          WHERE m.data_movimentacao BETWEEN ? AND ?
          ORDER BY m.data_movimentacao DESC";

$stmt = $conexao->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$resultado = $stmt->get_result();
$dados = $resultado->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conexao->close();

// --- GERAÇÃO DO ARQUIVO ---

if ($formato == 'csv') {
    // --- LÓGICA PARA GERAR CSV ---
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=relatorio_'.date('Y-m-d').'.csv');
    
    $output = fopen('php://output', 'w');
    
    // Cabeçalho do CSV
    fputcsv($output, ['Data', 'Produto', 'Tipo', 'Quantidade', 'Usuario'], ';');
    
    // Dados
    foreach ($dados as $linha) {
        $linha_formatada = [
            date('d/m/Y H:i', strtotime($linha['data_movimentacao'])),
            $linha['nome_produto'],
            ucfirst($linha['tipo_movimentacao']),
            ($linha['tipo_movimentacao'] == 'entrada' ? '+' : '-') . $linha['quantidade'],
            $linha['nome_usuario']
        ];
        fputcsv($output, $linha_formatada, ';');
    }
    
    fclose($output);
    exit();

} elseif ($formato == 'pdf') {
    // --- LÓGICA PARA GERAR PDF ---
    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial','B',12);
            $this->Cell(0,10,'Relatorio de Movimentacoes',0,1,'C');
            $this->Ln(5);
        }
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
        }
    }

    $pdf = new PDF('P', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',9);

    // Cabeçalho da Tabela
    $pdf->Cell(35, 7, 'Data', 1, 0, 'C');
    $pdf->Cell(70, 7, 'Produto', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Tipo', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Quantidade', 1, 0, 'C');
    $pdf->Cell(40, 7, 'Usuario', 1, 1, 'C');

    $pdf->SetFont('Arial','',9);
    // Dados da Tabela
    foreach ($dados as $linha) {
        $nome_produto = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $linha['nome_produto']);
        $nome_usuario = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $linha['nome_usuario']);

        $pdf->Cell(35, 6, date('d/m/Y H:i', strtotime($linha['data_movimentacao'])), 1);
        $pdf->Cell(70, 6, $nome_produto, 1);
        $pdf->Cell(20, 6, ucfirst($linha['tipo_movimentacao']), 1, 0, 'C');
        $pdf->Cell(25, 6, ($linha['tipo_movimentacao'] == 'entrada' ? '+' : '-') . $linha['quantidade'], 1, 0, 'C');
        $pdf->Cell(40, 6, $nome_usuario, 1, 1);
    }

    $pdf->Output('D', 'relatorio_'.date('Y-m-d').'.pdf');
    exit();
}
