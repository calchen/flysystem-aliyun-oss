<?php


namespace Calchen\Flysystem\AliyunOss;

use OSS\Core\OssUtil;

/**
 * Trait AliyunHelperTrait
 * @package Calchen\Flysystem\AliyunOss
 */
trait AliyunHelperTrait
{
    public static function startsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    public static function endsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    /**
     * endpoint 不以 ".aliyuncs.com" 结尾的且不是 IP 的都认为是用户域名，即 CNAME domain
     *
     * @link https://help.aliyun.com/document_detail/31837.html 访问域名和数据中心
     *
     * @param $endpoint
     *
     * @return boolean
     */
    public static function isEndpointCnameDomain($endpoint)
    {
        return !static::endsWith($endpoint, ".aliyuncs.com") &&
            !OssUtil::isIPFormat(static::getEndpointDomain($endpoint));
    }

    /**
     * 获取 endpoint 的域名
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
     * 获取以 endpoint 为域名的 base URL，默认为 https
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
            return "https://{$endpoint}";
        }
    }
}