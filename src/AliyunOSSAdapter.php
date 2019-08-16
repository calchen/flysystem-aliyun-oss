<?php

namespace Calchen\Flysystem\AliyunOss;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\Adapter\Polyfill\StreamedTrait;
use League\Flysystem\Config;
use League\Flysystem\Util;
use OSS\Core\OssException;
use OSS\Core\OssUtil;
use OSS\OssClient;

class AliyunOssAdapter extends AbstractAdapter
{

    use StreamedTrait;
    use NotSupportingVisibilityTrait;
    /**
     * @var OssClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $bucket;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected static $mappingOptions = [
        'mimetype' => OssClient::OSS_CONTENT_TYPE,
        'size' => OssClient::OSS_LENGTH,
        'filename' => OssClient::OSS_CONTENT_DISPOSTION,
    ];

    /**
     * Constructor.
     *
     * @param OssClient $client
     * @param string $bucket
     * @param string $prefix
     * @param array $options
     */
    public function __construct(OssClient $client, $bucket, $prefix = null, array $options = [])
    {
        $this->client = $client;
        $this->bucket = $bucket;
        $this->setPathPrefix($prefix);
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Get the OSSClient bucket.
     *
     * @return string
     */
    public function getBucket()
    {
        return $this->bucket;
    }

    /**
     * Set the OSSClient bucket.
     *
     * @param $bucket
     */
    public function setBucket($bucket)
    {
        $this->bucket = $bucket;
    }

    /**
     * Get the OSSClient instance.
     *
     * @return OSSClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getUrl($path)
    {
        $object = $this->applyPathPrefix($path);

        if (is_null($CDNBaseURL = $this->options['cdn_base_url'])) {
            return static::getEndpointBaseURL($this->options['endpoint'])."/{$object}";
        }

        return "{$CDNBaseURL}/{$object}";
    }

    /**
     * Get a temporary URL for the file at the given path.
     *
     * @param string $path
     * @param \DateTimeInterface $expiration
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getTemporaryUrl($path, $expiration)
    {
        $object = $this->applyPathPrefix($path);

        try {
            $url = $this->client->signUrl($this->bucket, $path, $expiration, OssClient::OSS_HTTP_GET, $object);
        } catch (OssException $e) {
            return false;
        }

        return $url;
    }

    public function write($path, $contents, Config $config)
    {
        $object = $this->applyPathPrefix($path);
        $options = $this->getOptionsFromConfig($config);

        if (! isset($options[OssClient::OSS_LENGTH])) {
            $options[OssClient::OSS_LENGTH] = Util::contentSize($contents);
        }

        if (! isset($options[OssClient::OSS_CONTENT_TYPE])) {
            $options[OssClient::OSS_CONTENT_TYPE] = Util::guessMimeType($path, $contents);
        }

        try {
            $this->client->putObject($this->bucket, $object, $contents, $options);
        } catch (OssException $e) {
            return false;
        }

        $type = 'file';
        $result = compact('type', 'path', 'contents');
        $result['mimetype'] = $options[OssClient::OSS_CONTENT_TYPE];
        $result['size'] = $options[OssClient::OSS_LENGTH];

        return $result;
    }

    public function update($path, $contents, Config $config)
    {
        $this->write($path, $contents, $config);
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        if (!$this->copy($path, $newpath)) {
            return false;
        }

        return $this->delete($path);
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newPath
     *
     * @return bool
     */
    public function copy($path, $newPath)
    {
        $object = $this->applyPathPrefix($path);
        $newObject = $this->applyPathPrefix($newPath);

        try {
            $this->client->copyObject($this->bucket, $object, $this->bucket, $newObject);
        } catch (OssException $e) {
            return false;
        }

        return true;
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $object = $this->applyPathPrefix($path);

        try {
            $this->client->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            return false;
        }

        return true;
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     * @throws OssException
     */
    public function deleteDir($dirname)
    {
        $list = $this->listContents($dirname, true);

        $objects = [];
        foreach ($list as $val) {
            if ($val['type'] === 'file') {
                $objects[] = $this->applyPathPrefix($val['path']);
            } else {
                $objects[] = $this->applyPathPrefix($val['path']).'/';
            }
        }

        try {
            $this->client->deleteObjects($this->bucket, $objects);
        } catch (OssException $e) {
            return false;
        }

        return true;
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        $object = $this->applyPathPrefix($dirname);
        $options = $this->getOptionsFromConfig($config);

        try {
            $this->client->createObjectDir($this->bucket, $object, $options);
        } catch (OssException $e) {
            return false;
        }

        return ['path' => $dirname, 'type' => 'dir'];
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        $object = $this->applyPathPrefix($path);

        try {
            $exists = $this->client->doesObjectExist($this->bucket, $object);
        } catch (OssException $e) {
            return false;
        }

        return $exists;
    }

    /**
     * Reads a file.
     *
     * @param string $path
     *
     * @return array|false
     *
     * @see League\Flysystem\ReadInterface::read()
     */
    public function read($path)
    {
        $object = $this->applyPathPrefix($path);

        try {
            $contents = $this->client->getObject($this->bucket, $object);
        } catch (OssException $e) {
            return false;
        }

        return compact('contents', 'path');
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     * @throws OssException
     */
    public function listContents($directory = '', $recursive = false)
    {
        $directory = rtrim($this->applyPathPrefix($directory), '\\/');
        if ($directory) {
            $directory .= '/';
        }

        $bucket = $this->bucket;
        $delimiter = '/';
        $nextMarker = '';
        $maxKeys = 1000;
        $options = [
            'delimiter' => $delimiter,
            'prefix' => $directory,
            'max-keys' => $maxKeys,
            'marker' => $nextMarker,
        ];

        $listObjectInfo = $this->client->listObjects($bucket, $options);

        $objectList = $listObjectInfo->getObjectList(); // 文件列表
        $prefixList = $listObjectInfo->getPrefixList(); // 目录列表

        $result = [];
        foreach ($objectList as $objectInfo) {
            if ($objectInfo->getSize() === 0 && $directory === $objectInfo->getKey()) {
                $result[] = [
                    'type' => 'dir',
                    'path' => $this->removePathPrefix(rtrim($objectInfo->getKey(), '/')),
                    'timestamp' => strtotime($objectInfo->getLastModified()),
                ];
                continue;
            }

            $result[] = [
                'type' => 'file',
                'path' => $this->removePathPrefix($objectInfo->getKey()),
                'timestamp' => strtotime($objectInfo->getLastModified()),
                'size' => $objectInfo->getSize(),
            ];
        }

        foreach ($prefixList as $prefixInfo) {
            if ($recursive) {
                $next = $this->listContents($this->removePathPrefix($prefixInfo->getPrefix()), $recursive);
                $result = array_merge($result, $next);
            } else {
                $result[] = [
                    'type' => 'dir',
                    'path' => $this->removePathPrefix(rtrim($prefixInfo->getPrefix(), '/')),
                    'timestamp' => 0,
                ];
            }
        }

        return $result;
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        $object = $this->applyPathPrefix($path);

        try {
            $result = $this->client->getObjectMeta($this->bucket, $object);
        } catch (OssException $e) {
            return false;
        }

        return [
            'type' => 'file',
            'dirname' => Util::dirname($path),
            'path' => $path,
            'timestamp' => strtotime($result['last-modified']),
            'mimetype' => $result['content-type'],
            'size' => $result['content-length'],
        ];
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the last modified time of a file as a timestamp.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get options from the config.
     *
     * @param Config $config
     *
     * @return array
     */
    protected function getOptionsFromConfig(Config $config)
    {
        $options = $this->options;
        foreach (static::$mappingOptions as $option => $ossOption) {
            if (!$config->has($option)) {
                continue;
            }
            $options[$ossOption] = $config->get($option);
        }

        return $options;
    }

    public static function startsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    public static function endsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    /**
     * Endpoint 不以 ".aliyuncs.com" 结尾的，都认为是用户域名，及 CNAME domain
     * @link https://help.aliyun.com/document_detail/31837.html 访问域名和数据中心
     *
     * @param $endpoint
     *
     * @return boolean
     */
    public static function isEndpointCNAMEDomain($endpoint)
    {
        return static::endsWith($endpoint, ".aliyuncs.com") &&
            !OssUtil::isIPFormat(static::getEndpointDomain($endpoint));
    }

    /**
     * 获取 Endpoint 的域名
     *
     * @param $endpoint
     *
     * @return bool|string
     */
    public static function getEndpointDomain($endpoint)
    {
        $domain = $endpoint;
        if (static::startsWith($endpoint, 'http://')) {
            $domain = substr($endpoint, strlen('http://'));
        } elseif (static::startsWith($endpoint, 'https://')) {
            $domain = substr($endpoint, strlen('https://'));
        }

        return $domain;
    }

    /**
     * 获取 Endpoint 的 baseUrl 默认为 http
     *
     * @param $endpoint
     *
     * @return string
     */
    public static function getEndpointBaseURL($endpoint)
    {
        if (static::startsWith($endpoint, 'http://') ||
            static::startsWith($endpoint, 'https://')
        ) {
            return $endpoint;
        } else {
            return "http://{$endpoint}";
        }
    }
}