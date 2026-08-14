<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class DashboardController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }
        
        $db = Database::getConnection();
        
        // Faturamento Hoje
        $stmtFaturamento = $db->query("
            SELECT SUM(valor_total) 
            FROM vendas 
            WHERE date(data_venda) = date('now', 'localtime')
        ");
        $faturamentoHoje = $stmtFaturamento->fetchColumn() ?: 0;
        
        // Vendas Hoje
        $stmtVendas = $db->query("
            SELECT COUNT(*) 
            FROM vendas 
            WHERE date(data_venda) = date('now', 'localtime')
        ");
        $vendasHoje = $stmtVendas->fetchColumn();
        
        // Estoque Crítico
        $stmtEstoque = $db->query("SELECT COUNT(*) FROM produtos WHERE quantidade_estoque <= estoque_minimo");
        $estoqueCritico = $stmtEstoque->fetchColumn();
        
        // Chart 1: Faturamento por Mês (Current Year)
        $stmtChart1 = $db->query("
            SELECT cast(strftime('%m', data_venda) as integer) as mes, SUM(valor_total) as total 
            FROM vendas 
            WHERE strftime('%Y', data_venda) = strftime('%Y', 'now', 'localtime') 
            GROUP BY mes ORDER BY mes
        ");
        
        $mesesData = array_fill(1, 12, 0);
        while($row = $stmtChart1->fetch(PDO::FETCH_ASSOC)) {
            $mesesData[(int)$row['mes']] = (float)$row['total'];
        }
        $chartMensal = array_values($mesesData);
        
        // Chart 2: Produtos mais vendidos
        $stmtChart2 = $db->query("
            SELECT p.nome_produto, SUM(vi.quantidade) as total 
            FROM vendas_itens vi
            JOIN produtos p ON vi.id_produto = p.id_produto
            GROUP BY p.nome_produto
            ORDER BY total DESC
            LIMIT 5
        ");
        
        $produtosNomes = [];
        $produtosTotais = [];
        while($row = $stmtChart2->fetch(PDO::FETCH_ASSOC)) {
            $produtosNomes[] = $row['nome_produto'];
            $produtosTotais[] = (int)$row['total'];
        }
        
        // Lista detalhada dos produtos mais vendidos
        $stmtTopProdutos = $db->query("
            SELECT p.id_produto, p.nome_produto, SUM(vi.quantidade) as qtd_vendida, SUM(vi.quantidade * vi.preco_unitario) as valor_gerado
            FROM vendas_itens vi
            JOIN produtos p ON vi.id_produto = p.id_produto
            GROUP BY p.id_produto, p.nome_produto
            ORDER BY qtd_vendida DESC
            LIMIT 10
        ");
        $topProdutosList = $stmtTopProdutos->fetchAll(PDO::FETCH_ASSOC);
        
        // Produtos Vencendo em 7 dias
        $stmtVencendo = $db->query("SELECT nome_produto, data_validade, quantidade_estoque 
                                    FROM produtos 
                                    WHERE data_validade IS NOT NULL 
                                    AND data_validade <= date('now', '+7 days') 
                                    AND quantidade_estoque > 0
                                    ORDER BY data_validade ASC");
        $vencendo = $stmtVencendo->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = [
            'faturamento_hoje' => $faturamentoHoje,
            'vendas_hoje' => $vendasHoje,
            'estoque_critico' => $estoqueCritico,
            'chart_mensal' => json_encode($chartMensal),
            'chart_top_labels' => json_encode($produtosNomes),
            'chart_top_data' => json_encode($produtosTotais),
            'top_produtos_list' => $topProdutosList,
            'vencendo' => $vencendo
        ];
        
        $this->view('dashboard', ['stats' => $stats]);
    }
}
