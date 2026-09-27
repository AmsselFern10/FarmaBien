@echo off
setlocal enabledelayedexpansion
chcp 65001 >nul 2>&1
title FarmaBien — Setup Automatico

echo.
echo  ===========================================================
echo   FARMABIEN — INSTALACION AUTOMATICA  v1.1
echo   Resuelve todos los problemas comunes de Docker en Windows
echo  ===========================================================
echo.

:: ─────────────────────────────────────────────
:: VERIFICACION 1: Docker Desktop corriendo?
:: ─────────────────────────────────────────────
echo [1/8] Verificando Docker Desktop...
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo  [ERROR] Docker Desktop no esta corriendo o no esta instalado.
    echo.
    echo  Solucion:
    echo    1. Descarga Docker Desktop desde: https://www.docker.com/products/docker-desktop
    echo    2. Instala y reinicia Windows
    echo    3. Abre Docker Desktop y espera a que el icono en la barra de tareas
    echo       diga "Docker Desktop is running"
    echo    4. Ejecuta este script de nuevo
    echo.
    pause
    exit /b 1
)
echo  [OK] Docker Desktop esta corriendo.

:: ─────────────────────────────────────────────
:: VERIFICACION 2: Puerto 80 libre?  (Inc-2)
:: ─────────────────────────────────────────────
echo.
echo [2/8] Verificando puerto 80...
netstat -ano | findstr ":80 " | findstr "LISTENING" >nul 2>&1
if %errorlevel% equ 0 (
    echo  [AVISO] Puerto 80 esta ocupado. Usando puerto 8080 automaticamente.
    set APP_PORT=8080
) else (
    echo  [OK] Puerto 80 libre.
    set APP_PORT=80
)

:: ─────────────────────────────────────────────
:: VERIFICACION 3: Puerto 3306 libre?  (Inc-1)
:: ─────────────────────────────────────────────
echo.
echo [3/8] Verificando puerto 3306 (MySQL)...
netstat -ano | findstr ":3306 " | findstr "LISTENING" >nul 2>&1
if %errorlevel% equ 0 (
    echo  [AVISO] Puerto 3306 ocupado (posiblemente XAMPP/MySQL local).
    echo          Usando puerto 3307 para el contenedor MySQL de Docker.
    set FORWARD_DB_PORT=3307
) else (
    echo  [OK] Puerto 3306 libre.
    set FORWARD_DB_PORT=3306
)

:: ─────────────────────────────────────────────
:: PASO 1: Crear .env si no existe  (Inc y Paso 2)
:: ─────────────────────────────────────────────
echo.
echo [4/8] Configurando archivo .env...
if not exist ".env" (
    copy ".env.local.example" ".env" >nul 2>&1
    if %errorlevel% neq 0 (
        copy ".env.example" ".env" >nul 2>&1
    )
    echo  [OK] Archivo .env creado desde plantilla.
) else (
    echo  [OK] Archivo .env ya existe — no se sobreescribe.
)

:: Ajustar APP_PORT y FORWARD_DB_PORT en .env usando PowerShell (mas seguro en Windows)
powershell -Command "(Get-Content .env) -replace 'APP_PORT=.*', 'APP_PORT=%APP_PORT%' | Set-Content .env" 2>nul
powershell -Command "(Get-Content .env) -replace 'FORWARD_DB_PORT=.*', 'FORWARD_DB_PORT=%FORWARD_DB_PORT%' | Set-Content .env" 2>nul

:: ─────────────────────────────────────────────
:: PASO 2: Verificar vendor/ — instalar si no existe  (Paso 1 / Inc-4)
:: ─────────────────────────────────────────────
echo.
echo [5/8] Verificando dependencias PHP (vendor/)...
if not exist "vendor\" (
    echo  [INFO] Carpeta vendor/ no existe. Instalando dependencias via contenedor temporal...
    echo         Esto puede tardar 2-5 minutos la primera vez...
    docker run --rm -v "%cd%:/app" -w /app composer:2 install --no-interaction --prefer-dist --ignore-platform-reqs
    if %errorlevel% neq 0 (
        echo.
        echo  [ERROR] No se pudieron instalar las dependencias PHP.
        echo          Verifica tu conexion a internet e intenta de nuevo.
        pause
        exit /b 1
    )
    echo  [OK] Dependencias PHP instaladas.
) else (
    echo  [OK] vendor/ ya existe.
)

:: ─────────────────────────────────────────────
:: PASO 3: Levantar contenedores
:: ─────────────────────────────────────────────
echo.
echo [6/8] Levantando contenedores Docker...
echo       (La primera vez puede tardar 3-5 minutos descargando imagenes)
docker compose up -d --build 2>&1
if %errorlevel% neq 0 (
    echo.
    echo  [ERROR] No se pudieron levantar los contenedores.
    echo          Revisa que Docker Desktop este corriendo correctamente.
    pause
    exit /b 1
)
echo  [OK] Contenedores levantados.

:: ─────────────────────────────────────────────
:: PASO 4: Esperar a que MySQL este listo  (Paso 3)
:: ─────────────────────────────────────────────
echo.
echo [7/8] Esperando que MySQL este listo (puede tomar 15-30 segundos)...
set INTENTOS=0
:esperar_mysql
set /a INTENTOS+=1
if %INTENTOS% gtr 20 (
    echo  [ERROR] MySQL no respondio en tiempo esperado.
    echo          Prueba ejecutar: docker compose logs mysql
    pause
    exit /b 1
)
docker compose exec -T mysql mysqladmin ping -uroot -ppassword --silent >nul 2>&1
if %errorlevel% neq 0 (
    echo         Intento %INTENTOS%/20 — esperando...
    timeout /t 3 /nobreak >nul
    goto esperar_mysql
)
echo  [OK] MySQL listo.

:: ─────────────────────────────────────────────
:: PASO 5: Generar APP_KEY, migrate y seed
:: ─────────────────────────────────────────────
echo.
echo [8/8] Configurando la aplicacion...

:: Verificar si ya tiene APP_KEY generada
findstr /C:"APP_KEY=base64:" .env >nul 2>&1
if %errorlevel% neq 0 (
    echo  - Generando clave de aplicacion...
    docker compose exec laravel.test php artisan key:generate --force
)

echo  - Ejecutando migraciones y cargando datos...
docker compose exec laravel.test php artisan migrate:fresh --seed --force
if %errorlevel% neq 0 (
    echo  [ERROR] Fallo la migracion. Revisa los logs con: docker compose logs laravel.test
    pause
    exit /b 1
)

echo  - Creando enlace de almacenamiento...
docker compose exec laravel.test php artisan storage:link >nul 2>&1

echo  - Compilando frontend (Vite) DENTRO del contenedor...  (Paso 5)
docker compose exec laravel.test npm run build
if %errorlevel% neq 0 (
    echo  [AVISO] Compilacion frontend fallo — la app igual funciona, los assets se cargaran via Vite dev.
)

echo  - Limpiando cache de configuracion...
docker compose exec laravel.test php artisan optimize:clear >nul 2>&1

:: ─────────────────────────────────────────────
:: LISTO
:: ─────────────────────────────────────────────
echo.
echo  ===========================================================
echo.
if "%APP_PORT%"=="80" (
    echo   FARMABIEN LISTO en: http://localhost
    echo   (Si no abre, prueba: http://127.0.0.1)
) else (
    echo   FARMABIEN LISTO en: http://localhost:%APP_PORT%
    echo   (Puerto 80 estaba ocupado, se uso el %APP_PORT%)
)
echo.
echo   CREDENCIALES DE ACCESO:
echo   ┌─────────────────────────────────────────────┐
echo   │  Admin       admin@farmabien.com  / password │
echo   │  Farmaceutico farmaceutico@farmabien.com / pw│
echo   │  Cajero      cajero@farmabien.com / password │
echo   └─────────────────────────────────────────────┘
echo.
echo   Para detener:   docker compose down
echo   Para reiniciar: docker compose up -d
echo.
echo  ===========================================================
echo.
pause
