<script setup lang="ts">
/**
 * ============================================================
 * 鹈鹕骑自行车 SVG 插画组件
 * ============================================================
 *
 * 纯 SVG 绘制鹈鹕骑自行车的动态插画，所有动画通过 CSS animation 实现。
 * 通过 paused prop 控制动画播放/暂停状态。
 *
 * SVG 结构：
 *   background → sky / sun / clouds / ground / road
 *   bicycle   → wheels / frame / seat / handlebars / pedals / chain
 *   pelican   → body / neck / head / beak / eye / wings / tail / legs
 *
 * 动画说明：
 *   - 车轮旋转：CSS keyframes rotate
 *   - 脚踏板圆周运动：CSS keyframes pedal-orbit（与轮子同步）
 *   - 腿部交替蹬踏：CSS keyframes pedal-leg-left / pedal-leg-right
 *   - 身体轻微起伏：CSS keyframes body-bob
 *   - 翅膀轻微摆动：CSS keyframes wing-flap
 *   - 头部/脖子轻微运动：CSS keyframes head-nod
 *   - 云朵缓慢移动：CSS keyframes cloud-drift
 *   - 背景整体前进感：CSS keyframes ground-scroll
 */
defineProps<{
  paused?: boolean
}>()
</script>

<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 800 500"
    class="pelican-bike-svg"
    :class="{ paused }"
  >
    <!-- ==================== 定义区域（渐变、滤镜、裁剪） ==================== -->
    <defs>
      <!-- 天空渐变 -->
      <linearGradient id="skyGradient" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#87CEEB" />
        <stop offset="70%" stop-color="#B0E0F0" />
        <stop offset="100%" stop-color="#E0F4FF" />
      </linearGradient>
      <!-- 地面渐变 -->
      <linearGradient id="groundGradient" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#7CB342" />
        <stop offset="100%" stop-color="#558B2F" />
      </linearGradient>
      <!-- 道路渐变 -->
      <linearGradient id="roadGradient" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#616161" />
        <stop offset="100%" stop-color="#424242" />
      </linearGradient>
      <!-- 太阳光晕 -->
      <radialGradient id="sunGlow" cx="50%" cy="50%" r="50%">
        <stop offset="0%" stop-color="#FFF9C4" stop-opacity="1" />
        <stop offset="60%" stop-color="#FFF176" stop-opacity="0.6" />
        <stop offset="100%" stop-color="#FFF176" stop-opacity="0" />
      </radialGradient>
      <!-- 鹈鹕身体渐变 -->
      <linearGradient id="pelicanBody" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#F5F5F5" />
        <stop offset="100%" stop-color="#E0E0E0" />
      </linearGradient>
      <!-- 鹈鹕头部渐变 -->
      <linearGradient id="pelicanHead" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#FAFAFA" />
        <stop offset="100%" stop-color="#E8E8E8" />
      </linearGradient>
      <!-- 嘴巴渐变 -->
      <linearGradient id="beakGradient" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#FF8F00" />
        <stop offset="100%" stop-color="#EF6C00" />
      </linearGradient>
      <!-- 喉囊渐变 -->
      <linearGradient id="pouchGradient" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#FFB74D" />
        <stop offset="100%" stop-color="#FF9800" />
      </linearGradient>
      <!-- 车架渐变 -->
      <linearGradient id="frameGradient" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#D32F2F" />
        <stop offset="100%" stop-color="#B71C1C" />
      </linearGradient>
      <!-- 车轮辐条滤镜（发光） -->
      <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
        <feGaussianBlur stdDeviation="1.5" result="blur" />
        <feMerge>
          <feMergeNode in="blur" />
          <feMergeNode in="SourceGraphic" />
        </feMerge>
      </filter>
      <!-- 树冠阴影 -->
      <filter id="treeShadow" x="-10%" y="-10%" width="120%" height="120%">
        <feDropShadow dx="2" dy="2" stdDeviation="2" flood-opacity="0.15" />
      </filter>
    </defs>

    <!-- ==================== 背景层 ==================== -->
    <g id="background">
      <!-- 天空 -->
      <rect x="0" y="0" width="800" height="500" fill="url(#skyGradient)" />

      <!-- 太阳 -->
      <g id="sun">
        <circle cx="680" cy="70" r="40" fill="#FDD835" opacity="0.9" />
        <circle cx="680" cy="70" r="60" fill="url(#sunGlow)" />
      </g>

      <!-- 云朵组（三个云朵，不同大小和速度） -->
      <g id="clouds">
        <!-- 云朵 1 -->
        <g class="cloud cloud-1">
          <ellipse cx="0" cy="70" rx="45" ry="18" fill="white" opacity="0.9" />
          <ellipse cx="-20" cy="65" rx="30" ry="16" fill="white" opacity="0.85" />
          <ellipse cx="20" cy="63" rx="35" ry="15" fill="white" opacity="0.88" />
          <ellipse cx="0" cy="60" rx="28" ry="14" fill="white" opacity="0.92" />
        </g>
        <!-- 云朵 2 -->
        <g class="cloud cloud-2">
          <ellipse cx="0" cy="110" rx="55" ry="20" fill="white" opacity="0.85" />
          <ellipse cx="-30" cy="105" rx="38" ry="17" fill="white" opacity="0.8" />
          <ellipse cx="25" cy="102" rx="42" ry="16" fill="white" opacity="0.82" />
          <ellipse cx="-5" cy="98" rx="32" ry="14" fill="white" opacity="0.87" />
        </g>
        <!-- 云朵 3（远处，更小更慢） -->
        <g class="cloud cloud-3">
          <ellipse cx="0" cy="50" rx="35" ry="14" fill="white" opacity="0.7" />
          <ellipse cx="-15" cy="46" rx="22" ry="11" fill="white" opacity="0.65" />
          <ellipse cx="18" cy="44" rx="25" ry="12" fill="white" opacity="0.68" />
        </g>
      </g>

      <!-- 远处的树 -->
      <g id="trees" filter="url(#treeShadow)">
        <!-- 树 1 -->
        <g class="tree tree-1">
          <rect x="-4" y="0" width="8" height="30" rx="3" fill="#8D6E63" />
          <ellipse cx="0" cy="-8" rx="22" ry="20" fill="#66BB6A" />
          <ellipse cx="-8" cy="-5" rx="15" ry="14" fill="#81C784" opacity="0.9" />
          <ellipse cx="10" cy="-3" rx="14" ry="13" fill="#4CAF50" opacity="0.85" />
        </g>
        <!-- 树 2 -->
        <g class="tree tree-2">
          <rect x="-3" y="0" width="6" height="25" rx="2" fill="#795548" />
          <ellipse cx="0" cy="-6" rx="18" ry="17" fill="#43A047" />
          <ellipse cx="-6" cy="-4" rx="12" ry="11" fill="#66BB6A" opacity="0.9" />
        </g>
        <!-- 树 3 -->
        <g class="tree tree-3">
          <rect x="-5" y="0" width="10" height="35" rx="4" fill="#6D4C41" />
          <ellipse cx="0" cy="-10" rx="25" ry="22" fill="#388E3C" />
          <ellipse cx="-10" cy="-6" rx="18" ry="16" fill="#4CAF50" opacity="0.9" />
          <ellipse cx="12" cy="-4" rx="16" ry="15" fill="#2E7D32" opacity="0.85" />
        </g>
      </g>

      <!-- 地面（绿色草地） -->
      <rect id="ground" x="0" y="390" width="800" height="110" fill="url(#groundGradient)" />

      <!-- 道路 -->
      <rect id="road" x="0" y="385" width="800" height="50" rx="2" fill="url(#roadGradient)" />
      <!-- 道路中线（虚线） -->
      <g class="road-lines">
        <line x1="20" y1="410" x2="70" y2="410" stroke="#FDD835" stroke-width="2" stroke-dasharray="0" />
        <line x1="100" y1="410" x2="150" y2="410" stroke="#FDD835" stroke-width="2" />
        <line x1="180" y1="410" x2="230" y2="410" stroke="#FDD835" stroke-width="2" />
        <line x1="260" y1="410" x2="310" y2="410" stroke="#FDD835" stroke-width="2" />
        <line x1="340" y1="410" x2="390" y2="410" stroke="#FDD835" stroke-width="2" />
        <line x1="420" y1="410" x2="470" y2="410" stroke="#FDD835" stroke-width="2" />
        <line x1="500" y1="410" x2="550" y2="410" stroke="#FDD835" stroke-width="2" />
        <line x1="580" y1="410" x2="630" y2="410" stroke="#FDD835" stroke-width="2" />
        <line x1="660" y1="410" x2="710" y2="410" stroke="#FDD835" stroke-width="2" />
        <line x1="740" y1="410" x2="790" y2="410" stroke="#FDD835" stroke-width="2" />
      </g>
      <!-- 道路边缘线 -->
      <line x1="0" y1="386" x2="800" y2="386" stroke="#9E9E9E" stroke-width="1" />
      <line x1="0" y1="434" x2="800" y2="434" stroke="#9E9E9E" stroke-width="1" />

      <!-- 草地上的小花 -->
      <g class="flowers">
        <circle cx="80" cy="395" r="3" fill="#F48FB1" />
        <circle cx="82" cy="393" r="2.5" fill="#F8BBD0" />
        <circle cx="200" cy="393" r="2.5" fill="#CE93D8" />
        <circle cx="202" cy="391" r="2" fill="#E1BEE7" />
        <circle cx="620" cy="394" r="3" fill="#FFAB91" />
        <circle cx="622" cy="392" r="2.5" fill="#FFCCBC" />
        <circle cx="730" cy="395" r="2.5" fill="#80CBC4" />
        <circle cx="732" cy="393" r="2" fill="#B2DFDB" />
      </g>
    </g>

    <!-- ==================== 自行车 ==================== -->
    <g id="bicycle">
      <!-- ---- 后轮 ---- -->
      <g id="rear-wheel" class="wheel-rotate">
        <circle cx="340" cy="365" r="50" fill="none" stroke="#333" stroke-width="3" />
        <circle cx="340" cy="365" r="47" fill="none" stroke="#555" stroke-width="1" />
        <!-- 轮胎 -->
        <circle cx="340" cy="365" r="50" fill="none" stroke="#212121" stroke-width="5" />
        <!-- 轮毂 -->
        <circle cx="340" cy="365" r="6" fill="#757575" stroke="#424242" stroke-width="1.5" />
        <!-- 辐条 -->
        <line x1="340" y1="318" x2="340" y2="412" stroke="#9E9E9E" stroke-width="1" />
        <line x1="293" y1="365" x2="387" y2="365" stroke="#9E9E9E" stroke-width="1" />
        <line x1="307" y1="332" x2="373" y2="398" stroke="#9E9E9E" stroke-width="1" />
        <line x1="307" y1="398" x2="373" y2="332" stroke="#9E9E9E" stroke-width="1" />
        <line x1="320" y1="320" x2="360" y2="410" stroke="#9E9E9E" stroke-width="0.8" />
        <line x1="360" y1="320" x2="320" y2="410" stroke="#9E9E9E" stroke-width="0.8" />
        <line x1="296" y1="345" x2="384" y2="385" stroke="#9E9E9E" stroke-width="0.8" />
        <line x1="296" y1="385" x2="384" y2="345" stroke="#9E9E9E" stroke-width="0.8" />
      </g>

      <!-- ---- 前轮 ---- -->
      <g id="front-wheel" class="wheel-rotate">
        <circle cx="510" cy="365" r="50" fill="none" stroke="#212121" stroke-width="5" />
        <circle cx="510" cy="365" r="47" fill="none" stroke="#555" stroke-width="1" />
        <circle cx="510" cy="365" r="6" fill="#757575" stroke="#424242" stroke-width="1.5" />
        <!-- 辐条 -->
        <line x1="510" y1="318" x2="510" y2="412" stroke="#9E9E9E" stroke-width="1" />
        <line x1="463" y1="365" x2="557" y2="365" stroke="#9E9E9E" stroke-width="1" />
        <line x1="477" y1="332" x2="543" y2="398" stroke="#9E9E9E" stroke-width="1" />
        <line x1="477" y1="398" x2="543" y2="332" stroke="#9E9E9E" stroke-width="1" />
        <line x1="490" y1="320" x2="530" y2="410" stroke="#9E9E9E" stroke-width="0.8" />
        <line x1="530" y1="320" x2="490" y2="410" stroke="#9E9E9E" stroke-width="0.8" />
        <line x1="466" y1="345" x2="554" y2="385" stroke="#9E9E9E" stroke-width="0.8" />
        <line x1="466" y1="385" x2="554" y2="345" stroke="#9E9E9E" stroke-width="0.8" />
      </g>

      <!-- ---- 车架 ---- -->
      <g id="frame">
        <!-- 下管（底部中轴→头管底部） -->
        <line x1="395" y1="355" x2="500" y2="310" stroke="url(#frameGradient)" stroke-width="5" stroke-linecap="round" />
        <!-- 上管（座管顶部→头管顶部） -->
        <line x1="388" y1="290" x2="500" y2="295" stroke="url(#frameGradient)" stroke-width="4.5" stroke-linecap="round" />
        <!-- 座管（底部中轴→座管顶部） -->
        <line x1="395" y1="355" x2="388" y2="285" stroke="url(#frameGradient)" stroke-width="5" stroke-linecap="round" />
        <!-- 头管 -->
        <line x1="500" y1="290" x2="505" y2="320" stroke="url(#frameGradient)" stroke-width="5" stroke-linecap="round" />
        <!-- 后下叉（底部中轴→后轴） -->
        <line x1="395" y1="355" x2="340" y2="365" stroke="url(#frameGradient)" stroke-width="3.5" stroke-linecap="round" />
        <!-- 后上叉（座管顶部→后轴） -->
        <line x1="388" y1="290" x2="340" y2="365" stroke="url(#frameGradient)" stroke-width="3" stroke-linecap="round" />
        <!-- 前叉（头管底部→前轴） -->
        <line x1="505" y1="320" x2="510" y2="365" stroke="#424242" stroke-width="4" stroke-linecap="round" />

        <!-- 立管（头管顶部→车把） -->
        <line x1="500" y1="290" x2="505" y2="268" stroke="#424242" stroke-width="4" stroke-linecap="round" />
      </g>

      <!-- ---- 链条（底部中轴→后轴） ---- -->
      <g id="chain" opacity="0.4">
        <ellipse cx="368" cy="360" rx="28" ry="10" fill="none" stroke="#616161" stroke-width="1.5" />
      </g>

      <!-- ---- 脚踏板组 ---- -->
      <g id="pedal-group" class="pedal-orbit">
        <!-- 左脚踏（当踏板在下方） -->
        <g class="pedal-left">
          <rect x="-12" y="-3" width="24" height="6" rx="2" fill="#424242" />
          <circle cx="0" cy="0" r="2" fill="#757575" />
        </g>
        <!-- 右脚踏（当踏板在上方） -->
        <g class="pedal-right">
          <rect x="-12" y="-3" width="24" height="6" rx="2" fill="#424242" />
          <circle cx="0" cy="0" r="2" fill="#757575" />
        </g>
      </g>

      <!-- ---- 座椅 ---- -->
      <g id="seat">
        <ellipse cx="385" cy="283" rx="22" ry="6" fill="#3E2723" />
        <ellipse cx="385" cy="282" rx="20" ry="5" fill="#5D4037" />
        <ellipse cx="382" cy="281" rx="16" ry="3.5" fill="#6D4C41" opacity="0.5" />
      </g>

      <!-- ---- 车把 ---- -->
      <g id="handlebar">
        <!-- 横把 -->
        <line x1="488" y1="262" x2="522" y2="262" stroke="#424242" stroke-width="4" stroke-linecap="round" />
        <!-- 把套 -->
        <rect x="483" y="258" width="10" height="8" rx="3" fill="#795548" />
        <rect x="517" y="258" width="10" height="8" rx="3" fill="#795548" />
        <!-- 弯把（向下弯曲到头管） -->
        <path d="M488,262 Q492,275 496,288" fill="none" stroke="#424242" stroke-width="3" stroke-linecap="round" />
        <path d="M522,262 Q518,275 514,288" fill="none" stroke="#424242" stroke-width="3" stroke-linecap="round" />
      </g>
    </g>

    <!-- ==================== 鹈鹕 ==================== -->
    <g id="pelican" class="body-bob">
      <!-- ---- 腿部（两只脚踩踏板，左右交替） ---- -->
      <g id="pelican-legs">
        <!-- 左腿（当前在下方/后方位置） -->
        <g class="leg leg-left">
          <path d="M390,305 Q385,330 375,355 Q370,362 368,368" fill="none" stroke="#FF8F00" stroke-width="3.5" stroke-linecap="round" />
          <!-- 左脚（蹼足） -->
          <path d="M368,368 L358,372 L365,365 L360,370 L368,368" fill="#FF8F00" />
        </g>
        <!-- 右腿（当前在上方/前方位置） -->
        <g class="leg leg-right">
          <path d="M400,305 Q408,325 418,348 Q422,356 425,362" fill="none" stroke="#FF8F00" stroke-width="3.5" stroke-linecap="round" />
          <!-- 右脚（蹼足） -->
          <path d="M425,362 L435,366 L428,359 L433,364 L425,362" fill="#FF8F00" />
        </g>
      </g>

      <!-- ---- 身体（蛋形，坐在座椅上） ---- -->
      <g id="pelican-body">
        <!-- 身体主体 -->
        <ellipse cx="395" cy="275" rx="40" ry="30" fill="url(#pelicanBody)" stroke="#BDBDBD" stroke-width="1" />
        <!-- 腹部羽毛纹理 -->
        <ellipse cx="395" cy="280" rx="32" ry="22" fill="#FAFAFA" opacity="0.5" />
        <!-- 胸部高光 -->
        <ellipse cx="385" cy="268" rx="18" ry="14" fill="white" opacity="0.4" />
        <!-- 背部翅膀的覆盖 -->
        <ellipse cx="400" cy="268" rx="30" ry="20" fill="#EEEEEE" opacity="0.6" />
      </g>

      <!-- ---- 尾巴 ---- -->
      <g id="pelican-tail">
        <path d="M355,265 Q335,250 330,240 Q332,245 340,252 Q335,238 328,228 Q333,238 342,248 Q340,235 336,225 Q342,237 348,248" fill="#BDBDBD" stroke="#9E9E9E" stroke-width="0.5" />
      </g>

      <!-- ---- 翅膀（左侧，骑车时的自然姿态） ---- -->
      <g id="pelican-wing" class="wing-flap">
        <!-- 翅膀主体 -->
        <path d="M400,265 Q430,250 450,245 Q455,243 450,250 Q440,262 430,268 Q440,255 445,248 Q448,253 440,263 Q435,270 425,274 Q430,262 435,255 Q438,260 430,270 Q420,278 410,280 Q405,278 400,272 Z" fill="#E0E0E0" stroke="#BDBDBD" stroke-width="0.8" />
        <!-- 翅膀羽毛纹理 -->
        <path d="M410,270 Q425,258 438,252" fill="none" stroke="#BDBDBD" stroke-width="0.6" opacity="0.7" />
        <path d="M408,275 Q420,265 432,258" fill="none" stroke="#BDBDBD" stroke-width="0.6" opacity="0.6" />
      </g>

      <!-- ---- 颈部（S 形曲线，从身体到头部） ---- -->
      <g id="pelican-neck" class="head-nod">
        <!-- 颈部主体（粗壮的S形） -->
        <path d="M390,255 Q385,230 380,210 Q375,190 380,175 Q385,160 392,148" fill="none" stroke="url(#pelicanBody)" stroke-width="18" stroke-linecap="round" />
        <!-- 颈部轮廓 -->
        <path d="M390,255 Q385,230 380,210 Q375,190 380,175 Q385,160 392,148" fill="none" stroke="#BDBDBD" stroke-width="0.8" stroke-linecap="round" />
        <!-- 颈部白色腹侧 -->
        <path d="M388,252 Q383,228 378,208 Q374,190 378,176 Q383,162 390,150" fill="none" stroke="white" stroke-width="6" stroke-linecap="round" opacity="0.5" />

        <!-- ---- 头部 ---- -->
        <g id="pelican-head">
          <!-- 头部主体 -->
          <ellipse cx="395" cy="140" rx="18" ry="16" fill="url(#pelicanHead)" stroke="#BDBDBD" stroke-width="1" />
          <!-- 头顶羽冠 -->
          <path d="M388,127 Q392,120 396,125 Q400,120 403,127" fill="#E0E0E0" stroke="#BDBDBD" stroke-width="0.5" />

          <!-- ---- 眼睛 ---- -->
          <g id="pelican-eye">
            <circle cx="404" cy="137" r="5" fill="white" />
            <circle cx="405" cy="137" r="3" fill="#212121" />
            <circle cx="406" cy="136" r="1.2" fill="white" />
          </g>

          <!-- ---- 嘴巴（鹈鹕标志性的大嘴 + 喉囊） ---- -->
          <g id="pelican-beak">
            <!-- 上喙（长而宽，前端带钩） -->
            <path d="M410,140 Q430,138 448,140 Q452,141 450,143 Q440,145 425,144 Q415,143 410,142 Z" fill="url(#beakGradient)" stroke="#E65100" stroke-width="0.8" />
            <!-- 上喙的鼻孔 -->
            <ellipse cx="430" cy="140" rx="2" ry="1" fill="#E65100" opacity="0.5" />
            <!-- 喙尖的钩 -->
            <path d="M448,140 Q452,139 451,142" fill="none" stroke="#E65100" stroke-width="1" />

            <!-- 下喙 + 喉囊（鹈鹕标志性特征） -->
            <path d="M412,143 Q425,152 440,150 Q448,148 450,143 Q440,146 425,146 Z" fill="url(#pouchGradient)" stroke="#E65100" stroke-width="0.6" opacity="0.9" />
            <!-- 喉囊纹理 -->
            <path d="M418,145 Q425,150 435,148" fill="none" stroke="#F57C00" stroke-width="0.5" opacity="0.6" />
            <path d="M420,147 Q428,152 438,149" fill="none" stroke="#F57C00" stroke-width="0.4" opacity="0.5" />

            <!-- 上喙中线 -->
            <line x1="412" y1="141.5" x2="446" y2="141" stroke="#FF6F00" stroke-width="0.5" opacity="0.4" />
          </g>

          <!-- 头部与颈部的衔接（柔化过渡） -->
          <ellipse cx="395" cy="148" rx="14" ry="8" fill="#F5F5F5" opacity="0.4" />
        </g>
      </g>

      <!-- ---- 头顶羽冠（在最上层） ---- -->
      <g id="pelican-crown" class="head-nod">
        <path d="M386,126 Q390,116 394,122 Q396,114 400,122 Q402,116 406,124" fill="none" stroke="#9E9E9E" stroke-width="1.2" stroke-linecap="round" />
      </g>
    </g>

    <!-- ==================== 前景小草装饰 ==================== -->
    <g class="foreground-grass">
      <path d="M50,392 Q55,380 60,392" fill="none" stroke="#4CAF50" stroke-width="1.5" />
      <path d="M150,390 Q157,376 162,390" fill="none" stroke="#388E3C" stroke-width="1.2" />
      <path d="M650,391 Q656,378 662,391" fill="none" stroke="#43A047" stroke-width="1.3" />
      <path d="M700,393 Q706,382 712,393" fill="none" stroke="#2E7D32" stroke-width="1.1" />
      <path d="M580,392 Q584,384 588,392" fill="none" stroke="#4CAF50" stroke-width="1" />
    </g>
  </svg>
</template>

<style scoped>
/* ============================================================
 * SVG 插画样式 + 动画定义
 * ============================================================
 *
 * 所有动画通过 CSS @keyframes 实现，不依赖 JS。
 * 通过 .paused 类暂停所有动画（animation-play-state: paused）。
 */

.pelican-bike-svg {
  width: 100%;
  height: auto;
  display: block;
}

/* ============================================================
 * 动画暂停控制
 * ============================================================ */
.pelican-bike-svg.paused * {
  animation-play-state: paused !important;
}

/* ============================================================
 * 车轮旋转动画
 * ============================================================
 * 两个轮子旋转中心各自在 (cx, cy)，通过 transform-origin 定位。
 * rear-wheel: 中心 (340, 365)
 * front-wheel: 中心 (510, 365)
 * 旋转速度一致，模拟自行车前进。
 */
.wheel-rotate {
  animation: rotateWheel 1.2s linear infinite;
  transform-origin: center;
  transform-box: fill-box;
}

#rear-wheel.wheel-rotate {
  transform-origin: 340px 365px;
}

#front-wheel.wheel-rotate {
  transform-origin: 510px 365px;
}

@keyframes rotateWheel {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* ============================================================
 * 脚踏板圆周运动
 * ============================================================
 * 脚踏板围绕底部中轴 (395, 355) 做圆周运动。
 * 与车轮旋转同步（周期一致 = 1.2s）。
 * 左踏板和右踏板相差 180°（半个周期）。
 */
.pedal-orbit {
  animation: rotatePedal 1.2s linear infinite;
  transform-origin: 395px 355px;
}

.pedal-left {
  transform-origin: 395px 355px;
  animation: pedalLeft 1.2s linear infinite;
}

.pedal-right {
  transform-origin: 395px 355px;
  animation: pedalRight 1.2s linear infinite;
}

@keyframes rotatePedal {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes pedalLeft {
  0%   { transform: rotate(0deg)   translate(0, 0); }
  25%  { transform: rotate(90deg)  translate(0, 12px); }
  50%  { transform: rotate(180deg) translate(0, 0); }
  75%  { transform: rotate(270deg) translate(0, -12px); }
  100% { transform: rotate(360deg) translate(0, 0); }
}

@keyframes pedalRight {
  0%   { transform: rotate(0deg)   translate(0, 0); }
  25%  { transform: rotate(90deg)  translate(0, -12px); }
  50%  { transform: rotate(180deg) translate(0, 0); }
  75%  { transform: rotate(270deg) translate(0, 12px); }
  100% { transform: rotate(360deg) translate(0, 0); }
}

/* ============================================================
 * 腿部蹬踏动画
 * ============================================================
 * 左右腿交替弯曲伸展，模拟踩踏板动作。
 * 腿部的 endpoint 跟随踏板运动。
 */
.leg-left {
  animation: legPedalLeft 1.2s ease-in-out infinite;
  transform-origin: 390px 305px;
}

.leg-right {
  animation: legPedalRight 1.2s ease-in-out infinite;
  transform-origin: 400px 305px;
}

@keyframes legPedalLeft {
  0%   { transform: rotate(0deg); }
  25%  { transform: rotate(8deg); }
  50%  { transform: rotate(0deg); }
  75%  { transform: rotate(-8deg); }
  100% { transform: rotate(0deg); }
}

@keyframes legPedalRight {
  0%   { transform: rotate(0deg); }
  25%  { transform: rotate(-8deg); }
  50%  { transform: rotate(0deg); }
  75%  { transform: rotate(8deg); }
  100% { transform: rotate(0deg); }
}

/* ============================================================
 * 身体轻微上下起伏
 * ============================================================
 * 鹈鹕坐在座椅上，踩踏时身体有节奏地轻微上下。
 * 幅度很小（±3px），模拟骑车的颠簸感。
 */
.body-bob {
  animation: bodyBob 1.2s ease-in-out infinite;
  transform-origin: 395px 275px;
}

@keyframes bodyBob {
  0%   { transform: translateY(0); }
  25%  { transform: translateY(-3px); }
  50%  { transform: translateY(0); }
  75%  { transform: translateY(-2px); }
  100% { transform: translateY(0); }
}

/* ============================================================
 * 翅膀轻微摆动
 * ============================================================
 * 翅膀在骑车时自然摆动，幅度小，不是飞行的剧烈扇动。
 * 周期比踏板慢一些（1.8s），更自然。
 */
.wing-flap {
  animation: wingFlap 1.8s ease-in-out infinite;
  transform-origin: 400px 265px;
}

@keyframes wingFlap {
  0%   { transform: rotate(0deg); }
  25%  { transform: rotate(3deg); }
  50%  { transform: rotate(0deg); }
  75%  { transform: rotate(-2deg); }
  100% { transform: rotate(0deg); }
}

/* ============================================================
 * 头部/脖子轻微点头
 * ============================================================
 * 鹈鹕骑车时头部有轻微的前倾后仰，模拟平衡动作。
 * 周期与身体起伏一致。
 */
.head-nod {
  animation: headNod 2s ease-in-out infinite;
  transform-origin: 390px 255px;
}

@keyframes headNod {
  0%   { transform: rotate(0deg) translateX(0); }
  25%  { transform: rotate(1.5deg) translateX(1px); }
  50%  { transform: rotate(0deg) translateX(0); }
  75%  { transform: rotate(-1deg) translateX(-0.5px); }
  100% { transform: rotate(0deg) translateX(0); }
}

/* ============================================================
 * 云朵移动动画
 * ============================================================
 * 三个云朵以不同速度和起始位置从右向左移动，
 * 模拟自行车前进时天空的相对运动。
 */
.cloud {
  animation: cloudDrift linear infinite;
}

.cloud-1 {
  transform: translateX(850px);
  animation-duration: 18s;
}

.cloud-2 {
  transform: translateX(880px);
  animation-duration: 24s;
}

.cloud-3 {
  transform: translateX(860px);
  animation-duration: 30s;
}

@keyframes cloudDrift {
  from { transform: translateX(850px); }
  to   { transform: translateX(-200px); }
}

/* ============================================================
 * 树木移动动画
 * ============================================================
 * 远处的树木以较慢速度向左移动，营造前进感。
 */
.tree {
  animation: treeDrift linear infinite;
}

.tree-1 {
  transform: translateX(860px);
  animation-duration: 14s;
}

.tree-2 {
  transform: translateX(880px);
  animation-duration: 11s;
}

.tree-3 {
  transform: translateX(870px);
  animation-duration: 16s;
}

@keyframes treeDrift {
  from { transform: translateX(880px); }
  to   { transform: translateX(-120px); }
}

/* ============================================================
 * 道路中线移动（增强前进感）
 * ============================================================ */
.road-lines {
  animation: roadScroll 1.2s linear infinite;
}

@keyframes roadScroll {
  from { transform: translateX(0); }
  to   { transform: translateX(-80px); }
}

/* ============================================================
 * 前景小草（轻微摇曳）
 * ============================================================ */
.foreground-grass {
  animation: grassSway 3s ease-in-out infinite;
}

.foreground-grass > *:nth-child(2) {
  animation: grassSway 2.5s ease-in-out 0.3s infinite;
}

.foreground-grass > *:nth-child(3) {
  animation: grassSway 3.5s ease-in-out 0.6s infinite;
}

.foreground-grass > *:nth-child(4) {
  animation: grassSway 2.8s ease-in-out 0.2s infinite;
}

.foreground-grass > *:nth-child(5) {
  animation: grassSway 3.2s ease-in-out 0.5s infinite;
}

@keyframes grassSway {
  0%   { transform: rotate(0deg); }
  25%  { transform: rotate(3deg); }
  50%  { transform: rotate(0deg); }
  75%  { transform: rotate(-2deg); }
  100% { transform: rotate(0deg); }
}
</style>
