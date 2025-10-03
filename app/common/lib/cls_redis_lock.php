<?php

namespace app\common\lib;

use think\facade\Cache;

class cls_redis_lock
{
    /**
     * 加锁
     * @param $name
     * @param $timeout
     * @param $expire
     * @param $wait_interval_us
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function lock($name, $timeout = 0, $expire = 15, $wait_interval_us = 100000)
    {
        if ($name == null) return false;

        //取得当前时间
        $now = time();
        //获取锁失败时的等待超时时刻
        $timeout_at = $now + $timeout;
        //锁的最大生存时刻
        $expire_at = $now + $expire;
        $key = "lock:{$name}";

        while (true)
        {
            //将key的最大生存时刻存到redis里，过了这个时刻该锁会被自动释放
            $result = Cache::store('redis')->set($key, $expire_at, ['nx' => true, 'ex' => $expire]);
            if ($result != false)
            {
                //设置key的失效时间
                Cache::store('redis')->set($key, $expire);
                return true;
            }

            //循环请求锁，如果没设置锁失败的等待时间 或者 已超过最大等待时间了，那就退出
            if ($timeout <= 0 || $timeout_at < microtime(true))
            {
                break;
            }

            //隔 $wait_interval_us 0.1秒后继续 请求
            usleep($wait_interval_us);
        }

        return false;
    }

    /**
     * 解锁
     * @param $name
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function unlock($name)
    {
        //先判断是否存在此锁
        if ( self::is_locking($name) )
        {
            //删除锁
            if(Cache::store('redis')->delete("Lock:$name"))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * 给当前所增加指定生存时间，必须大于0
     * @param $name
     * @param $expire
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function expire($name, $expire)
    {
        //先判断是否存在该锁
        if (self::is_locking($name))
        {
            //所指定的生存时间必须大于0
            $expire = max($expire, 1);
            //增加锁生存时间
            if(Cache::store('redis')->set("Lock:$name", $expire))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断当前是否拥有指定名字的所
     * @param $name
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function is_locking($name)
    {
        //从redis返回该锁的生存时间
        return Cache::store('redis')->get("Lock:$name");
    }
}
