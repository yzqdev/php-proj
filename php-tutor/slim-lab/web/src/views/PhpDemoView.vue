<script setup lang="ts">
import { ref } from 'vue'
import { phpDemoAction } from '@/api/php-demo'

const logContainer = ref<{ type: string; time: string; action: string; message: string }[]>([])

interface DemoAction {
  title: string
  desc: string
  action: string
  variant: 'primary' | 'success' | 'warning'
}

const actions: DemoAction[] = [
  { title: '数组操作', desc: 'sum, product, sort, unique, chunk 等', action: 'array_ops', variant: 'primary' },
  { title: '字符串操作', desc: '大小写转换, 替换, 截取, 分割等', action: 'string_ops', variant: 'primary' },
  { title: '日期时间', desc: '格式化, 计算, 时区, 相对时间', action: 'date_ops', variant: 'success' },
  { title: 'JSON 处理', desc: '编码解码, 选项, 美化输出', action: 'json_ops', variant: 'success' },
  { title: '正则表达式', desc: '匹配, 替换, 分割, 命名捕获', action: 'regex_ops', variant: 'success' },
  { title: '文件操作', desc: '读写, 信息获取, 临时文件', action: 'file_ops', variant: 'warning' },
  { title: '随机与加密', desc: '随机数, UUID, 密码哈希', action: 'random_ops', variant: 'warning' },
  { title: '过滤验证', desc: 'filter_var, 邮箱, URL, 数字验证', action: 'filter_ops', variant: 'success' },
  { title: '闭包与箭头函数', desc: 'map, filter, reduce, 一等可调用', action: 'closure_ops', variant: 'primary' },
]

async function runAction(item: DemoAction) {
  const time = new Date().toLocaleTimeString('zh-CN')
  try {
    const { data: res } = await phpDemoAction(item.action)
    logContainer.value.unshift({
      type: 'success',
      time,
      action: item.action,
      message: res.data?.message ?? '',
    })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : String(e)
    logContainer.value.unshift({
      type: 'error',
      time,
      action: item.action,
      message,
    })
  }
}

function clearLog() {
  logContainer.value = []
}
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-4">
      <div
        v-for="item in actions"
        :key="item.action"
        class="rounded-xl bg-white p-5 shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
      >
        <h3 class="mb-2 text-base font-semibold text-gray-800">
          {{ item.title }}
        </h3>
        <p class="mb-3 text-sm text-gray-500">{{ item.desc }}</p>
        <el-button
          :type="item.variant"
          class="w-full"
          @click="runAction(item)"
        >
          执行演示
        </el-button>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl bg-[#1e1e2e] shadow-xl">
      <div
        class="flex items-center justify-between bg-gradient-to-r from-[#334155] to-[#1e293b] px-5 py-4"
      >
        <h2 class="text-base font-semibold text-white">操作结果</h2>
        <el-button size="small" @click="clearLog">清空</el-button>
      </div>
      <div class="max-h-[500px] overflow-y-auto p-5">
        <div v-if="logContainer.length === 0" class="py-10 text-center text-gray-500">
          点击上方按钮执行 PHP 操作演示
        </div>
        <div
          v-for="(log, index) in logContainer"
          :key="index"
          :class="[
            'mb-2 rounded-lg border-l-3 p-3 font-mono text-sm',
            log.type === 'success'
              ? 'border-green-500 bg-green-500/15 text-green-300'
              : 'border-red-500 bg-red-500/15 text-red-300',
          ]"
        >
          <div class="mb-1 text-xs text-gray-400">{{ log.time }}</div>
          <div class="font-semibold text-indigo-300">[{{ log.action }}]</div>
          <div class="whitespace-pre-wrap">{{ log.message }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
