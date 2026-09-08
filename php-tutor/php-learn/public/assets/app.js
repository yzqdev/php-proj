/* ============================================================
   前端脚本
   ------------------------------------------------------------
   刻意保持极简，不引入任何框架：
     - 关掉 flash 消息
     - 表单删除确认（onclick="return confirm(...)" 已覆盖，这里保留扩展位）
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
    // 关闭 flash 提示
    document.querySelectorAll('.flash-close').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.flash')?.remove();
        });
    });

    // 分类下拉改变即提交（list.php 里的 onchange 已处理，这里是兜底）
    document.querySelectorAll('select[data-autosubmit]').forEach(sel => {
        sel.addEventListener('change', () => sel.closest('form')?.submit());
    });

    // 表单简单前端校验：required 属性走浏览器原生
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', (e) => {
            // 留作扩展位：真实项目可在这里加 CSRF 头校验、loading 状态等
        });
    });
});
