<script setup lang="ts">
/**
 * 鹈鹕骑自行车 —— 纯 SVG 插画,所有动画走 CSS keyframes。
 *
 * 关键几何常量(与 CSS 里的 transform-origin 必须一致,改一处要一起改):
 *   后轮心 (330,348)  前轮心 (610,348)  轮半径 60
 *   中轴/曲柄心 (458,344)  曲柄长 26
 *   髋关节 (420,288)  大腿长 54  小腿长 50
 *
 * 腿部的角度不是随手画的:曲柄每 45° 一帧,用两连杆 IK
 * (大腿/小腿各一杆)反解出髋角与膝角,保证脚始终钉在踏板上不脱位。
 * 远侧腿用 animation-delay: -0.55s 与近侧腿错开半个周期,形成左右交替踩踏。
 */
withDefaults(defineProps<{ paused?: boolean }>(), { paused: false })
</script>

<template>
  <svg
    class="scene"
    :class="{ paused }"
    viewBox="0 0 960 480"
    role="img"
    aria-label="一只鹈鹕正在骑自行车的动画插画"
  >
    <!-- ============ 渐变与纹理 ============ -->
    <defs>
      <linearGradient id="pb-sky" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="#BCE3F5" />
        <stop offset="0.55" stop-color="#DFF2FB" />
        <stop offset="1" stop-color="#F6FBFD" />
      </linearGradient>
      <linearGradient id="pb-road" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="#9BA9B7" />
        <stop offset="1" stop-color="#8493A3" />
      </linearGradient>
      <linearGradient id="pb-hill" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="#BFE0C6" />
        <stop offset="1" stop-color="#A9D2B2" />
      </linearGradient>
      <linearGradient id="pb-body" x1="0" y1="0" x2="0.6" y2="1">
        <stop offset="0" stop-color="#D8E5F1" />
        <stop offset="1" stop-color="#9FB4CC" />
      </linearGradient>
      <linearGradient id="pb-belly" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="#F4F8FC" />
        <stop offset="1" stop-color="#DCE7F1" />
      </linearGradient>
      <linearGradient id="pb-wing" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#8DA2BD" />
        <stop offset="1" stop-color="#6B8099" />
      </linearGradient>
      <linearGradient id="pb-beak" x1="0" y1="0" x2="1" y2="0.5">
        <stop offset="0" stop-color="#F7BE63" />
        <stop offset="1" stop-color="#E39A2F" />
      </linearGradient>
      <linearGradient id="pb-frame" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="#E26157" />
        <stop offset="1" stop-color="#B93A36" />
      </linearGradient>
      <radialGradient id="pb-sun" cx="0.5" cy="0.5" r="0.5">
        <stop offset="0" stop-color="#FFF3C4" stop-opacity="0.95" />
        <stop offset="0.55" stop-color="#FFE9A3" stop-opacity="0.5" />
        <stop offset="1" stop-color="#FFE9A3" stop-opacity="0" />
      </radialGradient>
    </defs>

    <!-- ============ 背景 ============ -->
    <g id="background">
      <rect x="0" y="0" width="960" height="360" fill="url(#pb-sky)" />
      <circle cx="820" cy="96" r="120" fill="url(#pb-sun)" />
      <circle cx="820" cy="96" r="34" fill="#FFF6D0" opacity="0.9" />

      <!-- 远山:两条起伏,层次拉开 -->
      <path d="M 0 300 C 120 250 240 292 360 268 C 480 244 600 296 720 272 C 830 252 900 286 960 270 L 960 360 L 0 360 Z" fill="url(#pb-hill)" opacity="0.85" />
      <path d="M 0 332 C 140 300 300 336 440 318 C 580 302 700 340 840 322 C 900 314 940 326 960 322 L 960 360 L 0 360 Z" fill="#C7E3CE" opacity="0.9" />
    </g>

    <!-- ============ 云(外层 g 定位,内层 g 负责动画,避免 transform 互相覆盖) ============ -->
    <g id="clouds" fill="#FFFFFF" opacity="0.9">
      <g transform="translate(0, 66)">
        <g class="cloud c1">
          <circle cx="0" cy="12" r="18" />
          <circle cx="26" cy="0" r="24" />
          <circle cx="56" cy="10" r="17" />
          <ellipse cx="28" cy="20" rx="46" ry="13" />
        </g>
      </g>
      <g transform="translate(0, 128)">
        <g class="cloud c2" transform="scale(0.72)">
          <circle cx="0" cy="12" r="18" />
          <circle cx="26" cy="0" r="24" />
          <circle cx="56" cy="10" r="17" />
          <ellipse cx="28" cy="20" rx="46" ry="13" />
        </g>
      </g>
      <g transform="translate(0, 34)">
        <g class="cloud c3" transform="scale(0.55)">
          <circle cx="0" cy="12" r="18" />
          <circle cx="26" cy="0" r="24" />
          <circle cx="56" cy="10" r="17" />
          <ellipse cx="28" cy="20" rx="46" ry="13" />
        </g>
      </g>
      <g transform="translate(0, 176)">
        <g class="cloud c4" transform="scale(0.85)">
          <circle cx="0" cy="12" r="18" />
          <circle cx="26" cy="0" r="24" />
          <circle cx="56" cy="10" r="17" />
          <ellipse cx="28" cy="20" rx="46" ry="13" />
        </g>
      </g>
    </g>

    <!-- ============ 树(轻微摇摆,不滚动:前进感交给轮子和路面虚线) ============ -->
    <g id="trees">
      <g class="tree t1" transform="translate(120, 352)">
        <rect x="-7" y="0" width="14" height="46" rx="3" fill="#96755A" />
        <circle cx="0" cy="-24" r="34" fill="#8CC48A" />
        <circle cx="-24" cy="-8" r="24" fill="#7CB47A" />
        <circle cx="24" cy="-10" r="26" fill="#98CE93" />
      </g>
      <g class="tree t2" transform="translate(742, 356)">
        <rect x="-6" y="0" width="12" height="38" rx="3" fill="#96755A" />
        <circle cx="0" cy="-20" r="28" fill="#8CC48A" />
        <circle cx="-20" cy="-6" r="20" fill="#7CB47A" />
        <circle cx="20" cy="-8" r="22" fill="#98CE93" />
      </g>
    </g>

    <!-- ============ 路面 ============ -->
    <g id="road">
      <rect x="0" y="360" width="960" height="120" fill="url(#pb-road)" />
      <line x1="0" y1="361" x2="960" y2="361" stroke="#B9C4CF" stroke-width="4" />
      <!-- 一个周期(1.1s)移动的像素 = 轮子滚动一周的轮周 2πr ≈ 377px,与轮子严格同步 -->
      <line
        class="road-dash"
        x1="-80"
        y1="408"
        x2="1040"
        y2="408"
        stroke="#F3F7FA"
        stroke-width="7"
        stroke-linecap="round"
        stroke-dasharray="48 42"
      />
    </g>

    <!-- ============ 车与鹈鹕 ============ -->
    <!-- 整体做极轻微起伏,幅度 2px,1.1s 周期,避免看起来"飘" -->
    <g id="rig" class="rig">
      <!-- 投影:随起伏略微缩放,强化落地感 -->
      <ellipse class="shadow" cx="470" cy="418" rx="212" ry="13" fill="#5C6B7A" opacity="0.22" />

      <!-- 后轮 -->
      <g id="rear-wheel" class="wheel" style="transform-origin: 330px 348px">
        <circle cx="330" cy="348" r="60" fill="none" stroke="#3D4756" stroke-width="11" />
        <circle cx="330" cy="348" r="52" fill="#F7FAFC" opacity="0.55" />
        <g stroke="#6C7888" stroke-width="2.4" stroke-linecap="round">
          <line x1="330" y1="348" x2="330" y2="298" />
          <line x1="330" y1="348" x2="330" y2="398" />
          <line x1="330" y1="348" x2="280" y2="348" />
          <line x1="330" y1="348" x2="380" y2="348" />
          <line x1="330" y1="348" x2="295" y2="313" />
          <line x1="330" y1="348" x2="365" y2="383" />
          <line x1="330" y1="348" x2="365" y2="313" />
          <line x1="330" y1="348" x2="295" y2="383" />
        </g>
        <circle cx="330" cy="348" r="8" fill="#3D4756" />
        <circle cx="330" cy="348" r="3" fill="#B8C2CD" />
      </g>

      <!-- 前轮 -->
      <g id="front-wheel" class="wheel" style="transform-origin: 610px 348px">
        <circle cx="610" cy="348" r="60" fill="none" stroke="#3D4756" stroke-width="11" />
        <circle cx="610" cy="348" r="52" fill="#F7FAFC" opacity="0.55" />
        <g stroke="#6C7888" stroke-width="2.4" stroke-linecap="round">
          <line x1="610" y1="348" x2="610" y2="298" />
          <line x1="610" y1="348" x2="610" y2="398" />
          <line x1="610" y1="348" x2="560" y2="348" />
          <line x1="610" y1="348" x2="660" y2="348" />
          <line x1="610" y1="348" x2="575" y2="313" />
          <line x1="610" y1="348" x2="645" y2="383" />
          <line x1="610" y1="348" x2="645" y2="313" />
          <line x1="610" y1="348" x2="575" y2="383" />
        </g>
        <circle cx="610" cy="348" r="8" fill="#3D4756" />
        <circle cx="610" cy="348" r="3" fill="#B8C2CD" />
      </g>

      <!-- 远侧腿(先画,被车架和近侧腿压住,形成前后层次) -->
      <g id="far-leg" class="leg leg-far">
        <g class="thigh" style="transform-origin: 420px 288px">
          <line x1="420" y1="288" x2="420" y2="342" stroke="#5A6E88" stroke-width="14" stroke-linecap="round" />
          <g class="calf" style="transform-origin: 420px 342px">
            <line x1="420" y1="342" x2="420" y2="392" stroke="#5A6E88" stroke-width="12.5" stroke-linecap="round" />
            <ellipse cx="420" cy="396" rx="14" ry="8" fill="#C98F2A" />
          </g>
        </g>
      </g>

      <!-- 车架 -->
      <g id="frame">
        <!-- 链条:后飞轮 → 曲柄盘,虚线模拟链条节 -->
        <path d="M 330 342 L 458 338 A 26 26 0 0 1 458 350 L 330 354 Z" fill="none" stroke="#4C5765" stroke-width="4" stroke-dasharray="6 4" opacity="0.8" />
        <g fill="none" stroke="url(#pb-frame)" stroke-width="10" stroke-linecap="round">
          <line x1="420" y1="254" x2="458" y2="344" />
          <line x1="420" y1="254" x2="586" y2="254" />
          <line x1="586" y1="254" x2="458" y2="344" />
        </g>
        <g fill="none" stroke="#8E2F2C" stroke-width="8" stroke-linecap="round">
          <line x1="586" y1="254" x2="604" y2="292" />
          <line x1="604" y1="292" x2="610" y2="348" />
          <line x1="330" y1="348" x2="458" y2="344" />
          <line x1="330" y1="348" x2="420" y2="254" />
        </g>
        <!-- 座管与座垫 -->
        <line x1="420" y1="254" x2="416" y2="238" stroke="#4C5765" stroke-width="7" stroke-linecap="round" />
        <path d="M 392 236 Q 416 226 442 236 Q 430 244 416 244 Q 402 244 392 236 Z" fill="#33394A" />
        <!-- 车把 -->
        <line x1="586" y1="254" x2="598" y2="232" stroke="#4C5765" stroke-width="7" stroke-linecap="round" />
        <path d="M 584 236 Q 600 224 622 230" fill="none" stroke="#4C5765" stroke-width="7" stroke-linecap="round" />
        <circle cx="624" cy="230" r="6" fill="#33394A" />
      </g>

      <!-- 曲柄:初始朝上(0°=曲柄臂指向正上方),1.1s 一圈,与轮子同速 -->
      <g id="crank" class="crank" style="transform-origin: 458px 344px">
        <line x1="458" y1="344" x2="458" y2="318" stroke="#3B4453" stroke-width="8" stroke-linecap="round" />
        <line x1="458" y1="344" x2="458" y2="370" stroke="#3B4453" stroke-width="8" stroke-linecap="round" />
        <rect x="440" y="310" width="36" height="9" rx="4.5" fill="#232833" />
        <rect x="440" y="366" width="36" height="9" rx="4.5" fill="#232833" />
      </g>
      <circle cx="458" cy="344" r="9" fill="#3B4453" />
      <circle cx="458" cy="344" r="4" fill="#8892A0" />

      <!-- 近侧腿 -->
      <g id="near-leg" class="leg leg-near">
        <g class="thigh" style="transform-origin: 420px 288px">
          <line x1="420" y1="288" x2="420" y2="342" stroke="#7B90AA" stroke-width="14" stroke-linecap="round" />
          <g class="calf" style="transform-origin: 420px 342px">
            <line x1="420" y1="342" x2="420" y2="392" stroke="#7B90AA" stroke-width="12.5" stroke-linecap="round" />
            <ellipse cx="420" cy="396" rx="15" ry="8.5" fill="#F0A93B" />
          </g>
        </g>
      </g>

      <!-- ============ 鹈鹕 ============ -->
      <g id="pelican" class="pelican">
        <!-- 尾羽:三片向左后探出 -->
        <g id="tail" fill="#7B90AA">
          <path d="M 352 246 L 316 256 L 342 262 Z" />
          <path d="M 348 258 L 308 272 L 342 272 Z" />
          <path d="M 352 268 L 320 284 L 352 278 Z" />
        </g>

        <!-- 远侧翅膀(身体后方,颜色更深) -->
        <g id="wing-back" class="wing-back" style="transform-origin: 378px 216px">
          <path d="M 382 212 C 362 216 348 232 344 252 C 360 262 386 262 402 254 C 392 238 386 224 382 212 Z" fill="#66798F" />
          <path d="M 366 236 Q 376 246 390 250 M 356 248 Q 368 256 382 258" fill="none" stroke="#56687D" stroke-width="2" stroke-linecap="round" />
        </g>

        <!-- 身体 -->
        <g id="body">
          <ellipse cx="404" cy="236" rx="62" ry="52" transform="rotate(-8 404 236)" fill="url(#pb-body)" />
          <ellipse cx="416" cy="258" rx="42" ry="30" transform="rotate(-8 416 258)" fill="url(#pb-belly)" />
          <!-- 背上的羽纹 -->
          <path d="M 372 206 Q 388 200 402 206 M 386 196 Q 402 190 416 196" fill="none" stroke="#AFC0D2" stroke-width="3" stroke-linecap="round" />
        </g>

        <!-- 头颈组:绕脖根轻微点头 -->
        <g id="neck-head" class="head-bob" style="transform-origin: 438px 218px">
          <path
            id="neck"
            d="M 436 220 C 446 200 458 190 466 184 C 476 177 486 172 496 168"
            fill="none"
            stroke="url(#pb-body)"
            stroke-width="26"
            stroke-linecap="round"
          />
          <path d="M 442 214 C 452 196 462 188 472 182" fill="none" stroke="#AFC0D2" stroke-width="3" stroke-linecap="round" />

          <g id="head">
            <circle cx="506" cy="162" r="24" fill="url(#pb-body)" />
            <!-- 头顶毛簇 -->
            <path d="M 494 144 Q 492 130 484 128 M 502 140 Q 504 126 498 120 M 510 142 Q 516 130 512 122" fill="none" stroke="#9FB4CC" stroke-width="4" stroke-linecap="round" />
            <!-- 眼睛 -->
            <circle cx="500" cy="155" r="7.5" fill="#FFFFFF" />
            <circle cx="502" cy="156.5" r="3.8" fill="#2A2F3A" />
            <circle cx="500.4" cy="154.6" r="1.4" fill="#FFFFFF" />
          </g>

          <!-- 大嘴:上喙 + 喉囊,鹈鹕的标志 -->
          <g id="beak">
            <path
              d="M 520 152 L 618 172 L 616 180 C 606 196 586 226 562 226 C 540 226 526 200 520 172 Z"
              fill="url(#pb-beak)"
            />
            <!-- 上喙与喉囊的分界 -->
            <path d="M 521 158 C 536 178 552 200 562 220 C 574 214 596 196 615 180" fill="none" stroke="#C8801F" stroke-width="2.4" opacity="0.75" />
            <!-- 上喙高光 -->
            <path d="M 528 156 L 608 174" fill="none" stroke="#FCE0AC" stroke-width="3" stroke-linecap="round" opacity="0.8" />
            <!-- 嘴尖 -->
            <path d="M 618 172 L 612 166 L 610 178 Z" fill="#C8801F" />
          </g>
        </g>

        <!-- 近侧翅膀(身体前方,摆动最明显) -->
        <g id="wing-front" class="wing" style="transform-origin: 368px 220px">
          <path d="M 372 216 C 348 222 330 240 324 262 C 344 274 374 276 396 268 C 384 250 376 232 372 216 Z" fill="url(#pb-wing)" />
          <path d="M 352 244 Q 366 256 384 260 M 342 258 Q 358 268 376 270" fill="none" stroke="#5D718A" stroke-width="2.4" stroke-linecap="round" />
          <circle cx="372" cy="218" r="7" fill="#8DA2BD" />
        </g>
      </g>
    </g>
  </svg>
</template>

<style scoped>
.scene {
  display: block;
  width: 100%;
  height: auto;
  border-radius: 18px;
  background: linear-gradient(180deg, #bce3f5 0%, #eaf6fb 100%);
}

/* 需要旋转的元素统一以 viewBox 坐标系解析 transform-origin */
.wheel,
.crank,
.thigh,
.calf,
.wing,
.wing-back,
.head-bob,
.rig,
.shadow,
.tree {
  transform-box: view-box;
}

/* 主节奏 1.1s = 曲柄一圈 = 轮子一圈 = 脚踩两下(每只脚各一次) */
@keyframes pb-spin {
  to {
    transform: rotate(360deg);
  }
}

/*
  大腿角度由两连杆 IK 反解,16 帧对应曲柄 0°~337.5°。
  踏板位置按 SVG rotate 的实际方向取 P = C + r·(sinθ, -cosθ),
  与 pb-spin 的顺时针旋转方向一致,否则近侧腿会踩到曲柄的镜像点。
  线性插值后脚位偏差 < 1 SVG 单位,踏板半宽 18,视觉上不穿模。
*/
@keyframes pb-thigh {
  0% {
    transform: rotate(109.8deg);
  }
  6.25% {
    transform: rotate(109.4deg);
  }
  12.5% {
    transform: rotate(103.1deg);
  }
  18.75% {
    transform: rotate(93.5deg);
  }
  25% {
    transform: rotate(82.4deg);
  }
  31.25% {
    transform: rotate(71.5deg);
  }
  37.5% {
    transform: rotate(62.3deg);
  }
  43.75% {
    transform: rotate(56.1deg);
  }
  50% {
    transform: rotate(53.3deg);
  }
  56.25% {
    transform: rotate(53.2deg);
  }
  62.5% {
    transform: rotate(55.1deg);
  }
  68.75% {
    transform: rotate(59deg);
  }
  75% {
    transform: rotate(65.4deg);
  }
  81.25% {
    transform: rotate(75.2deg);
  }
  87.5% {
    transform: rotate(88.6deg);
  }
  93.75% {
    transform: rotate(102.3deg);
  }
  100% {
    transform: rotate(109.8deg);
  }
}

/* 小腿是相对大腿的角:负值表示膝盖向前屈 */
@keyframes pb-calf {
  0% {
    transform: rotate(-124.7deg);
  }
  6.25% {
    transform: rotate(-112.8deg);
  }
  12.5% {
    transform: rotate(-98.8deg);
  }
  18.75% {
    transform: rotate(-84.1deg);
  }
  25% {
    transform: rotate(-70.3deg);
  }
  31.25% {
    transform: rotate(-59deg);
  }
  37.5% {
    transform: rotate(-52.4deg);
  }
  43.75% {
    transform: rotate(-52.5deg);
  }
  50% {
    transform: rotate(-59.4deg);
  }
  56.25% {
    transform: rotate(-70.8deg);
  }
  62.5% {
    transform: rotate(-84.7deg);
  }
  68.75% {
    transform: rotate(-99.3deg);
  }
  75% {
    transform: rotate(-113.3deg);
  }
  81.25% {
    transform: rotate(-125deg);
  }
  87.5% {
    transform: rotate(-132deg);
  }
  93.75% {
    transform: rotate(-131.9deg);
  }
  100% {
    transform: rotate(-124.7deg);
  }
}

/* 车身轻微起伏:1.1s 一圈,幅度 2px */
@keyframes pb-rig {
  0%,
  100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-2px);
  }
}

/* 翅膀是翅膀,不是飞行的动作:幅度 6°,周期取 1.5 圈避免与脚完全同步 */
@keyframes pb-flap {
  0%,
  100% {
    transform: rotate(0deg);
  }
  50% {
    transform: rotate(-6deg);
  }
}

@keyframes pb-flap-back {
  0%,
  100% {
    transform: rotate(0deg);
  }
  50% {
    transform: rotate(4deg);
  }
}

/* 头部点头:2.2s 是 1.1s 的整数倍,与踩踏节奏相干而不抢拍 */
@keyframes pb-nod {
  0%,
  100% {
    transform: rotate(0deg);
  }
  50% {
    transform: rotate(2.4deg);
  }
}

@keyframes pb-shadow {
  0%,
  100% {
    transform: scale(1);
    opacity: 0.22;
  }
  50% {
    transform: scale(0.985);
    opacity: 0.18;
  }
}

@keyframes pb-tree {
  0%,
  100% {
    transform: rotate(-1.6deg);
  }
  50% {
    transform: rotate(1.6deg);
  }
}

/* 路面虚线:一个周期滚过轮周 2π·60 ≈ 377px,与轮子线速度严格一致 */
@keyframes pb-dash {
  to {
    stroke-dashoffset: -377;
  }
}

@keyframes pb-drift {
  to {
    transform: translateX(-1600px);
  }
}

.wheel {
  animation: pb-spin 1.1s linear infinite;
}
.crank {
  animation: pb-spin 1.1s linear infinite;
}
.leg-near .thigh,
.leg-far .thigh {
  animation: pb-thigh 1.1s linear infinite;
}
.leg-near .calf,
.leg-far .calf {
  animation: pb-calf 1.1s linear infinite;
}
/* 远侧腿错开半个周期,与近侧腿左右交替 */
.leg-far .thigh,
.leg-far .calf {
  animation-delay: -0.55s;
}
.rig {
  animation: pb-rig 1.1s ease-in-out infinite;
}
.shadow {
  transform-origin: 470px 418px;
  animation: pb-shadow 1.1s ease-in-out infinite;
}
.wing {
  animation: pb-flap 1.65s ease-in-out infinite;
}
.wing-back {
  animation: pb-flap-back 1.65s ease-in-out infinite;
}
.head-bob {
  animation: pb-nod 2.2s ease-in-out infinite;
}
.road-dash {
  animation: pb-dash 1.1s linear infinite;
}
.t1 {
  transform-origin: 120px 398px;
  animation: pb-tree 3.4s ease-in-out infinite;
}
.t2 {
  transform-origin: 742px 394px;
  animation: pb-tree 3.8s ease-in-out infinite;
  animation-delay: -1.1s;
}
.c1 {
  animation: pb-drift 42s linear infinite;
  animation-delay: -6s;
}
.c2 {
  animation: pb-drift 56s linear infinite;
  animation-delay: -28s;
}
.c3 {
  animation: pb-drift 36s linear infinite;
  animation-delay: -18s;
}
.c4 {
  animation: pb-drift 68s linear infinite;
  animation-delay: -44s;
}

/* 暂停:整棵 SVG 子树冻结,不销毁动画状态,恢复时从暂停点继续 */
.scene.paused * {
  animation-play-state: paused;
}
</style>
