-- =========================================================================
-- SCRIPT DE SEGURANÇA SUPABASE (Item 4 - Boas Práticas RLS)
-- =========================================================================
-- Como a aplicação em PHP utiliza a string de conexão direta com usuário `postgres` (admin),
-- o RLS será "bypassado" pela aplicação. Estas regras servem para garantir a segurança
-- caso haja alguma chave `anon` ou `service_role` sendo utilizada via API REST futuramente.

-- 1. Ativar RLS nas tabelas principais
ALTER TABLE usuarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE clientes ENABLE ROW LEVEL SECURITY;
ALTER TABLE produtos ENABLE ROW LEVEL SECURITY;
ALTER TABLE vendas ENABLE ROW LEVEL SECURITY;
ALTER TABLE requisicoes ENABLE ROW LEVEL SECURITY;

-- 2. Bloquear acesso anônimo (público) por completo
-- Nenhuma pessoa usando a chave 'anon' do Supabase conseguirá ler ou alterar nada
CREATE POLICY "Bloquear acesso anônimo aos usuarios" 
ON usuarios FOR ALL 
TO anon 
USING (false);

CREATE POLICY "Bloquear acesso anônimo aos clientes" 
ON clientes FOR ALL 
TO anon 
USING (false);

CREATE POLICY "Bloquear acesso anônimo aos produtos" 
ON produtos FOR ALL 
TO anon 
USING (false);

CREATE POLICY "Bloquear acesso anônimo as vendas" 
ON vendas FOR ALL 
TO anon 
USING (false);

CREATE POLICY "Bloquear acesso anônimo as requisicoes" 
ON requisicoes FOR ALL 
TO anon 
USING (false);

-- (Os administradores com usuário `postgres` não são afetados por essas políticas)
