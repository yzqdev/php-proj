<?php
declare(strict_types=1);

/**
 * 日志查看页面
 * 显示最近的测速日志和统计信息。
 */

require_once dirname(__DIR__) . '/lib/Logger.php';

$stats = Logger::getStats();
$recentLogs = Logger::getRecent(50);
$currentPage = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>测速日志 - 日志查看器</title>
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;background:linear-gradient(135deg,#0c0e1a 0%,#1a1b2e 100%);color:#f8f9fa;min-height:100vh}
.container{max-width:1100px;margin:0 auto;padding:24px 16px}
.header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.header h1{font-size:1.4rem;font-weight:700;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.header .actions{display:flex;gap:8px;align-items:center}
.btn{padding:8px 16px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:rgba(255,255,255,0.06);color:#f8f9fa;cursor:pointer;font-size:0.85rem;font-family:inherit;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn:hover{background:rgba(255,255,255,0.12);border-color:rgba(255,255,255,0.2)}
.btn-primary{background:linear-gradient(135deg,rgba(102,126,234,0.3),rgba(118,75,162,0.3));border-color:rgba(102,126,234,0.4)}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
.stat-card{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:16px;padding:16px;text-align:center}
.stat-card .label{font-size:0.72rem;color:#6c757d;font-weight:500;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:6px}
.stat-card .value{font-size:1.8rem;font-weight:700}
.stat-card.total .value{color:#667eea}
.stat-card.tests .value{color:#11998e}
.stat-card.errors .value{color:#e74c3c}
.stat-card.size .value{color:#ffa502;font-size:1.4rem}
.log-container{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:16px;overflow:hidden}
.log-header{padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;justify-content:space-between;align-items:center}
.log-header h2{font-size:1rem;font-weight:600}
.log-header .date{font-size:0.8rem;color:#6c757d}
.log-table{width:100%;border-collapse:collapse;font-size:0.82rem}
.log-table th{text-align:left;padding:10px 14px;background:rgba(255,255,255,0.04);color:#6c757d;font-weight:500;text-transform:uppercase;letter-spacing:0.05em;font-size:0.72rem;border-bottom:1px solid rgba(255,255,255,0.08)}
.log-table td{padding:10px 14px;border-bottom:1px solid rgba(255,255,255,0.04);vertical-align:top}
.log-table tr:hover td{background:rgba(255,255,255,0.03)}
.level-tag{display:inline-block;padding:2px 8px;border-radius:6px;font-size:0.72rem;font-weight:600;text-transform:uppercase}
.level-INFO{background:rgba(17,153,142,0.2);color:#38ef7d}
.level-WARNING{background:rgba(255,165,2,0.2);color:#ffa502}
.level-ERROR{background:rgba(231,76,60,0.2);color:#e74c3c}
.level-TEST{background:rgba(102,126,234,0.2);color:#667eea}
.msg-test{color:#667eea;font-weight:500}
.msg-info{color:#f8f9fa}
.context-data{font-family:'JetBrains Mono','Fira Code',monospace;font-size:0.72rem;color:#adb5bd;word-break:break-all;max-width:400px}
.empty-state{text-align:center;padding:48px 20px;color:#6c757d}
.empty-state .icon{font-size:3rem;margin-bottom:12px;opacity:0.5}
@media all and (max-width:768px){.stats-grid{grid-template-columns:repeat(2,1fr)}.context-data{max-width:200px}}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📋 测速日志查看器</h1>
        <div class="actions">
            <a href="../index.php" class="btn">← 返回测速</a>
            <button class="btn btn-primary" onclick="location.reload()">🔄 刷新</button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card total">
            <div class="label">总日志数</div>
            <div class="value"><?php echo $stats['total']; ?></div>
        </div>
        <div class="stat-card tests">
            <div class="label">测速次数</div>
            <div class="value"><?php echo $stats['tests']; ?></div>
        </div>
        <div class="stat-card errors">
            <div class="label">错误数</div>
            <div class="value"><?php echo $stats['errors']; ?></div>
        </div>
        <div class="stat-card size">
            <div class="label">日志大小</div>
            <div class="value"><?php echo htmlspecialchars($stats['file_size_human']); ?></div>
        </div>
    </div>

    <div class="log-container">
        <div class="log-header">
            <h2>最近日志（<?php echo count($recentLogs); ?> 条）</h2>
            <span class="date"><?php echo htmlspecialchars($currentPage); ?></span>
        </div>
        <?php if (empty($recentLogs)): ?>
        <div class="empty-state">
            <div class="icon">📭</div>
            <p>暂无日志记录</p>
            <p style="margin-top:8px;font-size:0.8rem">完成一次测速后，日志将自动记录在此</p>
        </div>
        <?php else: ?>
        <table class="log-table">
            <thead>
                <tr>
                    <th style="width:160px">时间</th>
                    <th style="width:80px">级别</th>
                    <th style="width:200px">消息</th>
                    <th>详情</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentLogs as $log): ?>
                <tr>
                    <td style="white-space:nowrap;font-family:monospace;font-size:0.78rem;color:#adb5bd">
                        <?php
                            $ts = $log['datetime'] ?? '';
                            if ($ts) {
                                $dt = new DateTime($ts);
                                echo htmlspecialchars($dt->format('H:i:s.u'));
                            } else {
                                echo htmlspecialchars(substr($log['timestamp'] ?? '', 11, 12));
                            }
                        ?>
                    </td>
                    <td>
                        <span class="level-tag level-<?php echo htmlspecialchars($log['level_name'] ?? 'INFO'); ?>">
                            <?php echo htmlspecialchars($log['level_name'] ?? 'INFO'); ?>
                        </span>
                    </td>
                    <td class="<?php echo ($log['message'] ?? '') === '测速结果' ? 'msg-test' : 'msg-info'; ?>">
                        <?php echo htmlspecialchars($log['message'] ?? ''); ?>
                    </td>
                    <td>
                        <?php if (!empty($log['context'])): ?>
                        <div class="context-data"><?php echo htmlspecialchars(json_encode($log['context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></div>
                        <?php else: ?>
                        <span style="color:#495057">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div style="margin-top:16px;font-size:0.75rem;color:#495057;text-align:center">
        日志文件：<code style="color:#adb5bd"><?php echo htmlspecialchars(str_replace(dirname(__DIR__) . '/', '', Logger::getCurrentFile())); ?></code>
        &nbsp;·&nbsp; 保留 <?php echo Logger::RETENTION_DAYS; ?> 天 &nbsp;·&nbsp; 共 <?php echo count(glob(Logger::getLogDir() . '/speedtest-*.log')) ?: 0; ?> 个日志文件
    </div>
</div>

<script>
// 自动刷新（每 30 秒）
setTimeout(function(){ location.reload(); }, 30000);
</script>
</body>
</html>
