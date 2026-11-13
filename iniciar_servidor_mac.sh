#!/bin/bash
# StockFlow TCC - Inicializador para macOS / Linux

echo "=========================================================="
echo "       StockFlow TCC - Inicializador para macOS           "
echo "=========================================================="
echo ""

# Muda para a pasta onde o script está
cd "$(dirname "$0")"

# 1. Verifica se o PHP está instalado
if command -v php >/dev/null 2>&1; then
    echo "[OK] PHP encontrado: $(php -v | head -n 1)"
else
    echo "[ERRO] PHP nao encontrado no sistema."
    echo ""
    echo "Como resolver no Mac:"
    echo "1. Abra o Terminal e instale via Homebrew: brew install php"
    echo "2. Ou abra pelo MAMP / Docker Desktop"
    exit 1
fi

# 2. Inicializa o banco com dados de demonstracao caso nao exista
if [ ! -f "database.sqlite" ]; then
    echo "[INFO] Banco local novo detectado. Carregando dados de demonstracao..."
    php seed_demo.php
fi

# 3. Inicia servidor PHP em segundo plano na porta 8000
echo "[1/2] Iniciando servidor local na porta 8000..."
php -S 0.0.0.0:8000 -t public > /dev/null 2>&1 &
PHP_PID=$!

trap "kill $PHP_PID 2>/dev/null; exit" SIGINT SIGTERM EXIT

sleep 1

# 4. Se tiver cloudflared instalado no Mac, cria o túnel HTTPS
if command -v cloudflared >/dev/null 2>&1; then
    echo "[2/2] Criando tunel HTTPS seguro Cloudflare..."
    echo ""
    echo "=========================================================="
    echo " Link local: http://localhost:8000"
    echo " Para encerrar o servidor, pressione CTRL + C"
    echo "=========================================================="
    open "http://localhost:8000" >/dev/null 2>&1 &
    cloudflared tunnel --url http://localhost:8000
else
    echo ""
    echo "=========================================================="
    echo "       SERVIDOR STOCKFLOW LOCAL ONLINE COM SUCESSO!       "
    echo "=========================================================="
    echo ""
    echo " Link de acesso no navegador: http://localhost:8000"
    echo " Credenciais: admin / admin123"
    echo ""
    echo " (Se quiser link publico HTTPS no Mac: brew install cloudflared)"
    echo " Pressione CTRL + C para encerrar o servidor."
    echo "=========================================================="
    open "http://localhost:8000" >/dev/null 2>&1 &
    wait $PHP_PID
fi

