<?php

namespace app\common\lib;

/**
 * aes 加解密 (单例)
 * 使用示例
 * $key = cls_util::random();
 * $value = 'xxx';
 * cls_aes::getInstance()->set_key(substr($key, 0, 16));
 * cls_aes::getInstance()->set_iv(substr($key, 16, 16));
 * $value = cls_aes::getInstance()->encrypt($value);
 */
class cls_aes
{
    private static $instance;
    private static $blocksize = 16;
    private $_key = '';  // 密钥
    private $_iv = '';   // 向量

    public static function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 设置密钥
     * @param $key
     * @return void
     */
    public function set_key($key)
    {
        $this->_key = $key;
    }

    /**
     * 设置向量
     * @param $iv
     * @return void
     */
    public function set_iv($iv)
    {
        $this->_iv = $iv;
    }

    /**
     * 加密
     * @param $value
     * @return false|string
     */
    public function encrypt($value)
    {;
        // openssl 需要补码
        $value = $this->pkcs7_pad($value);
        $value = openssl_encrypt($value, "aes-128-cbc", $this->_key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->_iv);
        return $value;
    }

    /**
     * 解密
     * @param $value
     * @return string
     */
    public function decrypt($value)
    {
        $value = openssl_decrypt($value, "aes-128-cbc", $this->_key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->_iv);
        $value = $this->pkcs7_unpad($value);
        return $value;
    }

    /**
     * pkcs7补码，CBC加密方式必须补码
     * 在PKCS5Padding中，明确定义Block的大小是8位
     * 而在PKCS7Padding定义中，对于块的大小是不确定的，可以在1-255之间（块长度超出255的尚待研究）
     * 填充值的算法都是一样的
     * @param string $string  明文
     * @return String
     */
    public function pkcs7_pad($str)
    {
        $len = strlen($str);
        if ($len % self::$blocksize != 0)
        {
            // 计算需要填充的位数
            $pad = self::$blocksize - ($len % self::$blocksize);
            // 获得补位所用的字符
            $str .= str_repeat(chr($pad), $pad);
        }
        return $str;
    }

    /**
     * 除去pkcs7补码
     *
     * @param string 解密后的结果
     * @return string
     */
    public function pkcs7_unpad($str)
    {
        // 获得补位所用的字符，计算它的ASCII码，得到补码的长度
        $pad = ord(substr($str, -1));
        // 补码的长度超过或者等于补码块大小，说明明文是完整没有经过补码的
        if ($pad < 1 || $pad >= self::$blocksize)
        {
            $pad = 0;
        }
        // 获得补位所用的字符，检查这个字符是否在这个区间出现的次数跟它的数值相等
        if( strspn($str, chr($pad), strlen($str) - $pad) != $pad )
        {
            $pad = 0;
        }
        // 去掉补码，返回数据
        return substr($str, 0, (strlen($str) - $pad));
    }
}
