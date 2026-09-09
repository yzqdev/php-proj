#!/usr/bin/env pwsh

$Host.UI.RawUI.WindowTitle = "PHP Tutor API"
$port = 8080
$root = Join-Path $PSScriptRoot "public"
$projectRoot = $PSScriptRoot

# ---------------------------------------------------------------------------
# Pre-flight checks
# ---------------------------------------------------------------------------
if (-not (Test-Path "$projectRoot/vendor")) {
    Write-Host "Installing dependencies..." -ForegroundColor Yellow
    composer install --no-dev --optimize-autoloader
    Write-Host ""
}

if (-not (Test-Path "$projectRoot/.env")) {
    Write-Host "Creating .env from .env.example..." -ForegroundColor Yellow
    Copy-Item "$projectRoot/.env.example" "$projectRoot/.env"
    Write-Host ""
}

# ---------------------------------------------------------------------------
# Start server
# ---------------------------------------------------------------------------
Write-Host "PHP Tutor API" -ForegroundColor Cyan
Write-Host ""
Write-Host "  API        http://localhost:$port/api/v1" -ForegroundColor Green
Write-Host "  Swagger UI http://localhost:$port/swagger" -ForegroundColor Magenta
Write-Host "  Spec JSON  http://localhost:$port/swagger/json" -ForegroundColor DarkGray
Write-Host ""
Write-Host "Press Ctrl+C to stop" -ForegroundColor DarkGray
Write-Host ""

php -S "localhost:$port" -t $root
