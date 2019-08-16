<?php

namespace Calchen\Flysystem\AliyunOss;

use Storage;
use OSS\OssClient;
use League\Flysystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use ApolloPY\Flysystem\AliyunOss\Plugins\PutFile;
use ApolloPY\Flysystem\AliyunOss\Plugins\SignedDownloadUrl;

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
            $accessKey = $config['access_key'];
            $endPoint = $config['endpoint'];
            $bucket = $config['bucket'];

            $prefix = null;
            if (isset($config['prefix'])) {
                $prefix = $config['prefix'];
            }

            $client = new OssClient($accessId, $accessKey, $endPoint, AliyunOssAdapter::isEndpointCNAMEDomain($endPoint));
            $adapter = new AliyunOssAdapter($client, $bucket, $prefix, [
                'endpoint' => $endPoint,
                'bucket' => $bucket,
                'cdn_base_url' => empty($config['cdn_base_url']) ? null : trim($config['cdn_base_url'], '/')
            ]);

            $filesystem = new Filesystem($adapter);

            return $filesystem;
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
