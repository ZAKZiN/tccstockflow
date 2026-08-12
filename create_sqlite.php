<?php

$db = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
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

CREATE TABLE IF NOT EXISTS produtos (
    id_produto INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_produto TEXT NOT NULL UNIQUE,
    quantidade_estoque INTEGER NOT NULL DEFAULT 0,
    estoque_minimo INTEGER NOT NULL DEFAULT 5,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP
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

INSERT OR IGNORE INTO setores (id_setor, nome_setor) VALUES (1, 'TI'), (2, 'Administrativo'), (3, 'Pedagógico');
INSERT OR IGNORE INTO usuarios (nome, login, senha, nivel_acesso, id_setor) 
VALUES ('Administrador Master', 'admin', '$2y$12$.HfR4mloLrGDIO8RP4tcvu1f16ZOxa1XBvgAs6eJm80WQTq0Mxjna', 'Administrador', 1);
";

$db->exec($sql);
echo 'SQLite database created successfully!';
