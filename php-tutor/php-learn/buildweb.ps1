$ErrorActionPreference = "Stop"

Write-Host "==> 开始构建前端..." -ForegroundColor Cyan

# 项目根目录
$Root = $PSScriptRoot

# 前端目录
$WebDir = Join-Path $Root "web"

# 后端静态资源目录
$PublicDir = Join-Path $Root "public"
$AssetsDir = Join-Path $PublicDir "assets"

# 检查前端目录
if (-not (Test-Path $WebDir)) {
    throw "前端目录不存在: $WebDir"
}

# 进入 web 目录并构建
Push-Location $WebDir

try {
    Write-Host "==> 安装/检查前端依赖..." -ForegroundColor Yellow
    pnpm install

    Write-Host "==> 构建前端..." -ForegroundColor Yellow
    pnpm build

    $DistDir = Join-Path $WebDir "dist"

    if (-not (Test-Path $DistDir)) {
        throw "前端构建失败，dist 目录不存在: $DistDir"
    }

    $IndexFile = Join-Path $DistDir "index.html"

    if (-not (Test-Path $IndexFile)) {
        throw "前端构建失败，dist/index.html 不存在"
    }
}
finally {
    Pop-Location
}

# 创建 public/assets
if (-not (Test-Path $AssetsDir)) {
    Write-Host "==> 创建目录: $AssetsDir" -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $AssetsDir -Force | Out-Null
}

# 清理旧的前端静态资源
Write-Host "==> 清理旧的静态资源..." -ForegroundColor Yellow

if (Test-Path $AssetsDir) {
    Remove-Item -Path "$AssetsDir\*" -Recurse -Force
}

# 复制 assets
Write-Host "==> 复制前端 assets..." -ForegroundColor Yellow

$DistAssetsDir = Join-Path $DistDir "assets"

if (Test-Path $DistAssetsDir) {
    Copy-Item `
        -Path "$DistAssetsDir\*" `
        -Destination $AssetsDir `
        -Recurse `
        -Force
}

# 复制 index.html
$AppHtml = Join-Path $PublicDir "app.html"

Write-Host "==> 复制 index.html -> public/app.html" -ForegroundColor Yellow

Copy-Item `
    -Path $IndexFile `
    -Destination $AppHtml `
    -Force

Write-Host ""
Write-Host "======================================" -ForegroundColor Green
Write-Host "前端构建并部署完成！" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green
Write-Host "入口: $AppHtml"
Write-Host "资源: $AssetsDir"