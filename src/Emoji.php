<?php

/**
 * Author:  Yejia
 * Email:   ye91@foxmail.com
 */


namespace Cium\Tools;

/**
 * 处理 Emoji 表情
 * Class Emoji
 *
 * @package library\tools
 */
class Emoji
{
    /**
     * Emoji原形转换为String
     *
     * @param string $content
     *
     * @return string
     */
    public static function encode($content)
    {
        return json_decode(preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function ($maps) {
            return addslashes($maps[0]);
        }, json_encode($content)));
    }

    /**
     * Emoji字符串转换为原形
     *
     * @param string $content
     *
     * @return string
     */
    public static function decode($content)
    {
        return json_decode(preg_replace_callback('/\\\\\\\\/i', function () {
            return '\\';
        }, json_encode($content)));
    }

    /**
     * Emoji字符串清清理
     *
     * @param string $content
     *
     * @return string
     */
    public static function clear($content)
    {
        return preg_replace_callback('/./u', function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $content);
    }
}
