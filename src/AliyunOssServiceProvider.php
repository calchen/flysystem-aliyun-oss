<?php

namespace Calchen\Flysystem\AliyunOss;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use OSS\OssClient;

/**
 * Class AliyunOssServiceProvider
 * @package Calchen\Flysystem\AliyunOss
 */
class AliyunOssServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('oss', function ($app, $config) {
            $accessId = $config['access_id'];
            $accessKeySecret = $config['access_key_secret'];
            $bucket = $config['bucket'];
            $endPoint = $config['endpoint'];
            $cdnBaseUrl = Arr::get($config, 'cdn_base_url');
            $prefix = Arr::get($config, 'prefix');

            // 这里使得 endpoint 默认为 https 开头
            $endPoint = AliyunOssAdapter::getEndpointBaseURL($endPoint);
            $cdnBaseUrl = is_null($cdnBaseUrl) ? null : trim($cdnBaseUrl, '/');

            $client = new OssClient(
                $accessId,
                $accessKeySecret,
                $endPoint,
                AliyunOssAdapter::isEndpointCnameDomain($endPoint)
            );
            $adapter = new AliyunOssAdapter($client, $bucket, $endPoint, $cdnBaseUrl, $prefix);

            return new Filesystem($adapter);
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
