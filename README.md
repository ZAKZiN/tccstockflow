# StockFlow - Gestão Comercial e de Estoque

StockFlow é um sistema completo de gestão de estoque, controle de requisições, e frente de caixa (PDV) desenvolvido em PHP moderno.

## Funcionalidades Principais

- **Frente de Caixa (PDV):** Interface moderna e responsiva para lançamento rápido de vendas, suporte a códigos de barra (via teclado ou leitor ótico) e integração nativa com o módulo de estoque.
- **Gestão de Estoque:** Controle rígido de produtos com log de movimentações (Kardex), suporte a inventário e baixa automática nas vendas.
- **Requisições de Compra:** Fluxo de aprovação de materiais, onde setores solicitam e administradores aprovam e vinculam a ordens de compra de fornecedores.
- **Controle Financeiro e de Caixa:** Abertura e fechamento de turnos diários (Caixa), registro de sangrias e suprimentos, além de módulo de gestão de clientes "Fiado" (Contas a Receber).
- **Controle de Acessos (RBAC):** Níveis de acesso bem definidos (Administrador, Gerente, Operador de Caixa, Estoquista) garantindo que cada usuário visualize apenas as ferramentas de seu cargo.

## Segurança e LGPD

Este projeto foi construído seguindo rigorosos padrões de segurança da informação, protegendo os dados de acordo com os princípios da **Lei Geral de Proteção de Dados (LGPD)** e cobrindo diversas vulnerabilidades do padrão OWASP:

- **Prevenção contra Injeção SQL:** Toda a comunicação com o banco de dados é intermediada pelo `PDO` utilizando *Prepared Statements*.
- **Proteção contra Cross-Site Scripting (XSS):** Implementação ativa de cabeçalhos de segurança (`Content-Security-Policy`, `X-XSS-Protection`) impedindo a execução de códigos maliciosos vindos de clientes infectados.
- **Proteção Anti-CSRF e Anti-Bot (Honeypot):** Autenticação segura de formulários via tokens CSRF e inclusão de campos invisíveis (*honeypots*) para bloqueio instantâneo e silencioso de *brute-forces* e scripts automatizados na tela de login.
- **Rate Limiting:** Bloqueio temporário progressivo para contenção de ataques de força bruta no endpoint de autenticação.
- **Criptografia Simétrica Avançada:** Infraestrutura pronta baseada em `OpenSSL AES-256-CBC` (Padrão de indústria) projetada para a anonimização e mascaramento de Informações Pessoalmente Identificáveis (PIIs) de clientes.
- **Sessões e Cookies Blindados:** Emissão de cookies puramente Server-Side, demarcados como `Secure` e `HttpOnly`, tornando o sequestro de sessões viabilizado por scripts de clientes impossível.
- **Row Level Security (RLS) Database:** Políticas restritas instaladas diretamente na infraestrutura em Nuvem (Supabase / PostgreSQL) garantindo que tentativas externas (APIs públicas) sejam rigorosamente negadas.

## Tecnologias Utilizadas

- **Linguagem:** PHP 8+
- **Banco de Dados:** PostgreSQL (Hospedado no Supabase)
- **Frontend:** HTML5, CSS Nativo Moderno (Design System Próprio com suporte a CSS Variables e Flexbox/Grid), Bootstrap (auxiliar), e Phosphor Icons.
- **Infraestrutura:** Deploy Contínuo configurado na plataforma **Render**, garantindo escalabilidade e forçamento de conexões criptografadas (HTTPS).

## Como Rodar o Projeto Localmente

1. Clone o repositório.
2. Certifique-se de possuir o PHP instalado na máquina (versão >= 8.1).
3. Execute o comando `composer install` para baixar o roteador (`bramus/router`) e utilitários (`vlucas/phpdotenv`).
4. Duplique o arquivo `.env.example` para `.env` e preencha as variáveis de ambiente com os dados do seu banco de dados Supabase local ou remoto.
5. Inicie o servidor PHP embutido na raiz do projeto (ou na pasta public caso seu roteador exija):
```bash
php -S localhost:8000 -t public
```
6. Acesse via `http://localhost:8000`.

---
*Projeto desenvolvido como Trabalho de Conclusão de Curso (TCC).*
