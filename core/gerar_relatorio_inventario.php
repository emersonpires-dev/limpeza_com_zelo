<?php
// ======================================================================
// NOVO ARQUIVO: core/gerar_relatorio_inventario.php
// Objetivo: Gerar um relatório PDF ou CSV com a lista completa de stock.
// ======================================================================

require_once 'verificar_sessao.php';
require_once 'conexao.php';
require_once 'fpdf186/fpdf.php'; // Caminho para a biblioteca FPDF

// Pega o formato da URL (pdf ou csv)
$formato = isset($_GET['formato']) ? $_GET['formato'] : 'csv';

// Query para buscar todos os produtos e suas quantidades
$query = "SELECT nome_produto, quantidade_estoque, unidade_medida, nivel_minimo 
          FROM produtos 
          ORDER BY nome_produto ASC";

$stmt = $conexao->prepare($query);
$stmt->execute();
$resultado = $stmt->get_result();
$dados = $resultado->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conexao->close();

// --- GERAÇÃO DO ARQUIVO ---

if ($formato == 'csv') {
    // --- LÓGICA PARA GERAR CSV ---
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=relatorio_inventario_'.date('Y-m-d').'.csv');
    
    $output = fopen('php://output', 'w');
    
    // Cabeçalho do CSV
    fputcsv($output, ['Produto', 'Qtd. em Stock', 'Unidade de Medida', 'Nivel Minimo'], ';');
    
    // Dados
    foreach ($dados as $linha) {
        fputcsv($output, $linha, ';');
    }
    
    fclose($output);
    exit();

} elseif ($formato == 'pdf') {
    // --- LÓGICA PARA GERAR PDF ---
    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial','B',12);
            $this->Cell(0,10,'Relatorio de Inventario de Stock',0,1,'C');
            $this->SetFont('Arial','',8);
            $this->Cell(0,5,'Emitido em: '.date('d/m/Y H:i'),0,1,'C');
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
    $pdf->SetFont('Arial','B',10);

    // Cabeçalho da Tabela
    $pdf->Cell(90, 7, 'Produto', 1, 0, 'L');
    $pdf->Cell(35, 7, 'Qtd. em Stock', 1, 0, 'C');
    $pdf->Cell(35, 7, 'Unidade de Medida', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Nivel Minimo', 1, 1, 'C');

    $pdf->SetFont('Arial','',9);
    // Dados da Tabela
    foreach ($dados as $linha) {
        $nome_produto = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $linha['nome_produto']);
        
        $pdf->Cell(90, 6, $nome_produto, 1);
        $pdf->Cell(35, 6, $linha['quantidade_estoque'], 1, 0, 'C');
        $pdf->Cell(35, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $linha['unidade_medida']), 1, 0, 'C');
        $pdf->Cell(30, 6, $linha['nivel_minimo'], 1, 1, 'C');
    }

    $pdf->Output('D', 'relatorio_inventario_'.date('Y-m-d').'.pdf');
    exit();
}