<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
use App\Helpers\CryptHelper;

$db = Database::getConnection();
$db->exec("PRAGMA foreign_keys = ON;");

echo "Populando banco de dados com dados de demonstracao...\n";

// 1. Setores
$db->exec("INSERT OR IGNORE INTO setores (id_setor, nome_setor) VALUES 
    (1, 'Administração / Diretoria'),
    (2, 'Tecnologia da Informação (TI)'),
    (3, 'Atendimento & Frente de Loja'),
    (4, 'Logística & Almoxarifado'),
    (5, 'Operações e Manutenção');
");

// 2. Categorias
$db->exec("INSERT OR IGNORE INTO categorias (id_categoria, nome_categoria) VALUES 
    (1, 'Bebidas e Cafeteria'),
    (2, 'Insumos de Higiene e Limpeza'),
    (3, 'Suprimentos de Informática & Escritório'),
    (4, 'Alimentos e Descartáveis'),
    (5, 'Materiais de Manutenção e Reparo');
");

// 3. Fornecedores
$fornecedores = [
    ['Distribuidora Aliança de Bebidas Ltda', '12.345.678/0001-90', 'vendas@aliancabebidas.com.br', '(19) 3806-1122'],
    ['Suprimax Papelaria e Informática', '98.765.432/0001-21', 'comercial@suprimax.com.br', '(19) 3805-4433'],
    ['Higieniza Químicos & Limpeza Industrial', '45.678.901/0001-44', 'contato@higieniza.com.br', '(19) 3814-7788'],
    ['Atacadão Central de Embalagens', '23.456.789/0001-55', 'pedidos@atacadaocentral.com.br', '(19) 3862-9900'],
];

foreach ($fornecedores as $f) {
    $stmt = $db->prepare("SELECT id_fornecedor FROM fornecedores WHERE nome_fantasia = ?");
    $stmt->execute([$f[0]]);
    if (!$stmt->fetch()) {
        $stmtIns = $db->prepare("INSERT INTO fornecedores (nome_fantasia, cnpj, email, telefone, criado_em) VALUES (?, ?, ?, ?, datetime('now', 'localtime'))");
        $stmtIns->execute([$f[0], $f[1], CryptHelper::encrypt($f[2]), CryptHelper::encrypt($f[3])]);
    }
}

// 4. Clientes
$clientes = [
    ['Carlos Eduardo Silva', '19991234567', 'carlos.silva@email.com'],
    ['Mariana Souza Costa', '19988776655', 'mariana.costa@email.com'],
    ['Roberto Almeida Prado', '19977665544', 'roberto.prado@email.com'],
    ['Fernanda Lima Vasconcelos', '19981223344', 'fernanda.lima@email.com'],
    ['Lucas Martins Ferreira', '19992334455', 'lucas.martins@email.com'],
];

foreach ($clientes as $c) {
    $stmt = $db->prepare("SELECT id_cliente FROM clientes WHERE nome = ?");
    $stmt->execute([$c[0]]);
    if (!$stmt->fetch()) {
        $stmtIns = $db->prepare("INSERT INTO clientes (nome, telefone, email, criado_em) VALUES (?, ?, ?, datetime('now', 'localtime'))");
        $stmtIns->execute([$c[0], CryptHelper::encrypt($c[1]), CryptHelper::encrypt($c[2])]);
    }
}

// 5. Produtos
$produtos = [
    ['Café em Grãos Gourmet 1kg', '7891000100011', 'CAF-GRAO-01', 1, 32.50, 58.90, 42.0, 10.0, 'LT-2026C1', '2027-05-15'],
    ['Refrigerante Lata 350ml Variados', '7891000100028', 'REF-LATA-02', 1, 2.80, 6.00, 85.0, 20.0, 'LT-2026R2', '2026-12-30'],
    ['Água Mineral sem Gás 500ml', '7891000100035', 'AGU-500M-03', 1, 1.20, 3.50, 110.0, 30.0, 'LT-2026A3', '2027-08-20'],
    ['Papel A4 Chamex 75g Resma 500fls', '7891000200010', 'PAP-A4CH-04', 3, 22.00, 34.90, 18.0, 10.0, 'LT-2026P4', null],
    ['Bobina Térmica PDV 80mm x 40m', '7891000200027', 'BOB-80MM-05', 3, 3.50, 7.50, 3.0, 8.0, 'LT-2026B5', null],
    ['Mouse Óptico USB Ergonômico', '7891000300019', 'MOU-OPTI-06', 3, 18.50, 39.90, 2.0, 5.0, 'LT-2026M6', null],
    ['Detergente Neutro Concentrado 5L', '7891000400018', 'DET-NEUT-07', 2, 14.00, 26.50, 12.0, 6.0, 'LT-2026D7', '2027-03-10'],
    ['Desinfetante Lavanda Galão 5L', '7891000400025', 'DES-LAVA-08', 2, 16.50, 29.90, 8.0, 5.0, 'LT-2026D8', '2027-04-12'],
    ['Copo Descartável 200ml c/ 100 un', '7891000500017', 'COP-200M-09', 4, 4.20, 8.90, 35.0, 15.0, 'LT-2026C9', null],
    ['Fita Adesiva Transparente 45mmx50m', '7891000500024', 'FIT-ADES-10', 5, 2.90, 6.50, 1.0, 5.0, 'LT-2026F1', null],
    ['Sanduíche Natural Artesanal Frango', '2000010000001', 'LAN-NATU-11', 4, 6.00, 14.00, 15.0, 5.0, 'LT-2026L1', '2026-11-15'],
];

foreach ($produtos as $p) {
    $stmt = $db->prepare("SELECT id_produto FROM produtos WHERE codigo_barras = ?");
    $stmt->execute([$p[1]]);
    if (!$stmt->fetch()) {
        $stmtIns = $db->prepare("INSERT INTO produtos 
            (nome_produto, codigo_barras, sku, id_categoria, preco_custo, preco_venda, quantidade_estoque, estoque_minimo, lote, data_validade, atualizado_em) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now', 'localtime'))");
        $stmtIns->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9]]);
    }
}

// 6. Turno de Caixa
$stmtCaixa = $db->query("SELECT id_caixa FROM caixas WHERE id_usuario = 1 AND status = 'Aberto'");
$idCaixa = $stmtCaixa->fetchColumn();
if (!$idCaixa) {
    $db->exec("INSERT INTO caixas (id_usuario, data_abertura, saldo_inicial, status) VALUES (1, datetime('now', 'localtime', '-4 hours'), 150.00, 'Aberto')");
    $idCaixa = (int)$db->lastInsertId();
    $db->exec("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, metodo_pagamento, descricao, data_movimentacao) VALUES ({$idCaixa}, 'Abertura', 150.00, 'Dinheiro', 'Fundo de Troco Inicial', datetime('now', 'localtime', '-4 hours'))");
}

// 7. Vendas
$checkVendas = (int)$db->query("SELECT COUNT(*) FROM vendas")->fetchColumn();
if ($checkVendas < 5) {
    $db->exec("INSERT INTO vendas (id_cliente, valor_total, desconto, metodo_pagamento, status, data_venda) VALUES (1, 62.40, 0.00, 'Dinheiro', 'Concluída', datetime('now', 'localtime', '-3 hours'))");
    $v1 = (int)$db->lastInsertId();
    $db->exec("INSERT INTO vendas_itens (id_venda, id_produto, quantidade, preco_unitario) VALUES ({$v1}, 1, 1, 58.90), ({$v1}, 3, 1, 3.50)");
    $db->exec("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, metodo_pagamento, descricao, data_movimentacao) VALUES ({$idCaixa}, 'Venda', 62.40, 'Dinheiro', 'Venda #{$v1} - Dinheiro', datetime('now', 'localtime', '-3 hours'))");

    $db->exec("INSERT INTO vendas (id_cliente, valor_total, desconto, metodo_pagamento, status, data_venda) VALUES (2, 40.90, 0.00, 'Pix', 'Concluída', datetime('now', 'localtime', '-2 hours'))");
    $v2 = (int)$db->lastInsertId();
    $db->exec("INSERT INTO vendas_itens (id_venda, id_produto, quantidade, preco_unitario) VALUES ({$v2}, 4, 1, 34.90), ({$v2}, 2, 1, 6.00)");
    $db->exec("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, metodo_pagamento, descricao, data_movimentacao) VALUES ({$idCaixa}, 'Venda', 40.90, 'Pix', 'Venda #{$v2} - Pix', datetime('now', 'localtime', '-2 hours'))");

    $db->exec("INSERT INTO vendas (id_cliente, valor_total, desconto, metodo_pagamento, status, data_venda) VALUES (3, 70.00, 2.90, 'Cartão de Débito', 'Concluída', datetime('now', 'localtime', '-1 hours'))");
    $v3 = (int)$db->lastInsertId();
    $db->exec("INSERT INTO vendas_itens (id_venda, id_produto, quantidade, preco_unitario) VALUES ({$v3}, 1, 1, 58.90), ({$v3}, 11, 1, 14.00)");
    $db->exec("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, metodo_pagamento, descricao, data_movimentacao) VALUES ({$idCaixa}, 'Venda', 70.00, 'Cartão de Débito', 'Venda #{$v3} - Cartão de Débito', datetime('now', 'localtime', '-1 hours'))");

    $db->exec("INSERT INTO vendas (id_cliente, valor_total, desconto, metodo_pagamento, status, data_venda) VALUES (2, 85.40, 0.00, 'Fiado (Caderninho)', 'Concluída', datetime('now', 'localtime', '-30 minutes'))");
    $v4 = (int)$db->lastInsertId();
    $db->exec("INSERT INTO vendas_itens (id_venda, id_produto, quantidade, preco_unitario) VALUES ({$v4}, 1, 1, 58.90), ({$v4}, 7, 1, 26.50)");
    $db->exec("INSERT INTO contas_receber (id_venda, id_cliente, valor_total, valor_pago, status, data_vencimento, criado_em) VALUES ({$v4}, 2, 85.40, 0.00, 'Pendente', date('now', '+30 days'), datetime('now', 'localtime', '-30 minutes'))");

    $db->exec("INSERT INTO vendas (id_cliente, valor_total, desconto, metodo_pagamento, status, data_venda) VALUES (3, 59.80, 0.00, 'Fiado (Caderninho)', 'Concluída', datetime('now', 'localtime', '-15 minutes'))");
    $v5 = (int)$db->lastInsertId();
    $db->exec("INSERT INTO vendas_itens (id_venda, id_produto, quantidade, preco_unitario) VALUES ({$v5}, 8, 2, 29.90)");
    $db->exec("INSERT INTO contas_receber (id_venda, id_cliente, valor_total, valor_pago, status, data_vencimento, criado_em) VALUES ({$v5}, 3, 59.80, 0.00, 'Pendente', date('now', '+30 days'), datetime('now', 'localtime', '-15 minutes'))");

    $db->exec("INSERT INTO caixa_movimentacoes (id_caixa, tipo, valor, metodo_pagamento, descricao, data_movimentacao) VALUES ({$idCaixa}, 'Suprimento', 50.00, 'Dinheiro', 'Reforço de Troco', datetime('now', 'localtime', '-2 hours'))");
}

// 8. Requisições
$checkReq = (int)$db->query("SELECT COUNT(*) FROM requisicoes")->fetchColumn();
if ($checkReq < 3) {
    $db->exec("INSERT INTO requisicoes (solicitante, id_setor, material, quantidade, prioridade, justificativa, status, data_solicitacao, atualizado_em) VALUES 
        ('Prof. Isaque Santos Lessa', 2, 'Papel A4 Chamex 75g Resma 500fls', 20, 'Alta', 'Reposição para impressoras do laboratório', 'Compra Efetuada', datetime('now', 'localtime', '-2 days'), datetime('now', 'localtime', '-1 day'))");
    $r1 = (int)$db->lastInsertId();
    $db->exec("INSERT INTO historico_requisicoes (id_requisicao, acao, responsavel, data_acao) VALUES 
        ({$r1}, 'Abertura de Requisição', 'Prof. Isaque Santos Lessa', datetime('now', 'localtime', '-2 days')),
        ({$r1}, 'Aprovada pela Coordenação', 'Coordenador TI', datetime('now', 'localtime', '-36 hours')),
        ({$r1}, 'Ordem de Compra Emitida', 'Estoquista Central', datetime('now', 'localtime', '-1 day'))");
    $db->exec("INSERT INTO compras (id_requisicao, id_fornecedor, valor_total, data_compra) VALUES 
        ({$r1}, 2, 440.00, datetime('now', 'localtime', '-1 day'))");

    $db->exec("INSERT INTO requisicoes (solicitante, id_setor, material, quantidade, prioridade, justificativa, status, data_solicitacao, atualizado_em) VALUES 
        ('Ana Carolina Tomaz', 3, 'Café em Grãos Gourmet 1kg', 10, 'Média', 'Abastecimento da copa de atendimento', 'Pendente Coordenador', datetime('now', 'localtime', '-5 hours'), datetime('now', 'localtime', '-5 hours'))");
    $r2 = (int)$db->lastInsertId();
    $db->exec("INSERT INTO historico_requisicoes (id_requisicao, acao, responsavel, data_acao) VALUES 
        ({$r2}, 'Abertura de Requisição', 'Ana Carolina Tomaz', datetime('now', 'localtime', '-5 hours'))");

    $db->exec("INSERT INTO requisicoes (solicitante, id_setor, material, quantidade, prioridade, justificativa, status, data_solicitacao, atualizado_em) VALUES 
        ('Rafael Barboza Rosa', 5, 'Fita Adesiva Transparente 45mmx50m', 15, 'Urgente', 'Estoque zerando nas linhas de expedição', 'Pendente Almoxarifado', datetime('now', 'localtime', '-1 day'), datetime('now', 'localtime', '-8 hours'))");
    $r3 = (int)$db->lastInsertId();
    $db->exec("INSERT INTO historico_requisicoes (id_requisicao, acao, responsavel, data_acao) VALUES 
        ({$r3}, 'Abertura de Requisição', 'Rafael Barboza Rosa', datetime('now', 'localtime', '-1 day')),
        ({$r3}, 'Aprovada pela Coordenação', 'Coordenação Operacional', datetime('now', 'localtime', '-8 hours'))");
}

echo "Dados de demonstracao carregados com sucesso!\n";

