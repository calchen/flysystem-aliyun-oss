<h1 align="center"> flysystem-aliyun-oss </h1>

<p align="center"> Flysystem Adapter for Aliyun OSS </p>

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
    'access_key_secret' => env('ALIYUN_ACCESS_KEY_SECRET'),
    'bucket' => env('ALIYUN_OSS_BUCKET'),
    'endpoint' => env('ALIYUN_OSS_ENDPOINT'),
    'cdn_base_url' => env('ALIYUN_OSS_CDN_BASE_URL'),  // optional
    'prefix' => '',                                    // optional
],
```

If you want to use OSS by default, set `FILESYSTEM_DRIVER=oss` in `.env`

### Details
| key               	| required 	| remarks                      	|
|-------------------	|----------	|------------------------------	|
| driver            	| Y        	| default：oss, Do not change! 	|
| access_id         	| Y        	| See 'Security'               	|
| access_key_secret 	| Y        	| See 'Security'               	|
| bucket            	| Y        	| -                            	|
| endpoint          	| Y        	| See 'Endpoint'               	|
| cdn_base_url      	| N        	| See 'CDN base URL'           	|
| prefix            	| N        	| -                            	|

#### Endpoint

official endpoint list ：[Regions and endpoints](https://www.alibabacloud.com/help/doc-detail/31837.htm?spm=a2c63.p38356.b99.26.4655465afRzpga)

Endpoint can be a domain name, and can be either `http://domain_name` or `http://domain_name`

If your endpoint is the domain name, HTTPS is used by default, and `http://domain_name` is used if HTTP is required

#### Custom Domain Name

When you bind a custom domain name, and mapped the CNAME record of the domain correctly. You can use this domain as endpoint.

In particular, if this custom domain name is mapped to the CDN CNAME, it can't used as endpoint!

#### CDN base URL

If you config CDN service for your Aliyun OSS Bucket (Aliyun CDN or other CDN service). It is recommended that you should set the cdn_base_url so that all the file urls you get start with cdn_base_url.

#### Security

For security you should use AccessKey ID and AccessKey Key Secret of RAM users, and should never use AccessKey ID and AccessKey Key Secret of cloud account

## Usage

If you are using Laravel or Lumen ,you can get signed URL from private bucket like this `\Storage::disk('oss')->temporaryUrl($filePath);` 

## License

[MIT](http://opensource.org/licenses/MIT)