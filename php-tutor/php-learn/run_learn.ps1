# ============================================================
# php-learn 后端启动脚本
# ============================================================
#
# 用法：
#   .\run_learn.ps1
#
# 或者指定端口：
#   .\run_learn.ps1 -Port 8001
#
# 说明：
#   - 默认用本机绝对路径的 php.exe（F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe）
#   - 如果 PATH 里有 php，也可以用：$php = (Get-Command php).Source
#   - 启动后会打印出 Swagger UI 文档地址
# ============================================================

param(
    [int]$Port = 6850,
    [string]$ListenHost = '127.0.0.1'
)

# 1) 定位 php.exe
$php = 'F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe'
if (-not (Test-Path $php)) {
    # 回退到 PATH
    $cmd = Get-Command php -ErrorAction SilentlyContinue
    if ($cmd) { $php = $cmd.Source }
    else {
        Write-Host "ERROR: 找不到 php.exe，请检查路径或安装 PHP 8.5" -ForegroundColor Red
        exit 1
    }
}

# 2) 项目根目录
$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$publicDir = Join-Path $root 'public'

if (-not (Test-Path $publicDir)) {
    Write-Host "ERROR: 找不到 public 目录：$publicDir" -ForegroundColor Red
    exit 1
}

# 3) 打印启动信息
$baseUrl = "http://${ListenHost}:${Port}"
$docsUrl = "${baseUrl}/api/v1/docs"
$specUrl = "${baseUrl}/api/v1/docs/spec"
$healthUrl = "${baseUrl}/api/v1/health"

Write-Host ""
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host "  php-learn 后端启动中" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host "  PHP 版本：$($php)" -ForegroundColor DarkCyan
Write-Host "  根目录：  $root" -ForegroundColor DarkCyan
Write-Host "  端口：    $Port" -ForegroundColor DarkCyan
Write-Host "" -ForegroundColor Cyan
Write-Host "  📖 Swagger UI 文档：$docsUrl" -ForegroundColor Green
Write-Host "  📄 OpenAPI JSON：   $specUrl" -ForegroundColor Green
Write-Host "  ❤️  健康检查：       $healthUrl" -ForegroundColor Green
Write-Host "" -ForegroundColor Cyan
Write-Host "  按 Ctrl+C 停止服务器" -ForegroundColor Yellow
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""

# 4) 启动 PHP 内置服务器
& $php -S "${ListenHost}:${Port}" -t $publicDir
