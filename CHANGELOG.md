# Changelog

## Unreleased

- 适配 tg-disk 的私密 API Key：上传发送 `X-API-Key`，新增 config.php `api_key` / `TG_DISK_API_KEY`，要求至少 32 个字符。
- 对 401/403 提供明确鉴权提示，验证密钥不进入公开 API 或文件读取请求。
- 补充 tg-disk 接口核对记录、官方模板来源说明与含 GD 的开发测试镜像。
- 将原站点内 Telegram 附件适配整理为标准 Flarum 2.x Composer 扩展。
- 使用官方 Flarum CLI 3.0.13 模板、PSR-4、独立事件监听器及服务提供者。
- 将站点配置分离为论坛 config.php / 容器环境变量，仓库仅包含占位示例。
- 保留原有附件 URL、Telegram 存储标识及 FoF Upload 下载权限检查。
- 为尚未实现的远程删除提供明确提示。
- 加入单元测试、Flarum 集成测试和官方后端 CI。
