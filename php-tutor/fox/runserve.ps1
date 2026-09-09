#!/usr/bin/env pwsh

$Host.UI.RawUI.WindowTitle = "Fox Server"
$port = 2977
$root = Join-Path $PSScriptRoot "public"

Write-Host "Fox Server" -ForegroundColor Cyan
Write-Host "http://localhost:$port" -ForegroundColor Green
Write-Host "Press Ctrl+C to stop" -ForegroundColor DarkGray
Write-Host ""

php -S "localhost:$port" -t $root
