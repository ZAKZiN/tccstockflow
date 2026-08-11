-- Script SQL para o Sistema StockFlow adaptado para PostgreSQL (Supabase)

-- A tabela de Setores
CREATE TABLE IF NOT EXISTS setores (
    id_setor SERIAL PRIMARY KEY,
    nome_setor VARCHAR(80) NOT NULL UNIQUE
);

-- Tabela de Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario SERIAL PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    login VARCHAR(60) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso VARCHAR(20) CHECK (nivel_acesso IN ('Solicitante', 'Coordenador', 'Almoxarife', 'Administrador')) NOT NULL,
    id_setor INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_setor) REFERENCES setores(id_setor) ON DELETE SET NULL
);

-- Tabela de Produtos
CREATE TABLE IF NOT EXISTS produtos (
    id_produto SERIAL PRIMARY KEY,
    nome_produto VARCHAR(160) NOT NULL UNIQUE,
    quantidade_estoque INT NOT NULL DEFAULT 0,
    estoque_minimo INT NOT NULL DEFAULT 5,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Requisicoes
CREATE TABLE IF NOT EXISTS requisicoes (
    id_requisicao SERIAL PRIMARY KEY,
    solicitante VARCHAR(120) NOT NULL,
    id_setor INT,
    material VARCHAR(160) NOT NULL,
    quantidade INT NOT NULL,
    prioridade VARCHAR(20) CHECK (prioridade IN ('Baixa', 'Média', 'Alta', 'Urgente')) NOT NULL DEFAULT 'Média',
    justificativa TEXT,
    status VARCHAR(50) CHECK (status IN ('Pendente Coordenador', 'Recusado', 'Pendente Administrativo', 'Pendente Almoxarifado', 'Compra Efetuada', 'Despachado')) NOT NULL DEFAULT 'Pendente Coordenador',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_setor) REFERENCES setores(id_setor) ON DELETE SET NULL
);

-- Tabela de Historico de Requisicoes
CREATE TABLE IF NOT EXISTS historico_requisicoes (
    id_historico SERIAL PRIMARY KEY,
    id_requisicao INT NOT NULL,
    acao VARCHAR(160) NOT NULL,
    responsavel VARCHAR(120) NOT NULL,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_requisicao) REFERENCES requisicoes(id_requisicao) ON DELETE CASCADE
);

-- Tabela de Fornecedores
CREATE TABLE IF NOT EXISTS fornecedores (
    id_fornecedor SERIAL PRIMARY KEY,
    nome_fantasia VARCHAR(160) NOT NULL,
    cnpj VARCHAR(20) UNIQUE,
    email VARCHAR(120),
    telefone VARCHAR(20),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Compras
CREATE TABLE IF NOT EXISTS compras (
    id_compra SERIAL PRIMARY KEY,
    id_requisicao INT NOT NULL,
    id_fornecedor INT REFERENCES fornecedores(id_fornecedor) ON DELETE SET NULL,
    valor_total DECIMAL(10,2),
    data_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_requisicao) REFERENCES requisicoes(id_requisicao) ON DELETE CASCADE
);

-- Tabela de Saidas de Estoque
CREATE TABLE IF NOT EXISTS saidas_estoque (
    id_saida SERIAL PRIMARY KEY,
    id_requisicao INT NOT NULL,
    material VARCHAR(160) NOT NULL,
    quantidade INT NOT NULL,
    responsavel VARCHAR(120) NOT NULL,
    data_saida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_requisicao) REFERENCES requisicoes(id_requisicao) ON DELETE CASCADE
);

-- Função e Trigger para atualizar o campo "atualizado_em" automaticamente no PostgreSQL
CREATE OR REPLACE FUNCTION update_modified_column() 
RETURNS TRIGGER AS $$
BEGIN
    NEW.atualizado_em = CURRENT_TIMESTAMP;
    RETURN NEW; 
END;
$$ language 'plpgsql';

CREATE TRIGGER update_produtos_modtime
    BEFORE UPDATE ON produtos
    FOR EACH ROW
    EXECUTE FUNCTION update_modified_column();

CREATE TRIGGER update_requisicoes_modtime
    BEFORE UPDATE ON requisicoes
    FOR EACH ROW
    EXECUTE FUNCTION update_modified_column();

-- Inserindo Setor Padrao
INSERT INTO setores (nome_setor) VALUES ('TI'), ('Administrativo'), ('Pedagógico') ON CONFLICT DO NOTHING;

-- Inserindo Admin Padrao (senha: admin123 -> o hash deve ser gerado pelo password_hash, provisoriamente inserindo um hash valido)
-- Hash de 'admin123' gerado via BCRYPT
INSERT INTO usuarios (nome, login, senha, nivel_acesso, id_setor) 
VALUES ('Administrador Master', 'admin', '$2y$12$.HfR4mloLrGDIO8RP4tcvu1f16ZOxa1XBvgAs6eJm80WQTq0Mxjna', 'Administrador', 1) ON CONFLICT DO NOTHING;

-- Tabela de Logs de Auditoria (Cibersegurança)
CREATE TABLE IF NOT EXISTS audit_logs (
    id_audit SERIAL PRIMARY KEY,
    tabela_afetada VARCHAR(50) NOT NULL,
    id_registro INT NOT NULL,
    acao VARCHAR(50) NOT NULL,
    detalhes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE FUNCTION log_estoque_changes() RETURNS TRIGGER AS $$
BEGIN
    IF NEW.quantidade_estoque <> OLD.quantidade_estoque THEN
        INSERT INTO audit_logs (tabela_afetada, id_registro, acao, detalhes)
        VALUES ('produtos', NEW.id_produto, 'UPDATE_ESTOQUE', 
                'Estoque alterado de ' || OLD.quantidade_estoque || ' para ' || NEW.quantidade_estoque);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_audit_produtos ON produtos;
CREATE TRIGGER trigger_audit_produtos
    AFTER UPDATE ON produtos
    FOR EACH ROW
    EXECUTE FUNCTION log_estoque_changes();
