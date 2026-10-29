<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class DashboardController extends Controller {
    
    public function index() {
        $db = Database::getConnection();
        $hoje = date('Y-m-d');
        $anoAtual = date('Y');
        $limiteValidade = date('Y-m-d', strtotime('+7 days'));
        
        // Faturamento Hoje (horário local de Brasília)
        $stmtFaturamento = $db->prepare("
            SELECT COALESCE(SUM(valor_total), 0) 
            FROM vendas 
            WHERE status = 'Concluída' AND substr(data_venda, 1, 10) = ?
        ");
        $stmtFaturamento->execute([$hoje]);
        $faturamentoHoje = (float)$stmtFaturamento->fetchColumn();
        
        // Margem Bruta dos Itens Hoje - Descontos Concedidos Hoje = Lucro Líquido Hoje
        $stmtMargemItens = $db->prepare("
            SELECT COALESCE(SUM((vi.preco_unitario - IFNULL(p.preco_custo, 0)) * vi.quantidade), 0)
            FROM vendas_itens vi
            JOIN produtos p ON vi.id_produto = p.id_produto
            JOIN vendas v ON vi.id_venda = v.id_venda
            WHERE v.status = 'Concluída' AND substr(v.data_venda, 1, 10) = ?
        ");
        $stmtMargemItens->execute([$hoje]);
        $margemBrutaHoje = (float)$stmtMargemItens->fetchColumn();

        $stmtDescontos = $db->prepare("
            SELECT COALESCE(SUM(IFNULL(desconto, 0)), 0)
            FROM vendas
            WHERE status = 'Concluída' AND substr(data_venda, 1, 10) = ?
        ");
        $stmtDescontos->execute([$hoje]);
        $descontosHoje = (float)$stmtDescontos->fetchColumn();

        $lucroHoje = round($margemBrutaHoje - $descontosHoje, 2);
        
        // Vendas Hoje
        $stmtVendas = $db->prepare("
            SELECT COUNT(*) 
            FROM vendas 
            WHERE status = 'Concluída' AND substr(data_venda, 1, 10) = ?
        ");
        $stmtVendas->execute([$hoje]);
        $vendasHoje = (int)$stmtVendas->fetchColumn();
        
        // Estoque Crítico
        $stmtEstoque = $db->query("SELECT COUNT(*) FROM produtos WHERE quantidade_estoque <= IFNULL(estoque_minimo, 0)");
        $estoqueCritico = (int)$stmtEstoque->fetchColumn();

        $stmtEstoqueList = $db->query("SELECT nome_produto, quantidade_estoque, estoque_minimo FROM produtos WHERE quantidade_estoque <= IFNULL(estoque_minimo, 0) ORDER BY quantidade_estoque ASC");
        $estoqueCriticoList = $stmtEstoqueList->fetchAll(PDO::FETCH_ASSOC);
        
        // Chart 1: Faturamento por Mês (Ano Atual)
        $stmtChart1 = $db->prepare("
            SELECT CAST(substr(data_venda, 6, 2) AS INTEGER) as mes, SUM(valor_total) as total 
            FROM vendas 
            WHERE status = 'Concluída' AND substr(data_venda, 1, 4) = ? 
            GROUP BY mes ORDER BY mes
        ");
        $stmtChart1->execute([$anoAtual]);
        
        $mesesData = array_fill(1, 12, 0.0);
        while ($row = $stmtChart1->fetch(PDO::FETCH_ASSOC)) {
            $mesIdx = (int)$row['mes'];
            if ($mesIdx >= 1 && $mesIdx <= 12) {
                $mesesData[$mesIdx] = round((float)$row['total'], 2);
            }
        }
        $chartMensal = array_values($mesesData);
        
        // Chart 2: Produtos mais vendidos
        $stmtChart2 = $db->query("
            SELECT p.nome_produto, SUM(vi.quantidade) as total 
            FROM vendas_itens vi
            JOIN vendas v ON vi.id_venda = v.id_venda
            JOIN produtos p ON vi.id_produto = p.id_produto
            WHERE v.status = 'Concluída'
            GROUP BY p.id_produto, p.nome_produto
            ORDER BY total DESC
            LIMIT 5
        ");
        
        $produtosNomes = [];
        $produtosTotais = [];
        while ($row = $stmtChart2->fetch(PDO::FETCH_ASSOC)) {
            $produtosNomes[] = $row['nome_produto'];
            $produtosTotais[] = round((float)$row['total'], 3);
        }
        
        // Ranking Top 10 Produtos
        $stmtTopProdutos = $db->query("
            SELECT p.id_produto, p.nome_produto, SUM(vi.quantidade) as qtd_vendida, SUM(vi.quantidade * vi.preco_unitario) as valor_gerado
            FROM vendas_itens vi
            JOIN vendas v ON vi.id_venda = v.id_venda
            JOIN produtos p ON vi.id_produto = p.id_produto
            WHERE v.status = 'Concluída'
            GROUP BY p.id_produto, p.nome_produto
            ORDER BY qtd_vendida DESC
            LIMIT 10
        ");
        $topProdutosList = $stmtTopProdutos->fetchAll(PDO::FETCH_ASSOC);
        
        // Produtos Vencendo em até 7 dias (ou já vencidos com saldo)
        $stmtVencendo = $db->prepare("
            SELECT nome_produto, data_validade, quantidade_estoque 
            FROM produtos 
            WHERE data_validade IS NOT NULL 
            AND data_validade != '' 
            AND data_validade <= ? 
            AND quantidade_estoque > 0
            ORDER BY data_validade ASC
        ");
        $stmtVencendo->execute([$limiteValidade]);
        $vencendo = $stmtVencendo->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = [
            'faturamento_hoje' => $faturamentoHoje,
            'lucro_hoje' => $lucroHoje,
            'vendas_hoje' => $vendasHoje,
            'estoque_critico' => $estoqueCritico,
            'chart_mensal' => json_encode($chartMensal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            'chart_top_labels' => json_encode($produtosNomes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            'chart_top_data' => json_encode($produtosTotais, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            'top_produtos_list' => $topProdutosList,
            'vencendo' => $vencendo,
            'estoque_critico_list' => $estoqueCriticoList
        ];
        
        $this->view('dashboard', ['stats' => $stats]);
    }
}
