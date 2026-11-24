# 新增 API 模块

在现有 PHP 项目中增加一个接口或一组接口。

## 开始前

1. 读取 `AGENTS.md`、`.agents/docs/environment.md`。
2. 检查现有代码：`composer.json`、路由文件、Controller/Action、Service、Repository、DTO、错误处理、测试。
3. 确认项目框架与分层方式，沿用现有模式，不另起一套。

## 步骤

1. **定位**：找到同类接口的实现位置，确认命名、目录、依赖注入方式。
2. **设计契约**：按 `.agents/skills/api-design/SKILL.md` 确定 URL、方法、状态码、请求/响应结构、错误格式。
3. **实现**：
   - 路由注册。
   - 输入校验（FormRequest / Validator / Valinor）。
   - Controller/Action 只做转换与调用。
   - Service 承载业务逻辑。
   - Repository/Model 负责数据访问。
   - 输出使用 Resource/DTO，不直接返回 ORM 模型。
4. **认证与授权**：挂载正确的中间件，校验资源归属，防 IDOR。
5. **错误处理**：业务异常映射为 4xx，系统异常 5xx，结构统一。
6. **测试**：至少覆盖成功路径、校验失败、未认证、无权限、资源不存在。
7. **文档**：更新 OpenAPI/接口文档；如项目用 swagger-php，补 Attributes。

## 完成后

- 运行项目已有的测试与静态分析。
- 用 curl / Postman / Bruno 验证实际响应。
- 按 `.agents/skills/security/SKILL.md` 检查输入输出。
- 更新 `.agents/memory/worklog.md`。

## 参考

- `.agents/skills/api-design/SKILL.md`
- `.agents/skills/laravel/SKILL.md` 或 `.agents/skills/slim-api/SKILL.md`
- `.agents/skills/data-access/SKILL.md`
- `.agents/skills/security/SKILL.md`
