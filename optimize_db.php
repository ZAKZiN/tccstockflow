<?php

$dbPath = __DIR__ . '/database.sqlite';
if (!file_exists($dbPath)) {
    die("Database not found.\n");
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    -- Otimização: Índices Estruturais (SaaS Ready)
    CREATE INDEX IF NOT EXISTS idx_produtos_codigo_barras ON produtos(codigo_barras);
    CREATE INDEX IF NOT EXISTS idx_produtos_categoria ON produtos(id_categoria);
    
    CREATE INDEX IF NOT EXISTS idx_vendas_data ON vendas(data_venda);
    CREATE INDEX IF NOT EXISTS idx_vendas_status ON vendas(status);
    
    CREATE INDEX IF NOT EXISTS idx_vendas_itens_venda ON vendas_itens(id_venda);
    CREATE INDEX IF NOT EXISTS idx_vendas_itens_produto ON vendas_itens(id_produto);
    
    CREATE INDEX IF NOT EXISTS idx_caixas_usuario ON caixas(id_usuario);
    CREATE INDEX IF NOT EXISTS idx_caixas_status ON caixas(status);
    
    CREATE INDEX IF NOT EXISTS idx_caixa_movimentacoes_caixa ON caixa_movimentacoes(id_caixa);
    CREATE INDEX IF NOT EXISTS idx_caixa_movimentacoes_tipo ON caixa_movimentacoes(tipo);
    
    CREATE INDEX IF NOT EXISTS idx_contas_receber_venda ON contas_receber(id_venda);
    CREATE INDEX IF NOT EXISTS idx_contas_receber_cliente ON contas_receber(id_cliente);
    CREATE INDEX IF NOT EXISTS idx_contas_receber_status ON contas_receber(status);

    CREATE INDEX IF NOT EXISTS idx_requisicoes_status ON requisicoes(status);
    CREATE INDEX IF NOT EXISTS idx_compras_requisicao ON compras(id_requisicao);
    ";

    $db->exec($sql);
    echo "Otimizacao do banco concluida com sucesso (Indices criados)!\n";
} catch (Exception $e) {
    echo "Erro ao otimizar banco: " . $e->getMessage() . "\n";
}
