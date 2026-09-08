<?php

declare(strict_types=1);

namespace Service;

/**
 * 原 api/clouddrive.php 的全部 action 业务逻辑与工具函数，原样搬迁。
 * 方法名 = 旧接口 ?action= 参数值；目录布局、文件格式、响应结构均不变。
 * 未登录检查由 CloudDriveAuthMiddleware 完成（等价旧代码每分支开头的 apiError('未登录',401)）。
 */
final class CloudDriveService
{
    private const ALLOW_SORTBY = ['name', 'mtime'];
    private const ALLOW_ORDER = ['asc', 'desc'];

    private string $adminPwd;
    private string $storageDir;
    private string $storageReal;
    private string $shareDir;
    private string $zipTemp;
    private string $pwdConfigFile;

    public function __construct()
    {
        $root = dirname(__DIR__, 2);
        $this->adminPwd = '123456';
        // 运行时数据统一落在 storage/ 下，与 storage/users.json 同一目录（已被 .gitignore 整体忽略）
        $this->storageDir = $root . '/storage/cloud_file';
        $this->shareDir = $root . '/storage/share_temp';
        $this->zipTemp = $root . '/storage/zip_temp';
        $this->pwdConfigFile = $root . '/storage/pwd.config.txt';

        foreach ([$this->storageDir, $this->shareDir, $this->zipTemp] as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        // 存储根的规范真实路径（正向斜杠），供归属校验统一比较
        $this->storageReal = str_replace('\\', '/', (string) realpath($this->storageDir) ?: $this->storageDir);
        if (file_exists($this->pwdConfigFile)) {
            $this->adminPwd = trim((string) file_get_contents($this->pwdConfigFile));
        }
    }

    /** 旧 formatSize() */
    private function formatSize(int $b): string
    {
        if ($b < 1024) {
            return $b . ' B';
        }
        if ($b < 1048576) {
            return round($b / 1024, 2) . ' KB';
        }

        return round($b / 1048576, 2) . ' MB';
    }

    /** 旧 isImage() */
    private function isImage(string $ext): bool
    {
        return in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
    }

    /** 旧 isVideo() */
    private function isVideo(string $ext): bool
    {
        return in_array(strtolower($ext), ['mp4', 'mov', 'webm']);
    }

    /** 旧 isPdf() */
    private function isPdf(string $ext): bool
    {
        return strtolower($ext) === 'pdf';
    }

    /** 旧 safeFileName() */
    private function safeFileName(string $name): string
    {
        $name = mb_convert_encoding($name, 'UTF-8', mb_detect_encoding($name));

        return preg_replace('/[<>:"\/\\|?*]/', '_', $name);
    }

    /**
     * 旧 safePath() 的加固版：原实现只剥一层 "../"，".."、"....//"、"..\\" 都能绕过指向存储目录之外的路径。
     * 现统一把反斜杠视为分隔符后按段过滤，丢弃所有 "." / ".." 段，结果只由普通名称段组成。
     */
    private function safePath(string $path): string
    {
        $segments = explode('/', str_replace('\\', '/', $path));

        return implode('/', array_filter(
            $segments,
            fn(string $segment): bool => $segment !== '' && $segment !== '.' && $segment !== '..',
        ));
    }

    /**
     * 把存储根下的相对路径解析为绝对路径，并确保仍在存储根内（纵深防御，配合 safePath 双保险）。
     * 目标允许尚未创建（mkdir / 上传新子目录），此时回溯到最近存在的祖先做归属校验。
     *
     * @return string 规范化为正向斜杠的绝对路径
     */
    private function storagePath(string $relative): string
    {
        $clean = trim($this->safePath($relative), '/');

        return $this->confineToStorage($clean === '' ? $this->storageReal : $this->storageReal . '/' . $clean);
    }

    /**
     * 校验绝对路径仍位于存储根内，越界抛"非法目录路径"；原样返回入参。
     * 目标允许尚不存在（mkdir / 上传新子目录），此时回溯到最近可解析的祖先做归属校验。
     */
    private function confineToStorage(string $fullPath): string
    {
        if ($this->insideStorage($this->normalizePath($fullPath)) === '') {
            throw new ApiException('非法目录路径');
        }

        return $fullPath;
    }

    /** 统一为正向斜杠，保证与 storageReal 的前缀比较不因 Windows 反斜杠而失效 */
    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * 返回规范化后仍位于存储根内的路径；越界返回空串。
     * 目标可尚不存在（mkdir / 上传新子目录），此时回溯到最近能解析出真实路径的祖先再校验。
     */
    private function insideStorage(string $fullPath): string
    {
        $base = $this->storageReal;

        // Windows 下 file_exists(".../....") 会当作当前目录返回 true 而 realpath 解析失败，
        // 故以 realpath 结果为准，解析不出来就继续向上回溯。
        $probe = $fullPath;
        $real = '';
        while ($probe !== $base && $real === '') {
            $candidate = $this->normalizePath((string) realpath($probe));
            if ($candidate !== '') {
                $real = $candidate;
            } else {
                $parent = dirname($probe);
                if ($parent === $probe) {
                    break;
                }
                $probe = $parent;
            }
        }

        if ($real === '') {
            $real = $base;
        }

        return $this->isWithinStorage($real) ? $fullPath : '';
    }

    /** 路径归一化后是否位于存储根内（含存储根自身）；前缀比较带分隔符，避免 cloud_file_evil 误判 */
    private function isWithinStorage(string $normalizedPath): bool
    {
        $base = $this->storageReal;

        return $normalizedPath === $base || str_starts_with($normalizedPath, $base . '/');
    }

    /** 旧 scanAllDirs() */
    private function scanAllDirs(string $base, string $rel = ''): array
    {
        $res = [];
        $basePath = $base . ($rel ? '/' . $rel : '');
        if (!is_dir($basePath)) {
            return [];
        }
        $dh = opendir($basePath);
        while ($f = readdir($dh)) {
            if ($f == '.' || $f == '..') {
                continue;
            }
            $full = $basePath . '/' . $f;
            if (is_dir($full)) {
                $subRel = $rel ? ($rel . '/' . $f) : $f;
                $res[] = $subRel;
                $res = array_merge($res, $this->scanAllDirs($base, $subRel));
            }
        }
        closedir($dh);

        return $res;
    }

    /**
     * 旧 globalSearchFiles()。原样保留未登录返回 [] 的判断。
     * @return array<int, array<string, mixed>>
     */
    private function globalSearchFiles(string $baseDir, string $keyword, string $relPath = ''): array
    {
        if (!isset($_SESSION['login']) || !$_SESSION['login']) {
            return [];
        }
        $result = [];
        $currentFull = rtrim($baseDir, '/') . ($relPath ? '/' . $relPath : '');
        if (!is_dir($currentFull)) {
            return [];
        }
        $dh = opendir($currentFull);
        while ($f = readdir($dh)) {
            if ($f == '.' || $f == '..') {
                continue;
            }
            $fileFull = $currentFull . '/' . $f;
            $subRel = ($relPath === '') ? $f : $relPath . '/' . $f;
            if (stripos($f, $keyword) !== false) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                $result[] = [
                    'full_rel' => $subRel,
                    'name' => $f,
                    'parent_dir' => $relPath,
                    'is_dir' => is_dir($fileFull),
                    'size' => is_dir($fileFull) ? 0 : filesize($fileFull),
                    'mtime' => date('Y-m-d H:i', filemtime($fileFull)),
                    'ext' => $ext,
                ];
            }
            if (is_dir($fileFull)) {
                $childList = $this->globalSearchFiles($baseDir, $keyword, $subRel);
                $result = array_merge($result, $childList);
            }
        }
        closedir($dh);

        return $result;
    }

    /** 旧代码中的站点根地址拼接（HTTP_HOST 驱动，原样保留） */
    private function siteRoot(): string
    {
        return 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/';
    }

    /**
     * POST login。成功：session 写入 + data.login=true。
     * @return array<string, mixed>
     */
    public function login(string $pwd): array
    {
        // hash_equals 恒定时间比对，避免按字符逐位短路的时序侧信道
        if (hash_equals($this->adminPwd, $pwd)) {
            $_SESSION['login'] = true;

            return ['login' => true];
        }
        throw new ApiException('密码错误');
    }

    /**
     * GET logout。等价 session_destroy()。
     * @return array<string, mixed>
     */
    public function logout(): array
    {
        session_destroy();

        return ['login' => false];
    }

    /**
     * GET check。
     * @return array<string, mixed>
     */
    public function check(): array
    {
        $isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'];

        return ['login' => $isLoggedIn];
    }

    /**
     * POST global_search。
     * @return array<string, mixed>
     */
    public function globalSearch(string $kw): array
    {
        $kw = $this->safePath($kw);
        if ($kw === '') {
            return ['list' => []];
        }

        return ['list' => $this->globalSearchFiles($this->storageDir, $kw)];
    }

    /**
     * POST create_share。token 为 bin2hex(random_bytes(16))，storage/share_temp/<token>.txt 存储格式保留。
     * @return array<string, mixed>
     */
    public function createShare(string $currentFolder, string $shareFile, bool $isDirShare, int $exp, string $pwd): array
    {
        $currentFolder = $this->safePath($currentFolder);
        $shareFile = $this->safePath($shareFile);
        // 分享 token 是凭据，必须用 CSPRNG；md5(uniqid().time()) 可预测。
        // 长度仍为 32 位十六进制，与旧格式（md5）一致，不影响既有 token 展示与文件名规则。
        $token = bin2hex(random_bytes(16));
        $expireTime = $exp === 0 ? 0 : time() + $exp * 3600;
        $data = json_encode([
            'token' => $token,
            'path' => $currentFolder,
            'file' => $shareFile,
            'is_dir' => $isDirShare,
            'pwd' => $pwd,
            'expire' => $expireTime,
            'create_time' => time(),
        ], JSON_UNESCAPED_UNICODE);
        file_put_contents($this->shareDir . '/' . $token . '.txt', $data);
        $shareUrl = $this->siteRoot() . '?share=' . $token;

        return ['url' => $shareUrl, 'pwd' => $pwd, 'token' => $token];
    }

    /**
     * GET get_share_list。
     * @return array<string, mixed>
     */
    public function getShareList(): array
    {
        $list = [];
        if (is_dir($this->shareDir)) {
            $dh = opendir($this->shareDir);
            while ($f = readdir($dh)) {
                if ($f == '.' || $f == '..') {
                    continue;
                }
                if (pathinfo($f, PATHINFO_EXTENSION) !== 'txt') {
                    continue;
                }
                $token = pathinfo($f, PATHINFO_FILENAME);
                $filePath = $this->shareDir . '/' . $f;
                $raw = file_get_contents($filePath);
                $info = json_decode((string) $raw, true);
                if (!is_array($info)) {
                    continue;
                }
                $now = time();
                $isExpire = ($info['expire'] !== 0) && ($now > $info['expire']);
                $list[] = [
                    'token' => $token,
                    'url' => $this->siteRoot() . '?share=' . $token,
                    'name' => $info['file'],
                    'parent_path' => $info['path'],
                    'is_dir' => $info['is_dir'],
                    'pwd' => $info['pwd'],
                    'expire' => $info['expire'],
                    'create_time' => $info['create_time'],
                    'is_expire' => $isExpire,
                    'expire_text' => $info['expire'] === 0 ? '永久有效' : date('Y-m-d H:i', $info['expire']),
                ];
            }
            closedir($dh);
            usort($list, fn($a, $b) => $b['create_time'] - $a['create_time']);
        }

        return ['list' => $list];
    }

    /**
     * POST delete_share。
     * @return array<string, mixed>
     */
    public function deleteShare(string $token): array
    {
        $token = $this->safePath($token);
        $file = $this->shareDir . '/' . $token . '.txt';
        if (file_exists($file)) {
            unlink($file);

            return [];
        }
        throw new ApiException('分享不存在');
    }

    /**
     * POST share_info（公开）：按 token 读取分享元信息。
     * 分享设置了提取密码且未传对时：未传密码 → need_pwd=true（前端显示密码框）；
     * 传错 → 400 提取密码错误。过期返回 410。
     * @return array<string, mixed>
     */
    public function shareInfo(string $token, string $pwd): array
    {
        $info = $this->readShare($token);
        $needPwd = ($info['pwd'] ?? '') !== '';
        if ($needPwd && $pwd === '') {
            return ['need_pwd' => true, 'is_dir' => (bool) $info['is_dir'], 'expire_text' => $this->shareExpireText($info)];
        }
        if ($needPwd && !hash_equals((string) $info['pwd'], $pwd)) {
            throw new ApiException('提取密码错误', 400);
        }

        return [
            'need_pwd' => false,
            'name' => (string) $info['file'],
            'is_dir' => (bool) $info['is_dir'],
            'expire_text' => $this->shareExpireText($info),
        ];
    }

    /**
     * GET share_download / share_stream（公开）：校验 token/密码/有效期后定位目标。
     * 返回 {dir, name, is_dir}，目录走 zip 打包，文件走直接下载或预览。
     * @return array{dir: string, name: string, is_dir: bool}
     */
    public function resolveShare(string $token, string $pwd): array
    {
        $info = $this->readShare($token);
        if (($info['pwd'] ?? '') !== '' && !hash_equals((string) $info['pwd'], $pwd)) {
            throw new ApiException('提取密码错误', 400);
        }

        return ['dir' => (string) $info['path'], 'name' => (string) $info['file'], 'is_dir' => (bool) $info['is_dir']];
    }

    /**
     * 读取并校验分享记录文件；不存在 404，过期 410。
     * @return array<string, mixed>
     */
    private function readShare(string $token): array
    {
        $file = $this->shareDir . '/' . $this->safePath($token) . '.txt';
        if (!is_file($file)) {
            throw new ApiException('分享不存在', 404);
        }
        $info = json_decode((string) file_get_contents($file), true);
        if (!is_array($info)) {
            throw new ApiException('分享不存在', 404);
        }
        if (($info['expire'] ?? 0) !== 0 && time() > (int) $info['expire']) {
            throw new ApiException('分享已过期', 410);
        }

        return $info;
    }

    private function shareExpireText(array $info): string
    {
        return ($info['expire'] ?? 0) === 0 ? '永久有效' : date('Y-m-d H:i', (int) $info['expire']);
    }

    /**
     * POST mkdir。返回响应 message（'目录创建成功' / '目录已存在'），错误抛 ApiException。
     */
    public function mkdir(string $currentFolder, string $targetSub): string
    {
        $currentFolder = $this->safePath($currentFolder);
        $targetSub = $this->safePath(trim($targetSub));
        if ($targetSub === '') {
            throw new ApiException('目录名称不能为空');
        }
        $realFolder = $this->storageDir . '/' . $currentFolder;
        $fullMk = rtrim($realFolder, '/') . '/' . $targetSub;
        $storageReal = realpath($this->storageDir);
        $parentReal = realpath(rtrim($realFolder, '/'));
        if (!$parentReal || strpos($parentReal, $storageReal) !== 0) {
            throw new ApiException('非法目录路径');
        }
        if (!file_exists($fullMk)) {
            if (mkdir($fullMk, 0755, true)) {
                return '目录创建成功';
            }

            throw new ApiException('目录创建失败，服务器权限不足');
        }

        return '目录已存在';
    }

    /**
     * POST upload。相对路径子目录存在时才可写入（与旧行为一致：目录不存在则写入失败）。
     * @return array<string, mixed>
     */
    public function upload(string $currentFolder, array $file, string $relativePath): array
    {
        $realFolder = $this->storagePath($currentFolder);
        $oriName = (string) $file['name'];
        $tmpPath = (string) $file['tmp_name'];
        $fileSize = (int) $file['size'];
        if ($fileSize <= 0) {
            throw new ApiException('文件为空');
        }
        $safeName = $this->safeFileName($oriName);
        $relPath = $this->safePath(trim($relativePath));
        if ($relPath !== '') {
            // 落地目录 = 当前目录 + relative_path 的目录部分，confineToStorage 确保仍在存储根内
            $targetDir = $this->confineToStorage($realFolder . '/' . dirname($relPath));
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $saveFull = $targetDir . '/' . $safeName;
        } else {
            $saveFull = $realFolder . '/' . $safeName;
        }
        if (move_uploaded_file($tmpPath, $saveFull)) {
            return [];
        }
        throw new ApiException('服务器写入失败，检查目录权限0755');
    }

    /**
     * POST change_pwd。写 pwd.config.txt 后销毁会话。
     * @return array<string, mixed>
     */
    public function changePwd(string $p1, string $p2): array
    {
        if ($p1 !== $p2) {
            throw new ApiException('两次密码不一致');
        }
        file_put_contents($this->pwdConfigFile, $p1);
        session_destroy();

        return [];
    }

    /**
     * GET list_dir。
     * @return array<string, mixed>
     */
    public function listDir(string $currentFolder, string $sortby, string $sortorder): array
    {
        $currentFolder = $this->safePath($currentFolder);
        if (!in_array($sortby, self::ALLOW_SORTBY, true)) {
            $sortby = '';
        }
        if (!in_array($sortorder, self::ALLOW_ORDER, true)) {
            $sortorder = '';
        }
        $realFolder = $this->storagePath($currentFolder);
        $list = [];
        // Windows 下 is_dir(".../....") 返回 true 而 opendir 失败，readdir(false) 会直接抛 TypeError；
        // 以 realpath 能否解析作为"目录真实可用"的判据
        $dh = realpath($realFolder) !== false ? opendir($realFolder) : false;
        if ($dh !== false) {
            while ($f = readdir($dh)) {
                if ($f == '.' || $f == '..') {
                    continue;
                }
                $full = $realFolder . '/' . $f;
                $isDir = is_dir($full);
                // 只 stat 一次：旧实现同一条目调用了两次 filesize()
                $size = $isDir ? 0 : (int) filesize($full);
                $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
                $list[] = [
                    'name' => $f,
                    'is_dir' => $isDir,
                    'size' => $size,
                    'size_text' => $isDir ? '文件夹' : $this->formatSize($size),
                    'mtime' => date('Y-m-d H:i', filemtime($full)),
                    'ext' => $ext,
                ];
            }
            closedir($dh);
        }
        usort($list, function ($a, $b) use ($sortby, $sortorder) {
            $dirA = $a['is_dir'] ? 1 : 0;
            $dirB = $b['is_dir'] ? 1 : 0;
            if ($dirA != $dirB) {
                return $dirB - $dirA;
            }
            if (empty($sortby)) {
                return strcmp($a['name'], $b['name']);
            }
            if ($sortby == 'name') {
                $cmp = strcmp($a['name'], $b['name']);

                return $sortorder == 'desc' ? -$cmp : $cmp;
            }
            if ($sortby == 'mtime') {
                $t1 = strtotime($a['mtime']);
                $t2 = strtotime($b['mtime']);
                if ($t1 == $t2) {
                    return 0;
                }
                $cmp = $t1 > $t2 ? 1 : -1;

                return $sortorder == 'desc' ? -$cmp : $cmp;
            }

            return strcmp($a['name'], $b['name']);
        });
        $allDirs = $this->scanAllDirs($this->storageDir);

        return [
            'list' => $list,
            'dirs' => $allDirs,
            'current_folder' => $currentFolder,
        ];
    }

    /**
     * POST delete。
     * @return array<string, mixed>
     */
    public function delete(string $currentFolder, string $target): array
    {
        // 空目标解析到当前目录自身，旧版会因此删光整个当前文件夹；显式拒绝
        if ($this->safePath($target) === '') {
            return [];
        }
        $candidate = $this->normalizePath($this->storagePath($currentFolder . '/' . $target));
        // 旧版缺陷修复：target 为空时解析到当前目录自身，不加护栏会被整体删掉
        $inside = $this->insideStorage($candidate);
        if ($inside === '') {
            return [];
        }
        $realPath = realpath($inside);
        if ($realPath === false) {
            // 保持旧行为：目标不存在时静默返回成功
            return [];
        }
        $targetReal = $this->normalizePath($realPath);
        if (!$this->isWithinStorage($targetReal)) {
            return [];
        }
        if (is_dir($targetReal)) {
            $di = new \RecursiveDirectoryIterator($targetReal, \RecursiveDirectoryIterator::SKIP_DOTS);
            $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($ri as $entry) {
                // strict_types 下 SplFileInfo 不会隐式转 string，必须显式取路径；目录需 rmdir
                $entryPath = $entry->getRealPath();
                is_dir($entryPath) ? rmdir($entryPath) : unlink($entryPath);
            }
            rmdir($targetReal);
        } else {
            unlink($targetReal);
        }

        return [];
    }

    /**
     * POST batch_delete。
     * @param array<int, mixed> $batchList
     * @return array<string, mixed>
     */
    public function batchDelete(string $currentFolder, array $batchList): array
    {
        // 逐项复用 delete：路径归属校验、空项防护与目录递归删除只保留一份实现
        foreach ($batchList as $item) {
            $this->delete($currentFolder, (string) $item);
        }

        return [];
    }

    /**
     * POST rename。
     * @return array<string, mixed>
     */
    public function rename(string $currentFolder, string $old, string $new): array
    {
        $old = $this->safePath($old);
        $new = $this->safeFileName(trim($new));
        $realFolder = $this->storagePath($currentFolder);
        $oldP = $realFolder . '/' . $old;
        $newP = $realFolder . '/' . $new;
        if (file_exists($oldP) && !file_exists($newP)) {
            rename($oldP, $newP);

            return [];
        }
        throw new ApiException('重命名失败');
    }

    /**
     * POST move。
     * @return array<string, mixed>
     */
    public function move(string $currentFolder, string $src, string $dstDir): array
    {
        $src = $this->safePath($src);
        $dstDir = $this->safePath($dstDir);
        $realFolder = $this->storagePath($currentFolder);
        $srcP = $realFolder . '/' . $src;
        $dstP = $this->storagePath($dstDir) . '/' . $src;
        if (file_exists($srcP) && !file_exists($dstP)) {
            rename($srcP, $dstP);

            return [];
        }
        throw new ApiException('移动失败');
    }

    /**
     * GET download（流式输出，控制器内联实现）。
     *
     * @return array{path: string, name: string}
     */
    public function resolveDownload(string $dir, string $file): array
    {
        $fn = $this->safePath($file);
        $full = $this->storagePath($dir . '/' . $fn);
        if (!file_exists($full)) {
            throw new ApiException('文件不存在');
        }

        return ['path' => $full, 'name' => $fn];
    }

    /**
     * GET zip（流式输出，控制器内联实现）。
     *
     * @return array{file: string, name: string}
     */
    public function createZip(string $dir, string $folder): array
    {
        $zipName = $this->safePath($folder);
        $srcDir = $this->storagePath($dir . '/' . $zipName);
        // 并发打包时 uniqid()（毫秒精度）可能重复，覆盖彼此的临时文件；改用 CSPRNG 文件名
        $zipFile = $this->zipTemp . '/' . bin2hex(random_bytes(8)) . '.zip';
        if (!class_exists('ZipArchive')) {
            throw new ApiException('ZipArchive未启用');
        }
        if (!is_dir($srcDir)) {
            throw new ApiException('目录不存在', 404);
        }
        $zip = new \ZipArchive();
        if (!$zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            throw new ApiException('创建zip失败');
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($srcDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
        );
        foreach ($files as $f) {
            $rel = substr($f->getRealPath(), strlen(realpath($srcDir)) + 1);
            // strict_types 下 SplFileInfo 不能隐式转 string，必须显式 getRealPath()
            $f->isDir() ? $zip->addEmptyDir($rel) : $zip->addFile($f->getRealPath(), $rel);
        }
        // 空目录时 close() 成功但不会落盘任何文件，补一个目录条目保证 zip 生成
        if ($zip->numFiles === 0) {
            $zip->addEmptyDir($zipName);
        }
        if (!$zip->close()) {
            throw new ApiException('创建zip失败');
        }
        if (!is_file($zipFile)) {
            throw new ApiException('创建zip失败');
        }

        return ['file' => $zipFile, 'name' => $zipName];
    }

    /**
     * GET stream（流式输出，控制器内联实现）。
     */
    public function resolveStream(string $fileRel): string
    {
        $full = $this->storagePath($fileRel);
        if (!file_exists($full)) {
            throw new ApiException('文件不存在');
        }

        return $full;
    }

    public function isImageExt(string $ext): bool
    {
        return $this->isImage($ext);
    }

    public function isVideoExt(string $ext): bool
    {
        return $this->isVideo($ext);
    }

    public function isPdfExt(string $ext): bool
    {
        return $this->isPdf($ext);
    }

    public function getZipTemp(): string
    {
        return $this->zipTemp;
    }
}
