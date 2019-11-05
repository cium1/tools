<?php
/**
 * Author:  Yejia
 * Email:   ye91@foxmail.com
 */

namespace Cium\Tools;

/**
 * 批量CURL下载
 * Class BatchDownLoad
 *
 * @package helper
 */
class BatchDownLoad
{
    /**
     * 下载文件
     *
     * @var array
     */
    private $download;

    /**
     * 保存路径
     *
     * @var string
     */
    private $savePath;

    /**
     * 最大解析数量
     *
     * @var int
     */
    private $maxProcessNum;

    /**
     * 超时
     *
     * @var int
     */
    private $timeout;

    /**
     * 代理
     *
     * @var array
     */
    private $userAgents = [
        "Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11",
        "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0",
        "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; InfoPath.3; rv:11.0) like Gecko",
        "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50",
        "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)",
        "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
    ];

    /**
     * BatchDownLoad constructor.
     */
    public function __construct()
    {
        $this->download = [];
        $this->savePath = __DIR__ . DIRECTORY_SEPARATOR . 'download';
        $this->maxProcessNum = 10;
        $this->timeout = 20;
    }

    /**
     * 设置最大下载数量
     *
     * @param int $number
     *
     * @return $this
     */
    public function setMaxProcessNum(int $number)
    {
        $this->maxProcessNum = $number;
        return $this;
    }

    /**
     * 下载配置
     *
     * @param array $configs
     *
     * @return $this
     */
    public function setDownload(array $configs)
    {
        $this->download = $configs;
        return $this;
    }

    /**
     * 设置保存路径
     *
     * @param string $path
     *
     * @return $this
     */
    public function setSavePath(string $path)
    {
        $this->savePath = rtrim($path, DIRECTORY_SEPARATOR);
        return $this;
    }

    /**
     * 设置超时
     *
     * @param int $number
     *
     * @return $this
     */
    public function setTimeOut(int $number)
    {
        $this->timeout = $number;
        return $this;
    }

    /**
     * 执行下载
     *
     * @return array
     */
    public function run()
    {
        //创建目录
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777, true);
        }
        //处理数组索引为整数的问题
        foreach ($this->download as $key => $item) if (is_integer($key)) {
            $this->download['i-' . $key] = $this->download[$key];
            unset($this->download[$key]);
        }
        $handleArr = [];
        //分片下载
        while (count($this->download) > 0) {
            $processNum = count($this->download) > $this->maxProcessNum ? $this->maxProcessNum : count($this->download);
            $handleArr = array_merge($handleArr, $this->process(array_splice($this->download, 0, $processNum)));
        }
        //处理数组索引为整数的问题
        foreach ($handleArr as $key => $item) if (strpos($key, 'i-') === 0) {
            $handleArr[ltrim($key, 'i-')] = $handleArr[$key];
            unset($handleArr[$key]);
        }
        return $handleArr;
    }

    /**
     * 数据过滤
     *
     * @param array $download
     */
    private static function downFilter(array &$download)
    {
        foreach ($download as $k => $item) {
            $item = array_values($item);
            if (!isset($item[1])) {
                unset($download[$k]);
            }
        }
    }

    /**
     * 解析下载
     *
     * @param array $download
     *
     * @return array
     */
    private function process(array $download)
    {
        //数据过滤
        self::downFilter($download);
        // 文件资源
        $fp = [];
        // curl会话
        $ch = [];
        // 执行结果
        $result = [];
        // 创建curl handle
        $mh = curl_multi_init();
        // 循环设定数量
        foreach ($download as $k => $item) {
            $fp[$k] = fopen($this->savePath . DIRECTORY_SEPARATOR . $item[1], 'w');
            $ch[$k] = curl_init();
            curl_setopt($ch[$k], CURLOPT_URL, $item[0]);
            curl_setopt($ch[$k], CURLOPT_FILE, $fp[$k]);
            curl_setopt($ch[$k], CURLOPT_POST, false);
            curl_setopt($ch[$k], CURLOPT_HEADER, false);
            curl_setopt($ch[$k], CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch[$k], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch[$k], CURLOPT_USERAGENT, $this->userAgents[array_rand($this->userAgents, 1)]);
            curl_multi_add_handle($mh, $ch[$k]);
        }
        // 执行
        $active = null;
        do {
            curl_multi_exec($mh, $active);
        } while ($active);
        // 获取数据
        foreach ($fp as $k => $v) {
            fwrite($v, curl_multi_getcontent($ch[$k]));
        }
        // 关闭curl handle与文件资源
        foreach ($download as $k => $item) {
            curl_multi_remove_handle($mh, $ch[$k]);
            curl_close($ch[$k]);
            fclose($fp[$k]);
            // 检查是否下载成功
            if (file_exists($this->savePath . DIRECTORY_SEPARATOR . $item[1])) {
                $result[$k] = $this->savePath . DIRECTORY_SEPARATOR . $item[1];
            } else {
                $result[$k] = false;
            }
        }
        curl_multi_close($mh);
        return $result;
    }

    /**
     * demo
     */
    public function demo()
    {
        $faker = \Faker\Factory::create('zh_CN');
        $down = [];
        foreach (range(1, 20) as $item) {
            $url = $faker->imageUrl();
            $down[$item] = [$url, $item . '.' . (!empty(pathinfo($url, PATHINFO_EXTENSION)) ? pathinfo($url, PATHINFO_EXTENSION) : 'png')];
        }
        $this->setDownload($down);
        print_r($this->run());
    }
}
