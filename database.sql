-- Script SQL para o Sistema StockFlow adaptado para PostgreSQL (Supabase / Neon)
-- Atualizado para conter TODAS as tabelas do sistema comercial PDV

-- =========================================================================
-- ATENÇÃO: Os comandos abaixo irão APAGAR todas as tabelas existentes
-- para garantir que o banco seja recriado limpo e com as colunas corretas.
-- =========================================================================
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS notificacoes;
DROP TABLE IF EXISTS compras;
DROP TABLE IF EXISTS fornecedores;
DROP TABLE IF EXISTS historico_requisicoes;
DROP TABLE IF EXISTS requisicoes;
DROP TABLE IF EXISTS movimentacoes_estoque;
DROP TABLE IF EXISTS caixa_movimentacoes;
DROP TABLE IF EXISTS caixas;
DROP TABLE IF EXISTS contas_receber;
DROP TABLE IF EXISTS vendas_itens;
DROP TABLE IF EXISTS vendas;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS setores;
-- =========================================================================
-- A tabela de Setores
CREATE TABLE IF NOT EXISTS setores (
    id_setor INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_setor VARCHAR(80) NOT NULL UNIQUE
);

-- Tabela de Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(120) NOT NULL,
    login VARCHAR(60) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso VARCHAR(50) NOT NULL,
    id_setor INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_setor) REFERENCES setores(id_setor) ON DELETE SET NULL
);

-- Tabela de Clientes
CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(120) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(120),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Categorias
CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_categoria VARCHAR(80) NOT NULL UNIQUE
);

-- Tabela de Produtos (Completa)
CREATE TABLE IF NOT EXISTS produtos (
    id_produto INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_produto VARCHAR(160) NOT NULL,
    codigo_barras VARCHAR(100) UNIQUE,
    sku VARCHAR(100) UNIQUE,
    id_categoria INT,
    preco_custo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    preco_venda DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantidade_estoque INT NOT NULL DEFAULT 0,
    estoque_minimo INT NOT NULL DEFAULT 5,
    lote VARCHAR(50),
    data_validade DATE,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE SET NULL
);

-- Tabela de Vendas
CREATE TABLE IF NOT EXISTS vendas (
    id_venda INTEGER PRIMARY KEY AUTOINCREMENT,
    id_cliente INT,
    valor_total DECIMAL(10,2) NOT NULL,
    metodo_pagamento VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Concluída',
    data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL
);

-- Tabela de Itens da Venda
CREATE TABLE IF NOT EXISTS vendas_itens (
    id_venda_item INTEGER PRIMARY KEY AUTOINCREMENT,
    id_venda INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venda) REFERENCES vendas(id_venda) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE
);

-- Tabela de Contas a Receber (Fiado)
CREATE TABLE IF NOT EXISTS contas_receber (
    id_conta INTEGER PRIMARY KEY AUTOINCREMENT,
    id_venda INT NOT NULL,
    id_cliente INT NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    valor_pago DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(50) NOT NULL DEFAULT 'Pendente',
    data_vencimento DATE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_venda) REFERENCES vendas(id_venda) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
);

-- Tabela de Caixas
CREATE TABLE IF NOT EXISTS caixas (
    id_caixa INTEGER PRIMARY KEY AUTOINCREMENT,
    id_usuario INT NOT NULL,
    data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_fechamento TIMESTAMP NULL,
    saldo_inicial DECIMAL(10,2) DEFAULT 0.00,
    saldo_final DECIMAL(10,2) NULL,
    status VARCHAR(50) DEFAULT 'Aberto',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabela de Movimentacoes do Caixa
CREATE TABLE IF NOT EXISTS caixa_movimentacoes (
    id_movimentacao INTEGER PRIMARY KEY AUTOINCREMENT,
    id_caixa INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    descricao TEXT,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_caixa) REFERENCES caixas(id_caixa) ON DELETE CASCADE
);

-- Tabela de Movimentacoes de Estoque (Kardex)
CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
    id_movimentacao INTEGER PRIMARY KEY AUTOINCREMENT,
    id_produto INT NOT NULL,
    id_usuario INT,
    tipo VARCHAR(50) NOT NULL,
    quantidade INT NOT NULL,
    observacao TEXT,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- Tabela de Requisicoes (Antiga / Interna)
CREATE TABLE IF NOT EXISTS requisicoes (
    id_requisicao INTEGER PRIMARY KEY AUTOINCREMENT,
    solicitante VARCHAR(120) NOT NULL,
    id_setor INT,
    material VARCHAR(160) NOT NULL,
    quantidade INT NOT NULL,
    prioridade VARCHAR(50) NOT NULL DEFAULT 'Média',
    justificativa TEXT,
    status VARCHAR(50) NOT NULL DEFAULT 'Pendente Coordenador',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_setor) REFERENCES setores(id_setor) ON DELETE SET NULL
);

-- Tabela de Historico de Requisicoes
CREATE TABLE IF NOT EXISTS historico_requisicoes (
    id_historico INTEGER PRIMARY KEY AUTOINCREMENT,
    id_requisicao INT NOT NULL,
    acao VARCHAR(160) NOT NULL,
    responsavel VARCHAR(120) NOT NULL,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_requisicao) REFERENCES requisicoes(id_requisicao) ON DELETE CASCADE
);

-- Tabela de Fornecedores
CREATE TABLE IF NOT EXISTS fornecedores (
    id_fornecedor INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_fantasia VARCHAR(160) NOT NULL,
    cnpj VARCHAR(20) UNIQUE,
    email VARCHAR(120),
    telefone VARCHAR(20),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Compras
CREATE TABLE IF NOT EXISTS compras (
    id_compra INTEGER PRIMARY KEY AUTOINCREMENT,
    id_requisicao INT NOT NULL,
    id_fornecedor INT REFERENCES fornecedores(id_fornecedor) ON DELETE SET NULL,
    valor_total DECIMAL(10,2),
    data_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_requisicao) REFERENCES requisicoes(id_requisicao) ON DELETE CASCADE
);

-- Tabela de Notificacoes In-App
CREATE TABLE IF NOT EXISTS notificacoes (
    id_notificacao INTEGER PRIMARY KEY AUTOINCREMENT,
    id_usuario INT,
    nivel_destino VARCHAR(50),
    titulo VARCHAR(160) NOT NULL,
    mensagem TEXT NOT NULL,
    lida BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Logs de Auditoria (Cibersegurança)
CREATE TABLE IF NOT EXISTS audit_logs (
    id_audit INTEGER PRIMARY KEY AUTOINCREMENT,
    tabela_afetada VARCHAR(50) NOT NULL,
    id_registro INT NOT NULL,
    acao VARCHAR(50) NOT NULL,
    detalhes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Função e Trigger para atualizar o campo "atualizado_em" automaticamente



-- Triggers SQLite para atualizacao de data
CREATE TRIGGER IF NOT EXISTS update_produtos_modtime
AFTER UPDATE ON produtos
FOR EACH ROW
BEGIN
    UPDATE produtos SET atualizado_em = CURRENT_TIMESTAMP WHERE id_produto = NEW.id_produto;
END;

CREATE TRIGGER IF NOT EXISTS update_requisicoes_modtime
AFTER UPDATE ON requisicoes
FOR EACH ROW
BEGIN
    UPDATE requisicoes SET atualizado_em = CURRENT_TIMESTAMP WHERE id_requisicao = NEW.id_requisicao;
END;

CREATE TRIGGER IF NOT EXISTS trigger_audit_produtos
AFTER UPDATE ON produtos
FOR EACH ROW
WHEN NEW.quantidade_estoque <> OLD.quantidade_estoque
BEGIN
    INSERT INTO audit_logs (tabela_afetada, id_registro, acao, detalhes)
    VALUES ('produtos', NEW.id_produto, 'UPDATE_ESTOQUE', 
            'Estoque alterado de ' || OLD.quantidade_estoque || ' para ' || NEW.quantidade_estoque);
END;
INSERT INTO setores (nome_setor) VALUES ('Administrativo'), ('Caixa'), ('Estoque') ON CONFLICT DO NOTHING;

-- Hash de 'admin123' gerado via BCRYPT
INSERT INTO usuarios (nome, login, senha, nivel_acesso, id_setor) 
VALUES ('Administrador Master', 'admin', '$2y$12$.HfR4mloLrGDIO8RP4tcvu1f16ZOxa1XBvgAs6eJm80WQTq0Mxjna', 'Administrador', 1) ON CONFLICT DO NOTHING;

-- Inserindo Dados Iniciais Secundarios
INSERT INTO categorias (nome_categoria) VALUES ('Bebidas'), ('Mercearia'), ('Higiene') ON CONFLICT DO NOTHING;
INSERT INTO clientes (nome, telefone) VALUES ('Cliente Padrão (Balcão)', '') ON CONFLICT DO NOTHING;

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
