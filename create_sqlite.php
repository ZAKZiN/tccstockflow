<?php

// Remove o banco antigo para recriar do zero com o novo schema
$dbPath = __DIR__ . '/database.sqlite';
if (file_exists($dbPath)) {
    unlink($dbPath);
}

$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "
CREATE TABLE IF NOT EXISTS setores (
    id_setor INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_setor TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    login TEXT NOT NULL UNIQUE,
    senha TEXT NOT NULL,
    nivel_acesso TEXT NOT NULL,
    id_setor INTEGER,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    telefone TEXT,
    email TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_categoria TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS produtos (
    id_produto INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_produto TEXT NOT NULL,
    codigo_barras TEXT UNIQUE,
    sku TEXT UNIQUE,
    id_categoria INTEGER,
    preco_custo REAL NOT NULL DEFAULT 0,
    preco_venda REAL NOT NULL DEFAULT 0,
    quantidade_estoque INTEGER NOT NULL DEFAULT 0,
    estoque_minimo INTEGER NOT NULL DEFAULT 5,
    lote TEXT,
    data_validade DATE,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vendas (
    id_venda INTEGER PRIMARY KEY AUTOINCREMENT,
    id_cliente INTEGER,
    valor_total REAL NOT NULL,
    metodo_pagamento TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'Concluída',
    data_venda DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contas_receber (
    id_conta INTEGER PRIMARY KEY AUTOINCREMENT,
    id_venda INTEGER NOT NULL,
    id_cliente INTEGER NOT NULL,
    valor_total REAL NOT NULL,
    valor_pago REAL NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'Pendente',
    data_vencimento DATE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS caixas (
    id_caixa INTEGER PRIMARY KEY AUTOINCREMENT,
    id_usuario INTEGER NOT NULL,
    data_abertura DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_fechamento DATETIME NULL,
    saldo_inicial REAL DEFAULT 0.00,
    saldo_final REAL NULL,
    status TEXT DEFAULT 'Aberto'
);

CREATE TABLE IF NOT EXISTS caixa_movimentacoes (
    id_movimentacao INTEGER PRIMARY KEY AUTOINCREMENT,
    id_caixa INTEGER NOT NULL,
    tipo TEXT NOT NULL,
    valor REAL NOT NULL,
    descricao TEXT,
    data_movimentacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
    id_movimentacao INTEGER PRIMARY KEY AUTOINCREMENT,
    id_produto INTEGER NOT NULL,
    id_usuario INTEGER,
    tipo TEXT NOT NULL,
    quantidade INTEGER NOT NULL,
    observacao TEXT,
    data_movimentacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vendas_itens (
    id_venda_item INTEGER PRIMARY KEY AUTOINCREMENT,
    id_venda INTEGER NOT NULL,
    id_produto INTEGER NOT NULL,
    quantidade INTEGER NOT NULL,
    preco_unitario REAL NOT NULL
);

CREATE TABLE IF NOT EXISTS requisicoes (
    id_requisicao INTEGER PRIMARY KEY AUTOINCREMENT,
    solicitante TEXT NOT NULL,
    id_setor INTEGER,
    material TEXT NOT NULL,
    quantidade INTEGER NOT NULL,
    prioridade TEXT NOT NULL DEFAULT 'Média',
    justificativa TEXT,
    status TEXT NOT NULL DEFAULT 'Pendente Coordenador',
    data_solicitacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS fornecedores (
    id_fornecedor INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_fantasia TEXT NOT NULL,
    cnpj TEXT UNIQUE,
    email TEXT,
    telefone TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS compras (
    id_compra INTEGER PRIMARY KEY AUTOINCREMENT,
    id_requisicao INTEGER NOT NULL,
    id_fornecedor INTEGER,
    valor_total REAL,
    data_compra DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notificacoes (
    id_notificacao INTEGER PRIMARY KEY AUTOINCREMENT,
    id_usuario INTEGER,
    nivel_destino TEXT,
    titulo TEXT NOT NULL,
    mensagem TEXT NOT NULL,
    lida INTEGER DEFAULT 0,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT OR IGNORE INTO setores (id_setor, nome_setor) VALUES (1, 'Administrativo'), (2, 'Caixa'), (3, 'Estoque');
INSERT OR IGNORE INTO usuarios (nome, login, senha, nivel_acesso, id_setor) 
VALUES ('Administrador Master', 'admin', '$2y$12$.HfR4mloLrGDIO8RP4tcvu1f16ZOxa1XBvgAs6eJm80WQTq0Mxjna', 'Administrador', 1);

INSERT OR IGNORE INTO categorias (id_categoria, nome_categoria) VALUES (1, 'Bebidas'), (2, 'Mercearia'), (3, 'Higiene');

INSERT OR IGNORE INTO clientes (nome, telefone) VALUES ('Cliente Padrão (Balcão)', '');

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
echo 'SQLite database recreated successfully with commercial POS schema!';
