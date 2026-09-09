# 提前创建好不带 BOM 的 UTF-8 编码对象（$false 表示不带 BOM）
$utf8NoBom = [System.Text.UTF8Encoding]::new($false)

Get-ChildItem .\src -Recurse -Filter *.php | ForEach-Object {
    $path = $_.FullName

    # 1. 读取文本
    $content = [System.IO.File]::ReadAllText($path)

    # 2. 写入无 BOM UTF-8
    [System.IO.File]::WriteAllText($path, $content, $utf8NoBom)
}