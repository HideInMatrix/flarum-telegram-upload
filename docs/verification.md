# 本地验证记录

日期：2026-10-19。

环境：基于官方 `composer:2` 并安装 GD 的 `docker/Dockerfile.test` 容器，PHP 8.5.11；Composer 安装解析到 Flarum 2.0.2、FoF Upload 2.0.1、flarum/testing 2.0.1。SQLite 测试数据库位于本项目被 Git 忽略的 `tests/integration/tmp/`。完整论坛 API 启动需要 GD，原始 Composer 镜像未包含此扩展。

已执行：

- `composer validate --strict`：通过。
- `composer lint`：PHP 语法检查通过。
- `composer test:unit`：25 项测试、49 项断言通过。
- `composer test:integration`：6 项测试、10 项断言通过。

覆盖配置缺失与 URL 校验、上传 multipart 原始字节、Telegram document 文件名、上游失败及大小不一致、超限请求拦截、下载响应和文件名、真实 Flarum 扩展启动、旧 URL 兼容、访客拒绝、管理员图片读取及明确的删除提示。

本次新增覆盖：API Key 缺失/过短/控制字符拒绝、上传 `X-API-Key`、401/403 响应不泄露密钥或上游正文、禁止重定向转发密钥、公开文件请求不带密钥，以及论坛公开 API 不暴露密钥。

PHP 8.5 下，上游 `middlewares/utils` 的可空参数声明触发 3 条 deprecation 提示；没有测试错误或失败。未修改依赖源码以隐藏这些提示。

本轮使用模拟 tg-disk HTTP，不访问真实 Telegram；没有部署到线上论坛，也没有改变线上用户组权限。上线迁移仍需按 `docs/migration.md` 执行。
