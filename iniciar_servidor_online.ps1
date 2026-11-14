# Iniciar Servidor StockFlow TCC + Cloudflare Tunnel
$Host.UI.RawUI.WindowTitle = "StockFlow TCC - Servidor Online"

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
if (-not $scriptDir) { $scriptDir = (Get-Location).Path }
Set-Location $scriptDir

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "       StockFlow TCC - Inicializador de Servidor Online    " -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host ""

$phpPath = "C:\xampp\php\php.exe"
if (-not (Test-Path $phpPath)) {
    $phpCmd = Get-Command php -ErrorAction SilentlyContinue
    if ($phpCmd) { $phpPath = $phpCmd.Source }
}

$cloudflaredPath = "C:\Program Files (x86)\cloudflared\cloudflared.exe"
if (-not (Test-Path $cloudflaredPath)) {
    $cloudflaredPath = "C:\Program Files\cloudflared\cloudflared.exe"
    if (-not (Test-Path $cloudflaredPath)) {
        $cfCmd = Get-Command cloudflared -ErrorAction SilentlyContinue
        if ($cfCmd) { $cloudflaredPath = $cfCmd.Source }
    }
}

if (-not (Test-Path $phpPath)) {
    Write-Host "[ERRO] PHP nao encontrado em C:\xampp\php\php.exe" -ForegroundColor Red
    Read-Host "Pressione Enter para sair..."
    Exit 1
}

if (-not (Test-Path $cloudflaredPath)) {
    Write-Host "[ERRO] Cloudflared nao encontrado." -ForegroundColor Red
    Read-Host "Pressione Enter para sair..."
    Exit 1
}

Write-Host "[1/2] Iniciando servidor PHP local na porta 8000..." -ForegroundColor Yellow
$phpProcess = Start-Process -FilePath $phpPath -ArgumentList "-S 127.0.0.1:8000 -t public" -PassThru -WindowStyle Hidden

Start-Sleep -Seconds 1

Write-Host "[2/2] Criando tunel HTTPS seguro na Cloudflare..." -ForegroundColor Yellow
$logFile = Join-Path $env:TEMP "cloudflared_stockflow.log"
if (Test-Path $logFile) { Remove-Item $logFile -Force }

$cfProcess = Start-Process -FilePath $cloudflaredPath -ArgumentList "tunnel --url http://127.0.0.1:8000 --logfile `"$logFile`"" -PassThru -WindowStyle Hidden

$tunnelUrl = ""
$timeout = 25
$elapsed = 0

while ($elapsed -lt $timeout -and [string]::IsNullOrEmpty($tunnelUrl)) {
    Start-Sleep -Seconds 1
    $elapsed++
    if (Test-Path $logFile) {
        $content = Get-Content $logFile -Raw -ErrorAction SilentlyContinue
        if ($content -match '(https://[a-zA-Z0-9-]+\.trycloudflare\.com)') {
            $tunnelUrl = $matches[1]
        }
    }
}

try {
    if (-not [string]::IsNullOrEmpty($tunnelUrl)) {
        Clear-Host
        Write-Host "==========================================================" -ForegroundColor Green
        Write-Host "       SERVIDOR STOCKFLOW ONLINE COM SUCESSO!             " -ForegroundColor Green
        Write-Host "==========================================================" -ForegroundColor Green
        Write-Host ""
        Write-Host " Link publico para a Banca Examinadora (HTTPS Oficial):" -ForegroundColor White
        Write-Host " $tunnelUrl" -ForegroundColor Cyan
        Write-Host ""
        Write-Host " (Link copiado para a area de transferencia automaticamente!)" -ForegroundColor Gray
        Write-Host ""
        Write-Host " Link local de contingencia:" -ForegroundColor White
        Write-Host " http://localhost:8000" -ForegroundColor DarkGray
        Write-Host ""
        Write-Host " Credenciais padrao de homologacao:" -ForegroundColor White
        Write-Host " Usuario: admin" -ForegroundColor Yellow
        Write-Host " Senha:   admin123" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "==========================================================" -ForegroundColor Cyan
        Write-Host " Mantenha esta janela ABERTA durante a apresentacao." -ForegroundColor Magenta
        Write-Host " Pressione qualquer tecla ou feche esta janela para parar." -ForegroundColor Gray
        Write-Host "==========================================================" -ForegroundColor Cyan

        # Copiar para o clipboard
        Set-Clipboard -Value $tunnelUrl -ErrorAction SilentlyContinue

        # Abrir navegador automaticamente
        Start-Process $tunnelUrl

        $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    } else {
        Write-Host "[ERRO] Nao foi possivel obter o link do tunel a tempo." -ForegroundColor Red
        if (Test-Path $logFile) { Get-Content $logFile -Tail 20 }
        Read-Host "Pressione Enter para sair..."
    }
} finally {
    Write-Host "`nEncerrando processos..." -ForegroundColor Yellow
    if ($phpProcess -and -not $phpProcess.HasExited) { Stop-Process -Id $phpProcess.Id -Force -ErrorAction SilentlyContinue }
    if ($cfProcess -and -not $cfProcess.HasExited) { Stop-Process -Id $cfProcess.Id -Force -ErrorAction SilentlyContinue }
    Write-Host "Servidor finalizado." -ForegroundColor Green
}

