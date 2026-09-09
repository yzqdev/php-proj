#!/usr/bin/env pwsh

$Host.UI.RawUI.WindowTitle = "Fox Migrations"

$config = "migrations.php"
$dbConfig = "migrations-db.php"
$doctrine = "vendor/bin/doctrine-migrations"

function Show-Help {
    Write-Host ""
    Write-Host "Fox Doctrine Migrations" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "用法: ./runmig.ps1 <command>" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "  migrate   执行所有待执行的迁移" -ForegroundColor Green
    Write-Host "  status    查看迁移状态" -ForegroundColor Green
    Write-Host "  diff      根据 Entity 变更生成新迁移文件" -ForegroundColor Green
    Write-Host "  rollback  回滚到上一个版本" -ForegroundColor Green
    Write-Host "  fresh     删除所有表并重新执行迁移（危险！）" -ForegroundColor Red
    Write-Host ""
}

if ($args.Count -eq 0) {
    Show-Help
    exit 0
}

$cmd = $args[0]

switch ($cmd) {
    "migrate" {
        Write-Host ">>> 执行迁移..." -ForegroundColor Cyan
        php $doctrine migrate --configuration=$config --db-configuration=$dbConfig --no-interaction
    }
    "status" {
        Write-Host ">>> 迁移状态:" -ForegroundColor Cyan
        php $doctrine status --configuration=$config --db-configuration=$dbConfig
    }
    "diff" {
        Write-Host ">>> 生成迁移文件..." -ForegroundColor Cyan
        php $doctrine diff --configuration=$config --db-configuration=$dbConfig --no-interaction
    }
    "rollback" {
        Write-Host ">>> 回滚到上一个版本..." -ForegroundColor Yellow
        php $doctrine migrate --configuration=$config --db-configuration=$dbConfig --no-interaction 1
    }
    "fresh" {
        Write-Host ">>> 即将删除所有表并重新执行迁移！" -ForegroundColor Red
        $confirm = Read-Host "输入 YES 确认"
        if ($confirm -eq "YES") {
            php $doctrine migrate --configuration=$config --db-configuration=$dbConfig --no-interaction --delete-all
        } else {
            Write-Host "已取消" -ForegroundColor DarkGray
        }
    }
    default {
        Write-Host "未知命令: $cmd" -ForegroundColor Red
        Show-Help
    }
}
