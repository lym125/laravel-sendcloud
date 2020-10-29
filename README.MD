Laravel SendCloud
=================================

Laravel SendCloud 提供了用于 Laravel 的 SendCloud 邮件驱动，让你可以简单方便的使用 [SendCloud](https://www.sendcloud.net/doc/email_v2/) 发送邮件。

## 安装

```
composer require "lym125/laravel-sendcloud"
```

## 配置

在 `.env` 中配置 [SendCloud](https://www.sendcloud.net/doc/email_v2/) 密钥， 并修改邮件驱动为 `sendcloud`

```
MAIL_MAILER=sendcloud

SENDCLOUD_API_KEY=BfL0MUtrdo9azHUj
SENDCLOUD_API_USER=lym125_test_4B8soH
```

## 使用

具体使用请查看 [官方文档](https://laravel.com/docs/8.x/mail)
