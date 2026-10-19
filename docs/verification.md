# 本地验证记录

日期：2026-10-19。

环境：官方 `composer:2` 容器，PHP 8.5.11；Composer 安装解析到 Flarum 2.0.2、FoF Upload 2.0.1、flarum/testing 2.0.1。SQLite 测试数据库位于本项目被 Git 忽略的 `tests/integration/tmp/`。

已执行：

- `composer validate --strict`：通过。
- `composer lint`：PHP 语法检查通过。
- `composer test:unit`：16 项测试、27 项断言通过。
- `composer test:integration`：4 项测试、7 项断言通过。

覆盖配置缺失与 URL 校验、上传 multipart 原始字节、Telegram document 文件名、上游失败及大小不一致、超限请求拦截、下载响应和文件名、真实 Flarum 扩展启动、旧 URL 兼容、访客拒绝、管理员图片读取及明确的删除提示。

PHP 8.5 下，上游 `middlewares/utils` 的可空参数声明触发 3 条 deprecation 提示；没有测试错误或失败。未修改依赖源码以隐藏这些提示。

本轮使用模拟 tg-disk HTTP，不访问真实 Telegram；没有部署到线上论坛，也没有改变线上用户组权限。上线迁移仍需按 `docs/migration.md` 执行。
