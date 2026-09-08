<?php
declare(strict_types=1);

/**
 * 纯 PHP 网络测速工具
 *
 * 文件结构：
 *   index.php           — 本文件（主界面 + 内联 CSS/JS 测速逻辑）
 *   lib/Logger.php      — Monolog 日志封装
 *   test/ping.php       — 延迟测试端点
 *   test/info.php       — IP 信息端点
 *   test/download.php   — 下载测试数据生成
 *   test/upload.php     — 上传测试数据接收
 *   test/log.php        — 日志接收端点
 *   test/logs.php       — 日志查看页面
 *
 * 测速流程：
 *   1. 获取客户端 IP 信息
 *   2. 测量网络延迟 (Ping) 和抖动 (Jitter)
 *   3. 测量下载速度
 *   4. 测量上传速度
 *   5. 发送结果到日志系统
 */

// ============ 测速参数配置 ============
$config = [
    'download_size' => 20 * 1024 * 1024,
    'upload_size'   => 10 * 1024 * 1024,
    'ping_count'    => 10,
    'get_isp'       => false,
    'title'         => '中国科学技术大学测速网站',
    'footer'        => '本测速服务器位于中国科学技术大学网络信息中心',
    'source_url'    => 'https://github.com/bg6cq/speedtest',
    'ipv4_url'      => 'http://test.ustc.edu.cn/',
    'ipv6_url'      => 'http://test6.ustc.edu.cn/',
];

function _sizeToBytes(string $s): int {
    $s = trim($s);
    if ($s === '' || $s === '-1') return 0;
    $u = strtolower(substr($s, -1));
    $v = (int) $s;
    return match ($u) { 'g' => $v*1024*1024*1024, 'm' => $v*1024*1024, 'k' => $v*1024, default => $v };
}
$uploadLimit = _sizeToBytes(ini_get('post_max_size')) - 1024*1024;
if ($uploadLimit > 1024*1024) {
    $config['upload_size'] = min($config['upload_size'], $uploadLimit);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no,user-scalable=no" />
<meta charset="UTF-8" />
<link rel="shortcut icon" href="favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<title><?php echo htmlspecialchars($config['title']); ?></title>
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:linear-gradient(135deg,#0c0e1a 0%,#1a1b2e 50%,#0d1025 100%);color:#f8f9fa;min-height:100vh;overflow-x:hidden;text-align:center}
body.light-mode{background:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);color:#2d3436}
body.light-mode .glass-card{background:rgba(255,255,255,0.85);border-color:rgba(0,0,0,0.08)}
body.light-mode .start-btn{border-color:rgba(102,126,234,0.3);color:#667eea}
body.light-mode .start-btn.running{background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff}
body.light-mode .phase-step.done{color:#27ae60}
body.light-mode .phase-step.active{color:#667eea}
.bg-animation{position:fixed;top:0;left:0;width:100%;height:100%;z-index:-1;overflow:hidden}
.bg-orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:0.15;animation:float 20s ease-in-out infinite}
.bg-orb-1{width:400px;height:400px;background:#667eea;top:-100px;right:-100px}
.bg-orb-2{width:300px;height:300px;background:#764ba2;bottom:-50px;left:-50px;animation-delay:-7s}
.bg-orb-3{width:250px;height:250px;background:#11998e;top:40%;left:50%;animation-delay:-14s}
@keyframes float{0%,100%{transform:translate(0,0) scale(1)}25%{transform:translate(30px,-30px) scale(1.1)}50%{transform:translate(-20px,20px) scale(0.9)}75%{transform:translate(10px,40px) scale(1.05)}}
.container{max-width:700px;margin:0 auto;padding:60px 16px 40px}
.header{margin-bottom:24px}
.logo{font-size:1.4rem;font-weight:800;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:4px;letter-spacing:-0.02em}
.subtitle{font-size:0.8rem;color:#6c757d;font-weight:400}
.top-actions{position:fixed;top:12px;right:12px;display:flex;gap:6px;z-index:100}
.icon-btn{width:38px;height:38px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(255,255,255,0.06);color:#f8f9fa;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;transition:all .2s;backdrop-filter:blur(10px)}
.icon-btn:hover{background:rgba(255,255,255,0.15);border-color:rgba(255,255,255,0.25);transform:translateY(-1px)}
body.light-mode .icon-btn{background:rgba(255,255,255,0.8);border-color:rgba(0,0,0,0.1);color:#2d3436}
.phase-indicator{display:flex;align-items:center;justify-content:center;gap:4px;margin-bottom:16px;padding:10px 0;flex-wrap:wrap}
.phase-step{display:flex;align-items:center;gap:5px;padding:6px 12px;border-radius:20px;font-size:0.72rem;font-weight:500;color:#6c757d;transition:all .4s cubic-bezier(.4,0,.2,1);white-space:nowrap;max-width:45%}
.phase-step .dot{width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.5;transition:all .4s ease}
.phase-step.active{color:#667eea;background:rgba(102,126,234,.1);box-shadow:0 0 12px rgba(102,126,234,.2)}
.phase-step.active .dot{opacity:1;box-shadow:0 0 8px currentColor;animation:pulse 1.5s ease-in-out infinite}
.phase-step.done{color:#38ef7d}
.phase-step.done .dot{opacity:1}
.phase-sep{width:16px;height:2px;background:rgba(255,255,255,0.1);border-radius:1px;flex-shrink:0}
.phase-label{font-size:0.75rem;color:#adb5bd;margin-bottom:20px;font-weight:500;letter-spacing:0.03em;transition:color .3s}
body.light-mode .phase-label{color:#6c757d}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.4)}}
.glass-card{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:20px;backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);padding:24px 16px;margin-bottom:16px;transition:all .3s}
.glass-card:hover{border-color:rgba(255,255,255,0.18);background:rgba(255,255,255,0.08)}
.start-btn-wrapper{margin-bottom:20px}
.start-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:200px;height:56px;border-radius:18px;border:1.5px solid rgba(102,126,234,0.3);background:linear-gradient(135deg,rgba(102,126,234,0.15),rgba(118,75,162,0.15));color:#a0a7f5;font-size:1rem;font-weight:600;cursor:pointer;transition:all .3s cubic-bezier(.4,0,.2,1);position:relative;overflow:hidden;font-family:inherit;letter-spacing:0.02em;backdrop-filter:blur(10px)}
.start-btn::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.08),transparent);transition:left .5s}
.start-btn:hover::before{left:100%}
.start-btn:hover{border-color:rgba(102,126,234,0.5);box-shadow:0 0 24px rgba(102,126,234,0.2);transform:translateY(-2px)}
.start-btn.running{background:linear-gradient(135deg,#e74c3c,#c0392b);border-color:rgba(231,76,60,0.4);color:#fff;box-shadow:0 0 24px rgba(231,76,60,0.3)}
.start-btn.running::before{animation:shimmer 2s linear infinite}
@keyframes shimmer{0%{left:-100%}100%{left:100%}}
.start-btn .icon{font-size:1.2rem}
.metrics-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
.metric-card{text-align:center;position:relative;padding:18px 12px}
.metric-card .label{font-size:0.72rem;color:#6c757d;font-weight:500;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px}
.metric-card .value{font-size:2rem;font-weight:700;line-height:1.2;margin-bottom:2px;transition:color .3s}
.metric-card .unit{font-size:0.7rem;color:#6c757d;font-weight:400}
.metric-card .icon{position:absolute;top:10px;right:10px;font-size:1rem;opacity:.4}
.metric-card.dl .value{color:#764ba2}
.metric-card.ul .value{color:#11998e}
.metric-card.ping .value{color:#ff6b6b}
.metric-card.jitter .value{color:#ffa502}
.gauge-card{padding:16px 12px}
.gauge-container{width:100%;height:160px;position:relative;overflow:visible}
.gauge-container canvas{width:100%;height:100%;position:absolute;top:0;left:0;z-index:0}
.gauge-info{text-align:center;position:absolute;z-index:2;top:78%;left:50%;transform:translate(-50%,-50%);white-space:nowrap;background:rgba(0,0,0,0.45);border-radius:10px;padding:3px 14px}
.gauge-info .gauge-label{font-size:0.75rem;color:#6c757d;font-weight:500;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px}
.gauge-info .gauge-value{font-size:2.2rem;font-weight:700;line-height:1.1}
.gauge-info .gauge-unit{font-size:0.72rem;color:#6c757d;font-weight:400}
.gauge-dl .gauge-value{color:#764ba2}
.gauge-ul .gauge-value{color:#11998e}
.ip-card{display:flex;align-items:center;justify-content:center;gap:10px;padding:12px 20px;opacity:0;transform:translateY(8px);transition:all .5s cubic-bezier(.4,0,.2,1)}
.ip-card.visible{opacity:1;transform:translateY(0)}
.ip-card .ip-icon{font-size:1.2rem}
.ip-card .ip-label{font-size:0.72rem;color:#6c757d;font-weight:500}
.ip-card .ip-value{font-size:0.9rem;font-weight:600;color:#f8f9fa;font-family:'JetBrains Mono','Fira Code',monospace}
.footer{margin-top:32px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.06);font-size:0.75rem;color:#495057}
.footer a{color:#667eea;text-decoration:none;transition:color .2s}
.footer a:hover{color:#764ba2;text-decoration:underline}
.footer .server-info{margin-top:4px;color:#343a40}
#privacyOverlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:9998;opacity:0;transition:opacity .3s}
#privacyOverlay.show{opacity:1}
#privacyPolicy{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(.95);width:90%;max-width:480px;max-height:80vh;overflow-y:auto;background:rgba(26,27,46,0.95);border:1px solid rgba(255,255,255,0.12);border-radius:20px;padding:28px;z-index:9999;opacity:0;transition:all .3s cubic-bezier(.4,0,.2,1);backdrop-filter:blur(20px);box-shadow:0 24px 48px rgba(0,0,0,0.4)}
#privacyPolicy.show{opacity:1;transform:translate(-50%,-50%) scale(1)}
body.light-mode #privacyPolicy{background:rgba(255,255,255,0.95);border-color:rgba(0,0,0,0.08)}
#privacyPolicy h2{font-size:1.2rem;font-weight:700;margin-bottom:12px;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
#privacyPolicy h4{font-size:0.85rem;font-weight:600;margin-bottom:8px;color:#adb5bd}
#privacyPolicy ul{text-align:left;padding-left:20px;margin-bottom:16px}
#privacyPolicy li{font-size:0.82rem;color:#ced4da;margin-bottom:6px;line-height:1.5}
#privacyPolicy .close-btn{display:block;width:100%;padding:10px;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.06);color:#f8f9fa;border-radius:12px;cursor:pointer;font-size:0.85rem;font-weight:500;transition:all .2s;font-family:inherit}
#privacyPolicy .close-btn:hover{background:rgba(255,255,255,0.12);border-color:rgba(255,255,255,0.25)}
@media all and (max-width:480px){body{font-size:0.85em}.metrics-grid{gap:8px}.glass-card{padding:16px 12px;border-radius:16px}.metric-card .value{font-size:1.6rem}.gauge-info .gauge-value{font-size:1.8rem}.phase-step{padding:5px 8px;font-size:0.65rem}.phase-sep{width:8px}.start-btn{width:160px;height:50px;font-size:0.9rem}.logo{font-size:1.15rem}}
::-webkit-scrollbar{width:6px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.15);border-radius:3px}
::-webkit-scrollbar-thumb:hover{background:rgba(255,255,255,0.25)}
::selection{background:rgba(102,126,234,0.3)}
</style>
</head>
<body>
<div class="bg-animation"><div class="bg-orb bg-orb-1"></div><div class="bg-orb bg-orb-2"></div><div class="bg-orb bg-orb-3"></div></div>
<div class="top-actions"><button class="icon-btn" id="themeToggle" onclick="toggleTheme()" title="切换主题">🌙</button></div>
<div class="container">
  <div class="header">
    <div class="logo"><?php echo htmlspecialchars($config['title']); ?></div>
    <div class="subtitle">
      <a href="<?php echo htmlspecialchars($config['ipv4_url']); ?>" style="color:#667eea;text-decoration:none">IPv4</a>
      &nbsp;/&nbsp;
      <a href="<?php echo htmlspecialchars($config['ipv6_url']); ?>" style="color:#667eea;text-decoration:none">IPv6</a>
    </div>
  </div>
  <div class="phase-indicator" id="phaseIndicator">
    <div class="phase-step pending" data-phase="ping"><span class="dot"></span>延迟</div>
    <div class="phase-sep"></div>
    <div class="phase-step pending" data-phase="download"><span class="dot"></span>下载</div>
    <div class="phase-sep"></div>
    <div class="phase-step pending" data-phase="upload"><span class="dot"></span>上传</div>
  </div>
  <div class="phase-label" id="phaseLabel">准备就绪</div>
  <div class="start-btn-wrapper">
    <div id="startStopBtn" class="start-btn" onclick="startStop()">
      <span class="icon">⚡</span><span id="startBtnText">开始测速</span>
    </div>
  </div>
  <div class="metrics-grid">
    <div class="glass-card metric-card ping"><div class="icon">📡</div><div class="label">网络延迟</div><div id="pingText" class="value">—</div><div class="unit">ms</div></div>
    <div class="glass-card metric-card jitter"><div class="icon">📊</div><div class="label">延迟抖动</div><div id="jitText" class="value">—</div><div class="unit">ms</div></div>
  </div>
  <div class="metrics-grid">
    <div class="glass-card gauge-card gauge-dl">
      <div class="gauge-container"><canvas id="dlMeter"></canvas>
        <div class="gauge-info"><div class="gauge-label">↓ 下载速度</div><div id="dlText" class="gauge-value">—</div><div class="gauge-unit">Mbps</div></div>
      </div>
    </div>
    <div class="glass-card gauge-card gauge-ul">
      <div class="gauge-container"><canvas id="ulMeter"></canvas>
        <div class="gauge-info"><div class="gauge-label">↑ 上传速度</div><div id="ulText" class="gauge-value">—</div><div class="gauge-unit">Mbps</div></div>
      </div>
    </div>
  </div>
  <div id="ipArea">
    <div class="glass-card ip-card" id="ipCard"><span class="ip-icon">🌍</span>
      <div><div class="ip-label">您的 IP 地址</div><div id="ip" class="ip-value"></div></div>
    </div>
  </div>
  <div class="footer">
    <div>
      这里可以获得
      <a href="<?php echo htmlspecialchars($config['source_url']); ?>" target="_blank">本站源代码</a>
      &nbsp;·&nbsp;
      <a href="#" onclick="togglePrivacy(true);return false">隐私说明</a>
      &nbsp;·&nbsp;
      <a href="test/logs.php" target="_blank">📋 查看日志</a>
    </div>
    <div class="server-info"><?php echo htmlspecialchars($config['footer']); ?></div>
  </div>
</div>
<div id="privacyOverlay" onclick="togglePrivacy(false)" style="display:none"></div>
<div id="privacyPolicy" style="display:none">
  <h2>🔒 隐私说明</h2>
  <h4>使用本测速网站，测试过程中以下信息会被处理：</h4>
  <ul>
    <li>🌐 客户端 IP 地址（仅用于获取 ISP 信息，可选）</li>
    <li>📊 下载 / 上传测试数据（随机二进制数据，不包含任何个人信息）</li>
    <li>📡 网络延迟和抖动测量数据</li>
    <li>📋 测试结果仅显示在当前页面，不保存到服务器</li>
  </ul>
  <button class="close-btn" onclick="togglePrivacy(false)">我知道了</button>
</div>
<script>
var CONFIG = <?php echo json_encode($config, JSON_UNESCAPED_UNICODE); ?>;
var state = {
    phase: 'idle', abort: false, ip: '', isp: '',
    pings: [], ping: 0, jitter: 0,
    dlSpeed: 0, ulSpeed: 0, dlProgress: 0, ulProgress: 0,
    currentXhr: null, startTime: 0
};
function format(d){d=Number(d);if(d<10)return d.toFixed(2);if(d<100)return d.toFixed(1);return d.toFixed(0)}
function mbpsToAmount(s){return 1-(1/Math.pow(1.3,Math.sqrt(s)))}
function oscillate(){return 1+0.02*Math.sin(Date.now()/100)}
function drawGauge(canvas,amount,isDl,progress){
    var ctx=canvas.getContext('2d'),dp=window.devicePixelRatio||1;
    var cw=canvas.clientWidth*dp,ch=canvas.clientHeight*dp;
    var sizScale=ch*0.0055,cx=cw/2,cy=ch-40*sizScale;
    var radius=ch/1.75-10*sizScale,lineWidth=14*sizScale;
    if(canvas.width!==cw||canvas.height!==ch){canvas.width=cw;canvas.height=ch}
    ctx.clearRect(0,0,cw,ch);
    var bgGrad=ctx.createLinearGradient(0,0,cw,0);
    bgGrad.addColorStop(0,'rgba(255,255,255,0.05)');bgGrad.addColorStop(.5,'rgba(255,255,255,0.10)');bgGrad.addColorStop(1,'rgba(255,255,255,0.05)');
    ctx.beginPath();ctx.strokeStyle=bgGrad;ctx.lineWidth=lineWidth;ctx.lineCap='round';
    ctx.arc(cx,cy,radius,-Math.PI*1.1,Math.PI*0.1);ctx.stroke();
    if(amount>0){
        var colors=isDl?['#667eea','#764ba2']:['#11998e','#38ef7d'];
        var fgGrad=ctx.createLinearGradient(cx-radius,cy,cx+radius,cy);
        fgGrad.addColorStop(0,colors[0]);fgGrad.addColorStop(1,colors[1]);
        ctx.shadowColor=colors[1];ctx.shadowBlur=15*sizScale;
        ctx.beginPath();ctx.strokeStyle=fgGrad;ctx.lineWidth=lineWidth;ctx.lineCap='round';
        ctx.arc(cx,cy,radius,-Math.PI*1.1,amount*Math.PI*1.2-Math.PI*1.1);ctx.stroke();
        ctx.shadowBlur=0;
    }
    if(typeof progress!=='undefined'&&progress>0){
        var barGrad=ctx.createLinearGradient(cw*0.3,0,cw*0.7,0);
        barGrad.addColorStop(0,'rgba(102,126,234,0.3)');barGrad.addColorStop(1,'rgba(118,75,162,0.3)');
        ctx.fillStyle=barGrad;
        var bw=cw*0.4*progress,bx=cw*0.3,by=ch-18*sizScale,bh=5*sizScale,r=bh/2;
        ctx.beginPath();ctx.moveTo(bx+r,by);ctx.lineTo(bx+bw-r,by);ctx.arcTo(bx+bw,by,bx+bw,by+r,r);
        ctx.lineTo(bx+bw,by+bh-r);ctx.arcTo(bx+bw,by+bh,bx+bw-r,by+bh,r);
        ctx.lineTo(bx+r,by+bh);ctx.arcTo(bx,by+bh,bx,by+bh-r,r);ctx.lineTo(bx,by+r);ctx.arcTo(bx,by,bx+r,by,r);
        ctx.closePath();ctx.fill();
    }
}
var phaseNames=['ping','download','upload'];
function setPhase(phase){
    if(phase===state.phase)return;state.phase=phase;
    var items=document.getElementById('phaseIndicator').querySelectorAll('.phase-step');
    items.forEach(function(item){
        var sn=item.getAttribute('data-phase'),si=phaseNames.indexOf(sn);
        item.classList.remove('active','done','pending');
        if(phase==='idle'||phase==='done'){if(phase==='done')item.classList.add('done');else item.classList.add('pending')}
        else{var ci=phaseNames.indexOf(phase);if(si<ci)item.classList.add('done');else if(si===ci)item.classList.add('active');else item.classList.add('pending')}
    });
    document.getElementById('phaseLabel').textContent={'idle':'准备就绪','ping':'测试延迟中...','download':'测试下载中...','upload':'测试上传中...','done':'测试完成 ✓'}[phase]||'准备就绪';
}
function updateMetrics(){
    document.getElementById('pingText').textContent=state.ping>0?format(state.ping):'—';
    document.getElementById('jitText').textContent=state.jitter>0?format(state.jitter):'—';
    document.getElementById('dlText').textContent=state.dlSpeed>0?format(state.dlSpeed):'—';
    document.getElementById('ulText').textContent=state.ulSpeed>0?format(state.ulSpeed):'—';
}
function updateIPCard(){var c=document.getElementById('ipCard');if(state.ip){document.getElementById('ip').textContent=state.ip;c.classList.add('visible')}}
function initUI(){
    drawGauge(document.getElementById('dlMeter'),0,true,0);
    drawGauge(document.getElementById('ulMeter'),0,false,0);
    document.getElementById('pingText').textContent='—';document.getElementById('jitText').textContent='—';
    document.getElementById('dlText').textContent='—';document.getElementById('ulText').textContent='—';
    document.getElementById('ip').textContent='';document.getElementById('ipCard').classList.remove('visible');
    setPhase('idle');
}
window.requestAnimationFrame=window.requestAnimationFrame||window.webkitRequestAnimationFrame||window.mozRequestAnimationFrame||window.msRequestAnimationFrame||function(cb){return setTimeout(cb,1000/60)};
function frame(){
    requestAnimationFrame(frame);
    if(state.dlSpeed>0){var a=mbpsToAmount(Number(state.dlSpeed*(state.phase==='download'?oscillate():1)));drawGauge(document.getElementById('dlMeter'),a,true,state.dlProgress)}
    if(state.ulSpeed>0){var a=mbpsToAmount(Number(state.ulSpeed*(state.phase==='upload'?oscillate():1)));drawGauge(document.getElementById('ulMeter'),a,false,state.ulProgress)}
}
frame();
function startStop(){
    if(state.phase!=='idle'&&state.phase!=='done'){
        state.abort=true;if(state.currentXhr){try{state.currentXhr.abort()}catch(e){}state.currentXhr=null}
        document.getElementById('startStopBtn').classList.remove('running');
        document.getElementById('startBtnText').textContent='开始测速';initUI();
    }else{
        state.abort=false;state.pings=[];state.ping=0;state.jitter=0;state.dlSpeed=0;state.ulSpeed=0;
        state.dlProgress=0;state.ulProgress=0;state.currentXhr=null;state.startTime=performance.now();
        document.getElementById('startStopBtn').classList.add('running');
        document.getElementById('startBtnText').textContent='终止测速';
        fetchInfo();
    }
}
function fetchInfo(){
    if(state.abort)return;var xhr=new XMLHttpRequest();state.currentXhr=xhr;
    var url='test/info.php?isp='+(CONFIG.get_isp?1:0)+'&r='+Math.random();
    xhr.open('GET',url);
    xhr.onload=function(){if(state.abort)return;try{var d=JSON.parse(xhr.responseText);state.ip=d.ip||'';state.isp=d.isp||''}catch(e){}updateIPCard();fetchPings()};
    xhr.onerror=function(){if(state.abort)return;updateIPCard();fetchPings()};
    xhr.send();
}
function fetchPings(){if(state.abort)return;setPhase('ping');doPing(0)}
function doPing(index){
    if(state.abort)return;
    if(index>=CONFIG.ping_count){calculatePingResults();return}
    var st=performance.now(),xhr=new XMLHttpRequest();state.currentXhr=xhr;
    xhr.open('GET','test/ping.php?r='+Math.random());
    xhr.onload=function(){if(state.abort)return;state.pings.push(performance.now()-st);doPing(index+1)};
    xhr.onerror=function(){if(state.abort)return;state.pings.push(-1);doPing(index+1)};
    xhr.send();
}
function calculatePingResults(){
    if(state.abort)return;
    var vp=state.pings.filter(function(p){return p>0});
    if(vp.length===0){state.ping=0;state.jitter=0}
    else{
        vp.sort(function(a,b){return a-b});
        var t=vp.length>4?vp.slice(1,-1):vp;
        state.ping=t.reduce(function(a,b){return a+b},0)/t.length;
        var ds=[];for(var i=1;i<t.length;i++)ds.push(Math.abs(t[i]-t[i-1]));
        state.jitter=ds.length>0?ds.reduce(function(a,b){return a+b},0)/ds.length:0;
    }
    updateMetrics();runDownloadTest();
}
function runDownloadTest(){
    if(state.abort)return;setPhase('download');
    var st=performance.now(),xhr=new XMLHttpRequest();state.currentXhr=xhr;
    xhr.open('GET','test/download.php?size='+CONFIG.download_size+'&r='+Math.random());
    xhr.responseType='blob';
    xhr.onprogress=function(e){if(state.abort)return;if(e.lengthComputable&&e.loaded>0){state.dlProgress=e.loaded/e.total;var el=(performance.now()-st)/1000;if(el>0.1)state.dlSpeed=(e.loaded*8)/el/1000/1000;updateMetrics()}};
    xhr.onload=function(){if(state.abort)return;if(xhr.status===200){var et=performance.now(),tm=et-st,b=xhr.response.size;state.dlProgress=1;if(tm>0)state.dlSpeed=(b*8)/(tm/1000)/1000/1000;updateMetrics();runUploadTest()}else{state.dlSpeed=0;updateMetrics();runUploadTest()}};
    xhr.onerror=function(){if(state.abort)return;state.dlSpeed=0;updateMetrics();runUploadTest()};
    xhr.send();
}
function generateRandomData(size){
    var buf=new ArrayBuffer(size),view=new Uint8Array(buf),seedSize=Math.min(65536,size);
    for(var i=0;i<seedSize;i++)view[i]=Math.floor(Math.random()*256);
    for(var i=seedSize;i<size;i++)view[i]=view[i-seedSize];
    return buf;
}
function runUploadTest(){
    if(state.abort)return;setPhase('upload');
    var data=generateRandomData(CONFIG.upload_size),st=performance.now(),xhr=new XMLHttpRequest();
    state.currentXhr=xhr;
    xhr.open('POST','test/upload.php?r='+Math.random());
    xhr.setRequestHeader('Content-Type','application/octet-stream');
    xhr.upload.onprogress=function(e){if(state.abort)return;if(e.lengthComputable&&e.loaded>0){state.ulProgress=e.loaded/e.total;var el=(performance.now()-st)/1000;if(el>0.1)state.ulSpeed=(e.loaded*8)/el/1000/1000;updateMetrics()}};
    xhr.onload=function(){if(state.abort)return;if(xhr.status===200){var et=performance.now(),tm=et-st,b=data.byteLength;state.ulProgress=1;if(tm>0)state.ulSpeed=(b*8)/(tm/1000)/1000/1000;updateMetrics();finishTest()}else{state.ulSpeed=0;updateMetrics();finishTest()}};
    xhr.onerror=function(){if(state.abort)return;state.ulSpeed=0;updateMetrics();finishTest()};
    xhr.send(data);
}
function finishTest(){
    if(state.abort)return;
    setPhase('done');
    document.getElementById('startStopBtn').classList.remove('running');
    document.getElementById('startBtnText').textContent='开始测速';
    // 发送结果到日志系统
    sendLog();
}
function sendLog(){
    var endTime=performance.now();
    var logData={
        client_ip: state.ip,
        isp: state.isp,
        ping: state.ping,
        jitter: state.jitter,
        download_speed: state.dlSpeed,
        upload_speed: state.ulSpeed,
        test_start: new Date(state.startTime).toISOString(),
        test_end: new Date().toISOString(),
        duration_ms: Math.round(endTime - state.startTime),
        user_agent: navigator.userAgent,
        download_size: CONFIG.download_size,
        upload_size: CONFIG.upload_size,
        ping_count: CONFIG.ping_count,
        pings: state.pings
    };
    try {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'test/log.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify(logData));
    } catch(e) {
        console.warn('Log send failed:', e);
    }
}
var darkMode=true;
function toggleTheme(){darkMode=!darkMode;document.body.classList.toggle('light-mode',!darkMode);document.getElementById('themeToggle').textContent=darkMode?'🌙':'☀️';localStorage.setItem('speedtest-theme',darkMode?'dark':'light')}
(function(){var s=localStorage.getItem('speedtest-theme');if(s==='light'){darkMode=false;document.body.classList.add('light-mode')}})();
function togglePrivacy(show){
    var m=document.getElementById('privacyPolicy'),o=document.getElementById('privacyOverlay');
    if(show===undefined)show=m.style.display!=='block';
    if(show){m.style.display='block';o.style.display='block';document.body.style.overflow='hidden';requestAnimationFrame(function(){m.classList.add('show');o.classList.add('show')})}
    else{m.classList.remove('show');o.classList.remove('show');document.body.style.overflow='';setTimeout(function(){m.style.display='none';o.style.display='none'},300)}
}
setTimeout(function(){initUI()},150);
</script>
</body>
</html>
