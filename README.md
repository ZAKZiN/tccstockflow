# StockFlow — Gestão Integrada de Estoque, Requisições e Frente de Caixa (PDV)

**StockFlow** é um sistema web completo de gestão de estoque, fluxo de requisições internas, ordens de compra e frente de caixa (PDV), desenvolvido em **PHP 8+** seguindo arquitetura **MVC** como Trabalho de Conclusão de Curso (TCC).

## Funcionalidades Principais

- **Frente de Caixa (PDV):** Lançamento rápido de vendas com filtro por categoria, leitura de código de barras (leitor óptico, câmera ou etiquetas de balança EAN-13), cálculo de descontos/troco, formas múltiplas de pagamento (Dinheiro, Pix, Cartão, Misto e Fiado) e emissão de cupom não fiscal em 80mm / PDF / WhatsApp.
- **Controle Financeiro de Caixa (Turnos):** Abertura e fechamento de caixa com separação entre **Saldo Físico na Gaveta (Dinheiro)** e **Recebimentos Eletrônicos (Pix/Cartão)**, sangrias com trava de saldo físico, suprimentos e emissão de **Relatório Z**.
- **Gestão de Estoque e BI (Curva ABC):** Controle completo de produtos, categorias, lotes, alertas preventivos de validade e estoque crítico, rastreabilidade integral de entradas/saídas via **Kardex** e análise gerencial **Curva ABC (Pareto)**.
- **Fluxo de Requisições e Ordens de Compra:** Solicitações por setor com aprovação da coordenação, geração de Ordens de Compra vinculadas a fornecedores (com entrada automática no estoque/Kardex) e despacho controlado aos setores.
- **Fiado / Contas a Receber:** Cadastro rápido de clientes, controle de títulos pendentes, cobrança integrada via WhatsApp e integração direta da baixa com o turno de caixa aberto.
- **Controle de Acesso por Cargos (RBAC):** Seis perfis distintos (`Administrador`, `Gerente`, `Operador de Caixa`, `Estoquista`, `Coordenador` e `Solicitante`) com redirecionamento inteligente e isolamento de rotas e ações.

## Segurança da Informação e Conformidade LGPD

O projeto implementa controles defensivos alinhados ao **OWASP Top 10** e à **LGPD**:

- **Prevenção contra SQL Injection:** Uso estrito de `PDO` com *Prepared Statements* em todas as operações de banco.
- **Criptografia Autenticada de Dados Pessoais (LGPD):** Telefones e e-mails de clientes e fornecedores são criptografados em repouso no banco de dados via `OpenSSL AES-256-GCM` ([`CryptHelper.php`](app/Helpers/CryptHelper.php)).
- **Proteção Anti-CSRF Global:** Validação criptográfica constante (`hash_equals`) em **todas** as rotas `POST` e endpoints JSON (`X-CSRF-Token`), sem ações de escrita via `GET`.
- **Prevenção contra XSS e Clickjacking:** Sanitização de saídas no servidor (`htmlspecialchars`) e no DOM (`textContent`), aliada aos cabeçalhos `Content-Security-Policy`, `X-Frame-Options: SAMEORIGIN` e `X-Content-Type-Options: nosniff`.
- **Proteção de Sessão e Autenticação:** Cookies `HttpOnly`, `SameSite=Lax` e `Secure`, regeneração de ID de sessão pós-login (`session_regenerate_id`), senhas com hash `bcrypt`, campo *Honeypot* anti-bot e *Rate Limiting* persistente por IP contra força bruta.
- **Prevenção contra CSV Formula Injection:** Sanitização de células exportadas nos relatórios CSV de Estoque e Requisições.
- **Trilha de Auditoria (`audit_logs`):** Registro automático via *Triggers* SQLite e logs de aplicação identificando o usuário autor em ações críticas.

## Tecnologias Utilizadas

- **Back-end:** PHP 8.2+ (Arquitetura MVC própria, `bramus/router`, `vlucas/phpdotenv`)
- **Banco de Dados:** SQLite 3 (com `Foreign Keys`, modo concorrente `WAL` e auto-inicialização via [`database.sql`](database.sql))
- **Front-end:** HTML5, CSS3 Custom Properties (com **Modo Escuro** completo e responsividade mobile App-Like), Bootstrap 5, Chart.js, SweetAlert2, Phosphor Icons e PWA (`manifest.json` + Service Worker).
- **Infraestrutura:** Containerizado via [`Dockerfile`](Dockerfile) (`php:8.2-apache`) pronto para deploy contínuo no **Render**.

## Como Executar Localmente

1. Instale as dependências com Composer:
   ```bash
   composer install
   ```
2. (Opcional) Copie `.env.example` para `.env` e defina sua `APP_KEY`.
3. Inicie o servidor embutido apontando para `public/`:
   ```bash
   php -S localhost:8000 -t public
   ```
4. Acesse `http://localhost:8000`. Na primeira execução, o banco `database.sqlite` será criado automaticamente.
   - **Credencial padrão de homologação (TCC):** Usuário `admin` | Senha `admin123`.

---
*Projeto desenvolvido como Trabalho de Conclusão de Curso (TCC).*
