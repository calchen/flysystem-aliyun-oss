<h1 align="center"> 用于阿里云对象存储（OSS）的 Flysystem Adapter </h1>

<p align="center">
    <a href="https://packagist.org/packages/calchen/flysystem-aliyun-oss">
        <img alt="Latest Stable Version" src="https://img.shields.io/packagist/v/calchen/flysystem-aliyun-oss.svg">
    </a>
    <a href="https://packagist.org/packages/calchen/flysystem-aliyun-oss">
        <img alt="Total Downloads" src="https://img.shields.io/packagist/dt/calchen/flysystem-aliyun-oss.svg">
    </a>
    <a href="https://github.com/calchen/flysystem-aliyun-oss/blob/master/LICENSE">
        <img alt="License" src="https://img.shields.io/github/license/calchen/flysystem-aliyun-oss.svg">
    </a>
</p>

> [English](https://github.com/calchen/flysystem-aliyun-oss/blob/master/README_en.md)

这是一个基于阿里云 OSS SDK（2.3.0 及以上）的 Flysystem Adapter

受到 [apollopy/flysystem-aliyun-oss](https://github.com/apollopy/flysystem-aliyun-oss) 的启发，感谢该项目给予的帮助

## 安装

```shell
$ composer require calchen/flysystem-aliyun-oss
```

### Laravel

如果您的 Laravel 版本为 5.5 及以上，您不需要手动的配置文件中添加 `AliyunOssServiceProvider` Laravel 自带的扩展包发现机制会处理好一切。如是小于 5.5 版本那么需要您进行如下操作: 

打开位于 `app/Providers` 的 `AppServiceProvider.php` 文件并在 `register` 函数中添加如下内容：
```php
$this->app->register(\Calchen\Flysystem\AliyunOss\AliyunOssServiceProvider::class);
```
您也可以在配置文件 `config/app.php` 中的 `providers` 中添加如下内容：
```php
Calchen\Flysystem\AliyunOss\AliyunOssServiceProvider::class,
```
只需选择以上操作中的一种，即可加载本扩招包。

### Lumen

Lumen 并未移植扩展包自动发现机制，所以需要手动加载扩展包并复制配置文件。

打开配置文件 `bootstrap/app.php` 并在大约 81 行左右添加如下内容：
```php
$app->register(Calchen\Flysystem\AliyunOss\AliyunOssServiceProvider::class);
```

将文件系统配置文件从 `vendor/laravel/lumen-framework/config.php` 复制到 `config/filesystems.php`

## 配置

打开配置文件 `config/filesystems.php` 并在 `disks` 中添加如下内容：
```php
'oss' => [
    'driver' => 'oss',
    'access_id' => env('ALIYUN_ACCESS_KEY_ID'),
    'access_key_secret' => env('ALIYUN_ACCESS_KEY_SECRET'),
    'bucket' => env('ALIYUN_OSS_BUCKET'),
    'endpoint' => env('ALIYUN_OSS_ENDPOINT'),
    'cdn_base_url' => env('ALIYUN_OSS_CDN_BASE_URL'),  // 可选
    'prefix' => '',                                    // 可选
],
```

如果您想将阿里云 OSS 作为默认的存储方式，那么可以在 `.env` 文件中增加配置项 `FILESYSTEM_DRIVER=oss`

### 配置说明
| 配置项                	| 必须 	| 说明                                 	| 备注                  	|
|-------------------	|------	|--------------------------------------	|-----------------------	|
| driver            	| 是   	| 驱动名称                             	| 默认值：oss，不可修改   	|
| access_id         	| 是   	| 用于身份验证的 AccessKey ID          	| 见下文“安全提醒”         	|
| access_key_secret 	| 是   	| 用于身份验证的  AccessKey Key Secret 	| 见下文“安全提醒”      	    |
| bucket            	| 是   	| 存储空间名称                         	| -                     	|
| endpoint          	| 是   	| 地域节点                             	| 见下文“地域节点”      	    |
| cdn_base_url      	| 否   	| CDN 基础路径                         	| 见下文“CDN 基础路径”  	    |
| prefix            	| 否   	| 保存路径的统一前缀                   	| -                     	|

#### 地域节点（endpoint）

官方地域节点：[访问域名和数据中心](https://help.aliyun.com/document_detail/31837.html)

地域节点可以是域名，也可以是以 `http://域名` 或  `https://域名`。

如果地域节点是域名则默认使用 https，如果需要使用 http 请使用 `http://域名`

#### 用户域名（CNAME domain）

设置成功并正常解析至阿里云 OSS 访问域名的用户域名可作为地域节点使用，如果是解析到 CDN 节点的用户域名是不可以作为地域节点使用的！！！

#### CDN 基础路径（CDN base URL）

如果您启用了 CDN 且 CDN 回源至阿里云 OSS，那么建议您设置 cdn_base_url，设置此项后您获取到的文件 URL 将全部以 cdn_base_url 开头

#### 安全提醒

为了安全，请使用子账户的 AccessKey ID 和 AccessKey Key Secret，请务必不要使用主账户的 AccessKey ID 和 AccessKey Key Secret

## 开源协议

[MIT](http://opensource.org/licenses/MIT)