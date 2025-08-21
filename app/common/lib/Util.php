<?php

namespace app\common\lib;

use GeoIp2\Database\Reader;

class Util
{
    /**
     * 生成唯一标识
     * @param $type
     * @param $length
     * @return int|string|void
     */
    public static function random($type = 'web', $length = 32)
    {
        switch($type)
        {
            case 'basic':
                return mt_rand();   //使用 Mersenne Twister 算法返回随机整数
                break;
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
            case 'distinct':
            case 'hexdec':
                switch ($type)
                {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;

                    default:
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;

                    case 'numeric':
                        $pool = '0123456789';
                        break;

                    case 'nozero':
                        $pool = '123456789';
                        break;

                    case 'distinct': //用在兑换码(给用户用), 用户需要输入兑换码, 所以可读性要好
                        $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                        break;

                    case 'hexdec':
                        $pool = '0123456789abcdef';
                        break;
                }

                $str = '';
                for ($i=0; $i < $length; $i++)
                {
                    $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
                }
                return $str;
                break;
            case 'sha1' :
                return sha1(uniqid(mt_rand(), true));
                break;
            case 'uuid':
                $pool = ['8', '9', 'a', 'b'];
                return sprintf('%s-%s-4%s-%s%s-%s',
                    self::random('hexdec', 8),
                    self::random('hexdec', 4),
                    self::random('hexdec', 3),
                    $pool[array_rand($pool)],
                    self::random('hexdec', 3),
                    self::random('hexdec', 12));
                break;
            case 'unique':
                //会产生大量的重复数据
                //$str = uniqid();
                //生成的唯一标识中没有重复
                //版本>=7.1,使用 session_create_id()
                $str = version_compare(PHP_VERSION,'7.1.0','ge') ? md5(session_create_id()) : md5(uniqid(md5(microtime(true)),true));
                if ( $length == 32 )
                {
                    return $str;
                }
                else
                {
                    return substr($str, 8, 16);
                }
                break;
            case 'web':
                // 即使同一个IP，同一款浏览器，要在微妙内生成一样的随机数，也是不可能的
                // 进程ID保证了并发，微妙保证了一个进程每次生成都会不同，IP跟AGENT保证了一个网段
                // md5(当前进程id在目前微秒时间生成唯一id + 当前ip + 当前浏览器)
                $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'; //兼容cli本地调用时会报错
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? ''; //兼容cli本地调用时会报错
                $str = md5(getmypid().uniqid(md5(microtime(true)),true).$remote_addr.$user_agent);
                if ( $length == 32 )
                {
                    return $str;
                }
                else
                {
                    echo $str."\n";
                    echo substr($str, 8, $length);
                    return substr($str, 8, $length);
                }
                break;
            default:
        }
    }

    /**
     * 获得国家代码 例 SG或KH
     * @param $ip
     * @return string
     */
    public static function ip2country($ip = '')
    {
        if (empty($ip) || $ip == '127.0.0.1') {
            return '';
        }

        $db_path = app()->getRootPath().'GeoLite2-City.mmdb';
        $reader  = new Reader($db_path);
        $record  = $reader->city($ip);
        $country = strtoupper($record->country->isoCode); //返回大写国家代码
        return $country;
    }

    /**
     * 获取手机号国码
     * @return mixed
     */
    public static function get_area_code()
    {
        $data = json_decode(config('myconfig.area_code'), true);
        $china = [
            'cname' => '中国',
            'ename' => 'china',
            'areanum' => '86',
            'fee' => 1,
        ];
        //插入到数组开头
        array_unshift($data, $china);
        return $data;
    }

    /**
     * 获取图片URL
     * @param $img
     * @param $dir
     * @return mixed|string
     */
    public static function display_img($img, $dir = 'image')
    {
        if(empty($img))
        {
            return $img;
        }
        $img_url = config('myconfig.file_url')."/{$dir}/{$img}";
        return $img_url;
    }

    /**
     * 获取唯一性GUID
     * @param $trim
     * @return string
     */
    public static function get_guid_v4($trim = true)
    {
        // Windows
        if (function_exists('com_create_guid') === true) {
            $charid = com_create_guid();
            return $trim == true ? trim($charid, '{}') : $charid;
        }
        // OSX/Linux
        if (function_exists('openssl_random_pseudo_bytes') === true) {
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        // Fallback (PHP 4.2+)
        mt_srand((double)microtime() * 10000);
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45);                  // "-"
        $lbrace = $trim ? "" : chr(123);    // "{"
        $rbrace = $trim ? "" : chr(125);    // "}"
        $guidv4 = $lbrace .
            substr($charid, 0, 8) . $hyphen .
            substr($charid, 8, 4) . $hyphen .
            substr($charid, 12, 4) . $hyphen .
            substr($charid, 16, 4) . $hyphen .
            substr($charid, 20, 12) .
            $rbrace;
        return $guidv4;
    }

    /**
     * 多维数组合并
     * @param $array1
     * @param $array2
     * @return array
     */
    public static function array_merge_multiple($array1, $array2)
    {
        $merge = $array1 + $array2;
        $data = [];
        foreach ($merge as $key => $val) {
            if (isset($array1[$key])
                && is_array($array1[$key])
                && isset($array2[$key])
                && is_array($array2[$key])
            ) {
                $data[$key] = array_merge_multiple($array1[$key], $array2[$key]);
            } else {
                $data[$key] = isset($array2[$key]) ? $array2[$key] : $array1[$key];
            }
        }
        return $data;
    }

    /**
     * 获取数组中某个字段的所有值
     * @param $arr
     * @param $name
     * @return array
     */
    public static function array_key_value($arr, $name = "")
    {
        $return = array();
        if ($arr) {
            foreach ($arr as $key => $val) {
                if ($name) {
                    $return[] = $val[$name];
                } else {
                    $return[] = $key;
                }
            }
        }
        $return = array_unique($return);
        return $return;
    }

    /**
     * 二位数组排序
     * @param $arr
     * @param $keys
     * @param $desc
     * @return array
     */
    public static function array_sort($arr, $keys, $desc = false)
    {
        $key_value = $new_array = array();
        foreach ($arr as $k => $v) {
            $key_value[$k] = $v[$keys];
        }
        if ($desc) {
            arsort($key_value);
        } else {
            asort($key_value);
        }
        reset($key_value);
        foreach ($key_value as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    /**
     * 数组转成某个键值作为索引
     * @param $data
     * @param $key
     * @param $fields
     * @return array
     */
    public static function array_to_key($data, $key = 'id', $fields = [])
    {
        if (empty($data)) {
            return [];
        }
        $arr = [];
        if (!empty($fields)) {
            foreach ($data as $k => $v) {
                foreach ($fields as $v2) {
                    $arr[$v[$key]][$v2] = $v[$v2];
                }
            }
        } else {
            foreach ($data as $k => $v) {
                $arr[$v[$key]] = $v;
            }
        }
        return $arr;
    }

    /**
     * 1维数组键值对转化成select 类型输出
     * @param $arr
     * @return array
     */
    public static function key_value_arr_to_select($arr)
    {
        if (empty($arr)) {
            return [];
        }
        $data = [];
        foreach ($arr as $k => $v) {
            $data[] = [
                'id' => $k,
                'name' => $v
            ];
        }
        return $data;
    }

    /**
     * 将数组转成select
     * @param $arr
     * @param $key
     * @param $name_key
     * @return array
     */
    public static function key_value_arr_to_select2($arr, $key = '', $name_key = '')
    {
        if (empty($arr)) {
            return [];
        }
        $data = [];
        foreach ($arr as $k => $v) {
            $arr_name_key = explode(',', $name_key);
            $temp = [];
            foreach($arr_name_key as $item) {
                $temp[] = $v[$item];
            }
            $data[] = [
                'id' => $v[$key],
                'name' => implode(' ', $temp)
            ];
        }
        return $data;
    }

    /**
     * 有深度的树形，一般用于select
     * @param $data
     * @param $parent_id
     * @return array
     */
    public static function get_tree_option($data, $parent_id)
    {
        $stack = [$parent_id];
        $child = [];
        $added = [];
        $options = [];
        $obj = [];
        $loop = 0;
        $depth = -1;
        foreach ($data as $node) {
            $pid = $node['parent_id'];
            if (!isset($child[$pid])) {
                $child[$pid] = [];
            }
            array_push($child[$pid], $node['id']);
            $obj[$node['id']] = $node;
        }

        while (count($stack) > 0) {
            $id = $stack[0];
            $flag = false;
            $node = isset($obj[$id]) ? $obj[$id] : null;
            if (isset($child[$id])) {
                for ($i = count($child[$id]) - 1; $i >= 0; $i--) {
                    array_unshift($stack, $child[$id][$i]);
                }
                $flag = true;
            }
            if ($id != $parent_id && $node && !isset($added[$id])) {
                $node['depth'] = $depth;
                $options[] = $node;
                $added[$id] = true;
            }
            if ($flag == true) {
                $depth++;
            } else {
                if ($node) {
                    for ($i = count($child[$node['parent_id']]) - 1; $i >= 0; $i--) {
                        if ($child[$node['parent_id']][$i] == $id) {
                            array_splice($child[$node['parent_id']], $i, 1);
                            break;
                        }
                    }
                    if (count($child[$node['parent_id']]) == 0) {
                        $child[$node['parent_id']] = null;
                        $depth--;
                    }
                }
                array_shift($stack);
            }
            $loop++;
            if ($loop > 5000) return $options;
        }
        unset($child);
        unset($obj);
        return $options;
    }

    /**
     * 对象转数组
     * @param $object
     * @return mixed
     */
    public static function object_array($object)
    {
        //先编码成json字符串，再解码成数组
        return json_decode(json_encode($object), true);
    }

    /**
     * 数组转对象
     * @param $arr
     * @return object|void
     */
    public static function array_to_object($arr)
    {
        if(gettype($arr) != 'array') {
            return;
        }
        foreach($arr as $k => $v) {
            if(gettype($v) == 'array' || gettype($v) == 'object') {
                $arr[$k] = (object)array_to_object($v);
            }
        }
        return (object)$arr;
    }

    /**
     * 数组转XML
     * @param $arr
     * @param $ignore
     * @param $level
     * @return array|string|string[]|null
     */
    public static function array2xml($arr, $ignore = true, $level = 1)
    {
        $s = $level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n<root>\r\n" : '';
        $space = str_repeat("\t", $level);
        foreach ($arr as $k => $v) {
            if (!is_array($v)) {
                $s .= $space . "<item id=\"$k\">" . ($ignore ? '<![CDATA[' : '') . $v . ($ignore ? ']]>' : '')
                    . "</item>\r\n";
            } else {
                $s .= $space . "<item id=\"$k\">\r\n" . array2xml($v, $ignore, $level + 1) . $space . "</item>\r\n";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s . "</root>" : $s;
    }

    /**
     * xml转数组
     * @param $xml
     * @return string
     */
    public static function xml2array(&$xml)
    {
        $xml = "<xml>";
        foreach ($xml as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 字符串转换驼峰峰式，默认第一个字符串大写
     * @param $str
     * @param $ucfirst
     * @return mixed|string
     */
    public static function convert_under_line($str, $ucfirst = true)
    {
        while (($pos = strpos($str, '_')) !== false)
            $str = substr($str, 0, $pos) . ucfirst(substr($str, $pos + 1));

        return $ucfirst ? ucfirst($str) : $str;
    }

    /**
     * 转换成linux路径
     * @param $path
     * @return array|string|string[]
     */
    public static function linux_path($path)
    {
        return str_replace("\\", "/", $path);
    }

    /**
     * 将上下级转换成树形
     * @param $list
     * @param $pk
     * @param $pid
     * @param $child
     * @param $root
     * @return array
     */
    public static function tree($list = [], $pk = 'id', $pid = 'parent_id', $child = '_child', $root = 0)
    {

        // 创建Tree
        $tree = [];
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            //转出ID对内容
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];

                } else {

                    if (isset($refer[$parentId])) {

                        $parent =& $refer[$parentId];

                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * 将字节转换为可读文本
     * @param $size
     * @param $delimiter
     * @return string
     */
    public static function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }

    /**
     * 将秒转换为可读秒, 例 00:32:23
     * @param $seconds
     * @return string
     */
    public static function format_seconds($seconds)
    {
        $hour       = floor($seconds / 3600);
        $minute     = floor(($seconds - 3600 * $hour) / 60);
        $seconds    = floor((($seconds - 3600 * $hour) - 60 * $minute) % 60);
        if ($hour < 10) {
            $hour = "0" . $hour;
        }
        if ($minute < 10) {
            $minute = "0" . $minute;
        }
        if ($seconds < 10) {
            $seconds = "0" . $seconds;
        }
        return $hour . ':' . $minute . ':' . $seconds;
    }

    /**
     * 格式化显示时间
     * @param $time
     * @return string
     */
    public static function format_time($time)
    {
        $time = (int)substr($time, 0, 10);
        $int = time() - $time;
        $str = '';
        if ($int <= 2) {
            $str = sprintf('刚刚', $int);
        } elseif ($int < 60) {
            $str = sprintf('%d秒前', $int);
        } elseif ($int < 3600) {
            $str = sprintf('%d分钟前', floor($int / 60));
        } elseif ($int < 86400) {
            $str = sprintf('%d小时前', floor($int / 3600));
        } elseif ($int < 1728000) {
            $str = sprintf('%d天前', floor($int / 86400));
        } else {
            $str = date('Y年m月d日', $time);
        }
        return $str;
    }

    /**
     * 格式化日期
     * @param $str
     * @param $format
     * @return string
     */
    public static function format_date($str, $format = "Y-m-d")
    {
        $datetime = strtotime($str);
        return date($format, $datetime);
    }

    /**
     * 检查敏感词
     * @param $list
     * @param $str
     * @param $flag
     * @return int|mixed|string
     */
    public static function checkWords($list, $str, $flag = false)
    {
        $count = 0; //违规词的个数
        $sensitiveWord = '';  //违规词
        $stringAfter = $str;  //替换后的内容
        $pattern = "/" . implode("|", $list) . "/i"; //定义正则表达式
        if (preg_match_all($pattern, $str, $matches)) { //匹配到了结果
            $patternList = $matches[0];  //匹配到的数组
            $count = count($patternList);
            $sensitiveWord = implode(',', $patternList); //敏感词数组转字符串
//            $replaceArray = array_combine($patternList, array_fill(0, count($patternList), '***')); //把匹配到的数组进行合并，替换使用
//            $stringAfter = strtr($str, $replaceArray); //结果替换

            // 临时解决方案
            $itemArr = [];
            if (!empty($patternList)) {
                foreach ($patternList as $val) {
                    if (!$val) {
                        continue;
                    }
                    $itemArr[] = str_pad("", mb_strlen($val), "*", STR_PAD_LEFT);
                }
            }
            $replaceArray = array_combine($patternList, $itemArr); //把匹配到的数组进行合并，替换使用
            $stringAfter = strtr($str, $replaceArray); //结果替换
        }
        $log = "原句为 [ {$str} ]<br/>";
        if ($count == 0) {
            $log .= "暂未匹配到敏感词！";
        } else {
            $log .= "匹配到 [ {$count} ]个敏感词：[ {$sensitiveWord} ]<br/>" .
                "替换后为：[ {$stringAfter} ]";
        }
        if (!$flag) {
            return $stringAfter;
        } else {
            return $count;
        }
    }

    /**
     * 是否序列化数据
     * @param $string
     * @return bool
     */
    public static function is_serialized($string)
    {
        $array = @unserialize($string);
        return ! ($array === false and $string !== 'b:0;');
    }

    /**
     * 是否为json
     * @param $string
     * @return bool
     */
    public static function is_json($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 按照权重随机, 输入 ['a'=>10, 'b'=>20, 'c'=>50] 返回 'c'
     * @param $weight
     * @return int|string
     */
    public static function roll($weight = [])
    {
        $total = array_sum($weight);
        $random = rand(1, $total);
        $tmp = 0;
        $roll_num = 0;
        foreach($weight as $k => $v)
        {
            $min = $tmp;
            $tmp += $v;
            $max = $tmp;
            if ($random > $min && $random <= $max)
            {
                $roll_num = $k;
                break;
            }
        }
        return $roll_num;
    }

    /**
     * 获取双MD5加密密码
     * @param $password
     * @return string
     */
    public static function get_password($password)
    {
        return md5($password);
    }

    /**
     * 生成8位大写英文+数字兑换码
     * @return int|string|null
     */
    public static function make_exchange_code()
    {
        $code = self::random('distinct', 8);
        return $code;
    }

    /**
     * 生成8位大写英文+数字推广码
     * @return mixed
     */
    public static function make_channel_code()
    {
        $code = self::random('distinct', 8);
        return $code;
    }

    /**
     * 生成订单号(默认19位)
     * @param $num
     * @return string
     */
    public static function make_order_id($num = 7)
    {
        return date("ymdHis").self::random('numeric', $num);
    }

    /**
     * 数据导出Excel(csv文件)
     * @param $file_name
     * @param $tile
     * @param $data
     * @return void
     */
    public static function export_excel($file_name, $tile = [], $data = [])
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 0);
        ob_end_clean();
        ob_start();
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=" . $file_name);
        $fp = fopen('php://output', 'w');
        // 转码 防止乱码(比如微信昵称)
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fp, $tile);
        $index = 0;
        foreach ($data as $item) {
            if ($index == 1000) {
                $index = 0;
                ob_flush();
                flush();
            }
            $index++;
            fputcsv($fp, $item);
        }
        fclose($fp);

        ob_flush();
        flush();
        ob_end_clean();
    }

    /**
     * 时间戳转日期格式
     * @param $time
     * @param $format
     * @return string
     */
    public static function datetime($time, $format = 'Y-m-d H:i:s')
    {
        if (empty($time)) {
            return '--';
        }
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }

    /**
     * 判断是否手机端
     * @return bool
     */
    public static function is_mobile_client()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 判断手机发送的客户端标志,兼容性有待提高,把常见的类型放到前面
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = [
                'android',
                'iphone',
                'samsung',
                'ucweb',
                'wap',
                'mobile',
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'ipod',
                'blackberry',
                'meizu',
                'netfront',
                'symbian',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp'
            ];
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 提取纯文本
     * @param $str
     * @return string
     */
    public static function cutstr_html($str)
    {
        $str = trim(strip_tags($str)); //清除字符串两边的空格
        $str = preg_replace("/\t/", "", $str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
        $str = preg_replace("/\r\n/", "", $str);
        $str = preg_replace("/\r/", "", $str);
        $str = preg_replace("/\n/", "", $str);
        $str = preg_replace("/ /", "", $str);
        $str = preg_replace("/  /", "", $str);  //匹配html中的空格
        return trim($str); //返回字符串
    }

    /**
     * 去除指定HTML标签, 示例：echo strip_html_tags($str, array('a','img'))
     * @param $str
     * @param $tags
     * @param $content
     * @return array|string|string[]|null
     */
    public static function strip_html_tags($str, $tags, $content = 0)
    {
        if ($content) {
            $html = array();
            foreach ($tags as $tag) {
                $html[] = '/(<' . $tag . '.*?>[\s|\S]*?<\/' . $tag . '>)/';
            }
            $result = preg_replace($html, '', $str);
        } else {
            $html = array();
            foreach ($tags as $tag) {
                $html[] = "/(<(?:\/" . $tag . "|" . $tag . ")[^>]*>)/i";
            }
            $result = preg_replace($html, '', $str);
        }
        return $result;
    }

    /**
     * 字符串截取
     * @param $str
     * @param $start
     * @param $length
     * @param $suffix
     * @param $charset
     * @return false|string
     */
    public static function sub_str($str, $start = 0, $length = 10, $suffix = true, $charset = "utf-8")
    {
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }
        $omit = mb_strlen($str) >= $length ? '...' : '';
        return $suffix ? $slice . $omit : $slice;
    }

    /**
     * 字符串截取，支持中文和其他编码
     * @param $str
     * @param $start
     * @param $length
     * @param $encoding
     * @param $suffix
     * @return false|string
     */
    public static function mbsubstr($str, $start = 0, $length = null, $encoding = "utf-8", $suffix = '...')
    {
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $encoding);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $encoding);
            if (false === $slice) {
                $slice = '';
            }
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$encoding], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice . $suffix : $slice;
    }

    /**
     * DES解密
     * @param $data
     * @param $key
     * @return false|string
     */
    public static function decrypt($data, $key = 'p@ssw0rd')
    {
        return openssl_decrypt($data, 'des-ecb', $key);
    }

    /**
     * DES加密
     * @param $data
     * @param $key
     * @return false|string
     */
    public static function encrypt($data, $key = 'p@ssw0rd')
    {
        return openssl_encrypt($data, 'des-ecb', $key);
    }
}
