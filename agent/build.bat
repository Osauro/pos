@echo off
setlocal
title Compilar print-agent.exe

echo.
echo  ============================================
echo   Compilar print-agent.exe  ^|  TPV MiSoio
echo  ============================================
echo.

:: Verificar Go instalado
where go >nul 2>&1
if errorlevel 1 (
    echo  [ERROR] Go no esta instalado.
    echo.
    echo  Descarga el instalador desde:
    echo    https://go.dev/dl/
    echo.
    echo  Selecciona: go1.2x.x.windows-amd64.msi
    echo  Instala, luego CIERRA y REABRE este bat.
    echo.
    pause
    exit /b 1
)

:: Mostrar version
for /f "tokens=3" %%v in ('go version') do echo  Go version: %%v
echo.

:: Compilar
echo  Compilando...
go build -ldflags="-s -w -H=windowsgui" -o print-agent.exe print-agent.go

if errorlevel 1 (
    echo.
    echo  [ERROR] La compilacion fallo. Revisa el mensaje arriba.
    pause
    exit /b 1
)

echo.
echo  [OK] print-agent.exe generado correctamente.
echo.
echo  Ahora ejecuta  abrir-configurador.bat  para configurar.
echo.
pause
endlocal
