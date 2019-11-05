<?php
/**
 * User:    Yejia
 * Email:   ye91@foxmail.com
 */

namespace Cium\Tools;

/**
 * Csv文件输出
 * Class PhpCsv
 *
 * @package helper
 */
class PhpCsv
{
    /**
     * 输出header
     *
     * @var array
     */
    private $headers = [];

    /**
     * 打开文件
     *
     * @var
     */
    private $fp;

    /**
     * PhpCsv constructor.
     *
     * @param string|null $memory_limit 内存
     */
    public function __construct(string $memory_limit = null)
    {
        set_time_limit(0);
        ini_set('memory_limit', $memory_limit ?: '512M');
    }

    /**
     * 添加header
     *
     * @param string $header header
     */
    public function addHeader(string $header)
    {
        array_push($this->headers, $header);
    }

    /**
     * 初始化
     */
    public function init()
    {
        foreach ($this->headers as $header) header($header);
        $this->fp = fopen('php://output', 'w');
        fwrite($this->fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
    }

    /**
     * 添加数据
     *
     * @param array $row
     * @param bool  $isFlush
     */
    public function addRow(array $row, bool $isFlush = false)
    {
        fputcsv($this->fp, $row);
        if ($isFlush) $this->flush();
    }

    /**
     * 添加数据组
     *
     * @param array $rows 数据
     */
    public function addRows(array $rows)
    {
        foreach ($rows as $row) {
            fputcsv($this->fp, $row);
            unset($row);
        }
        $this->flush();
    }

    /**
     * 缓冲
     */
    public function flush()
    {
        ob_flush();
        flush();
    }

    /**
     * 关闭
     */
    public function close()
    {
        fclose($this->fp);
    }

    /**
     * 默认Header
     *
     * @param string $name 文件名称
     */
    public function defaultHeader(string $name = '')
    {
        if (!mb_strlen($name)) $name = uniqid();
        $this->addHeader('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        $this->addHeader('Content-Disposition: attachment; filename="' . $name . '.csv"');
        $this->addHeader('Cache-Control: max-age=0');
    }


//    $step = 100;
//    $nums = 10000;
//    for ($s = 1; $s <= $step; ++$s) {
//        $start = ($s - 1) * $nums;
//        $users = db()->name('user_base')->field('user_id,parent_id,channel_id')->limit($start, $nums)->select();
//        $csv->addBody($users);
//        $csv->flush();
//        unset($users);
//    }
}