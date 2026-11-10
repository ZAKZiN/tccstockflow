@echo off
title StockFlow TCC - Inicializador de Servidor Online
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0iniciar_servidor_online.ps1"
