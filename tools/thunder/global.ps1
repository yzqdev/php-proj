$bin = "C:\Users\yanni\AppData\Roaming\Composer\vendor\bin"
$current = [Environment]::GetEnvironmentVariable("Path", "User")
$items = $current -split ';' | Where-Object { $_ }

if ($items -notcontains $bin) {
    [Environment]::SetEnvironmentVariable("Path", ($items + $bin) -join ';', "User")
    Write-Host "已添加到用户 PATH" -ForegroundColor Green
} else {
    Write-Host "PATH 中已存在，跳过" -ForegroundColor Yellow
}
