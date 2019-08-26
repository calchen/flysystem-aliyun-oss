<h1 align="center"> Flysystem Adapter for Aliyun OSS </h1>

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

> [中文](https://github.com/calchen/flysystem-aliyun-oss/blob/master/README.md)

This is a Flysystem adapter for the Aliyun OSS ~2.3.0

inspire by [apollopy/flysystem-aliyun-oss](https://github.com/apollopy/flysystem-aliyun-oss)

## Installing

```shell
$ composer require calchen/flysystem-aliyun-oss
```

### Laravel

For Laravel >=5.5, no need to manually add `AliyunOssServiceProvider` into config. It uses package auto discovery feature. Skip this if you are on >=5.5, if not: 

Open your `AppServiceProvider` (located in `app/Providers`) and add this line in `register` function
```php
$this->app->register(\Calchen\Flysystem\AliyunOss\AliyunOssServiceProvider::class);
```
or open your `config/app.php` and add this line in `providers` section
```php
Calchen\Flysystem\AliyunOss\AliyunOssServiceProvider::class,
```

### Lumen

Open your `bootstrap/app.php` and add this line
```php
$app->register(Calchen\Flysystem\AliyunOss\AliyunOssServiceProvider::class);
```

Copy configuration file from `vendor/laravel/lumen-framework/config.php` to `config/filesystems.php`

## Configuration

Open your `config/filesystems.php` and add these lines in `disks` section
```php
'oss' => [
    'driver' => 'oss',
    'access_id' => env('ALIYUN_ACCESS_KEY_ID'),
    'access_key' => env('ALIYUN_ACCESS_KEY_SECRET'),
    'bucket' => env('ALIYUN_OSS_BUCKET'),
    'endpoint' => env('ALIYUN_OSS_ENDPOINT'),
    'cdn_base_url' => env('ALIYUN_OSS_CDN_BASE_URL'),
],
```

If you want to use OSS by default, set `FILESYSTEM_DRIVER=oss` in `.env`

## License

MIT