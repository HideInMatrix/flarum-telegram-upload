# 从站点内适配器迁移

本次仓库化不直接修改正在运行的论坛。以下步骤供安排部署时使用。

1. 备份论坛数据库、`composer.json`、`composer.lock`、根目录 `extend.php`、现有 `tinker-telegram/` 目录及其配置。
2. 使用 Composer 安装本扩展，暂不启用。
3. 将旧 `tinker-telegram/config.php` 中的 `base_url`、`chat_id` 填入论坛根 `config.php` 的 `telegram-upload` 配置，并新增 `api_key`：必须与 tg-disk 私密 `NUXT_API_KEY` 完全一致且至少 32 个字符。也可以使用 `TG_DISK_API_KEY` 容器环境变量，但需移除 config.php 中同名空值。不要把实际配置提交到扩展仓库。
4. 从论坛根 `extend.php` 中移除加载 `tinker-telegram/extend.php` 的那一项，保留其他扩展项。不要同时加载旧适配器与本扩展，否则会重复注册同一下载路由。
5. 在 Flarum 后台启用 `Telegram Upload` 并执行 `php flarum cache:clear`。
6. 检查 FoF Upload 的 MIME 规则仍使用 `telegram`，文件大小上限为 10240 KB，按实际用户组测试图片与压缩包的读取。

本扩展保留 `upload_method=telegram`、Telegram `remote_id`、旧路由名称和路径。无需改写历史帖子中的链接，也不迁移或清空附件数据库。旧代码目录可以先留作回滚备份，不再加载。

回滚时先禁用新扩展，再恢复原来的根 `extend.php` 加载项和配置并清缓存。不要在还有附件依赖本扩展时直接删除扩展或附件索引。

## 删除功能

旧版本没有保存 Telegram `message_id`，本扩展仍不支持远程删除。当前仅把原先笼统的失败改为清晰提示；不把“仅删除论坛记录”当成 Telegram 删除成功。完整删除需要另行设计消息元数据持久化、受鉴权保护的 tg-disk 删除接口、失败回滚，以及 Telegram 删除限制和旧数据处理。

## 已安装此 Composer 扩展时升级

tg-disk 启用 API Key 后，旧上传客户端会收到 401；只更新 tg-disk 不会自动更新论坛适配器。先在论坛私密配置中补齐上述密钥，再执行 `composer update hideinmatrix/flarum-telegram-upload --with-dependencies` 并清缓存。密钥不会存入浏览器设置或论坛公开 API；上传失败提示鉴权问题时，请在服务器配置中核对双方密钥。历史附件读取仍走原路由。
