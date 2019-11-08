<?php

/**
 * Author:  Yejia
 * Email:   ye91@foxmail.com
 */

namespace Cium\Tools;


class Xml
{
    static private $instance;
    // 数据编码(utf-8)
    private $encoding = '';
    // 根节点名
    private $rootNode = 'root';
    // 根节点属性
    private $rootAttr = '';
    // 数字索引的子节点名
    private $numericNode = 'item';
    // 数字索引子节点key转换的属性名
    private $numericKey = 'id';

    /**
     * 实例
     *
     * @return Xml
     */
    static public function instance()
    {
        if (!self::$instance instanceof self) self::$instance = new self;
        return self::$instance;
    }

    /**
     * 编码
     *
     * @param string $encoding
     *
     * @return $this
     */
    public function encoding(string $encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * 根节点
     *
     * @param string $node
     * @param string $attr
     *
     * @return $this
     */
    public function rootNode(string $node, string $attr = '')
    {
        $this->rootNode = $node;
        $this->rootAttr = $attr;
        return $this;
    }


    /**
     * 数字节点名称
     *
     * @param string $node
     * @param string $key
     *
     * @return $this
     */
    public function numericNode(string $node, string $key = 'id')
    {
        $this->numericNode = $node;
        $this->numericKey = $key;
        return $this;
    }


    /**
     * @param $data
     *
     * @return string
     */
    public function encode($data)
    {
        if (is_string($data)) {
            if (strpos($data, '<?xml') !== 0) {
                $encoding = strlen($this->encoding) ? " encoding=\"{$this->encoding}\"" : '';
                $xml = "<?xml version=\"1.0\"{$encoding}?>";
                $data = $xml . $data;
            }
            return $data;
        }

        return $this->xmlEncode($data, $this->rootNode, $this->numericNode, $this->rootAttr, $this->numericKey, $this->encoding);
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    static public function decode($data)
    {
        $disableLibxmlEntityLoader = libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        libxml_disable_entity_loader($disableLibxmlEntityLoader);
        return $values;
    }

    /**
     * XML编码
     *
     * @access protected
     *
     * @param mixed  $data     数据
     * @param string $root     根节点名
     * @param string $item     数字索引的子节点名
     * @param string $attr     根节点属性
     * @param string $id       数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     *
     * @return string
     */
    private function xmlEncode($data, $root, $item, $attr, $id, $encoding)
    {
        if (is_array($attr)) {
            $array = [];
            foreach ($attr as $key => $value) {
                $array[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $array);
        }

        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml .= "<{$root}{$attr}>";
        $xml .= $this->dataToXml($data, $item, $id);
        $xml .= "</{$root}>";

        return $xml;
    }

    /**
     * 数据XML编码
     *
     * @access protected
     *
     * @param mixed  $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id   数字索引key转换为的属性名
     *
     * @return string
     */
    private function dataToXml($data, $item, $id)
    {
        $xml = $attr = '';

        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= (is_array($val) || is_object($val)) ? $this->dataToXml($val, $item, $id) : $val;
            $xml .= "</{$key}>";
        }

        return $xml;
    }
}
