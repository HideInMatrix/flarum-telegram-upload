# Telegram Upload for Flarum 2.x

通过 **FoF Upload → tg-disk → Telegram** 存储论坛附件的独立 Flarum 扩展。

使用官方 `@flarum/cli` **3.0.13** 的 Flarum 2.x 扩展模板建立，采用 Composer 包、PSR-4 自动加载、标准 `extend.php`、服务提供者和事件监听器。项目不包含论坛安装文件或实际站点配置。[模板来源](docs/scaffold.md)

## 功能与范围

- 依赖 PHP 8.3+、Flarum `^2.0`、FoF Upload `^2.0.1`。
- 支持常见图片、PDF、ZIP、7z、gzip 和纯文本；每个文件最多 **10 MiB**。
- Telegram 使用 document 保存，避免 Telegram 压缩图片和 GIF。
- 下载流式经过论坛，保留 FoF Upload 的下载权限、标签限制和记录功能。
- 兼容旧适配器生成的 `/api/tinker-telegram/{uuid}` 地址。
- 没有配置缩放、水印时，图片保留原始字节。
- **远程删除尚未实现**。删除会返回明确提示，不能冒充已经从 Telegram 删除；可在 FoF 媒体库使用“隐藏”。
- 缩略图暂不单独存储，图片预览直接读取完整图片。

## 安装

在论坛根目录执行（私有仓库需要你自己的 GitHub 读取权限）：

```sh
composer config repositories.telegram-upload vcs https://github.com/HideInMatrix/flarum-telegram-upload
composer require hideinmatrix/flarum-telegram-upload:dev-main
```

将以下配置**合并**到论坛现有 `config.php` 返回的数组中，不要覆盖数据库等已有配置：

```php
'telegram-upload' => [
    'base_url' => 'https://your-tg-disk.example.com',
    'chat_id' => '-1000000000000',
],
```

也可以配置论坛容器环境变量 `TG_DISK_BASE_URL` 和 `TG_DISK_CHAT_ID`；`config.php` 的同名配置优先。`.env.example` 只是示例，本扩展不会自动加载 `.env`。论坛不需要持有 Telegram Bot Token。

然后在管理后台启用 **Telegram Upload**，清理缓存：

```sh
php flarum cache:clear
```

在 **FoF Upload → 文件类型** 中，将需要的规则的存储位置选择为 Telegram；大小上限设置为 **10240 KB**。图片选择“完整图片预览模板”，其他文件选择“默认文件下载模板”。本扩展不会自动改变用户组权限，按论坛需要分别设置上传和下载权限。

**如果服务器已部署旧的 `tinker-telegram` 目录适配器，请先阅读 [迁移说明](docs/migration.md)，避免重复注册路由。**

## 本地开发与测试

```sh
composer install
composer validate --strict
composer lint
composer test:unit
# 只对隔离的测试数据库执行，会清空指定的测试库：
composer test:setup
composer test:integration
```

PHPUnit 集成测试使用 Flarum 官方 `flarum/testing`。测试初始化默认使用 SQLite；不要指向生产数据库。测试目录与缓存均已被 Git 忽略。

没有本地 PHP 时，可以使用官方 Composer 容器：

```sh
docker run --rm -v "$PWD:/app" -w /app composer:2 composer install
docker run --rm -v "$PWD:/app" -w /app composer:2 composer test:unit
docker run --rm -v "$PWD:/app" -w /app \
  -e FLARUM_TEST_TMP_DIR_LOCAL=/app/tests/integration/tmp \
  composer:2 composer test:integration
```

集成测试验证扩展启动与依赖注入、旧 URL、访客下载限制、授权图片响应以及删除提示。测试对 tg-disk 使用模拟 HTTP，不会发送真实附件或删除 Telegram 消息。

## 运行条件

tg-disk 需要提供 `POST /api/telegram/send` 和 `GET /file/{file_id}`，上传结果包含 `code`、`data.file_id`、`data.file_size`；Bot 及频道权限在 tg-disk 侧配置。服务器必须能访问 tg-disk，tg-disk 的文件访问规则也需允许论坛服务端读取。10 MiB 是当前对接接口的限制。

论坛数据库仍保存附件索引和权限关联，需与扩展配置一起备份。附件读取依赖论坛、tg-disk 和 Telegram 的可用性，下载流量会经过论坛。tg-disk 本身的上传鉴权和文件直链访问策略由其服务端负责。

## License

MIT，见 [LICENSE.md](LICENSE.md)。
